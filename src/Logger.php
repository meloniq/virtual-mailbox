<?php
/**
 * Virtual Mailbox Logger class.
 *
 * @package Meloniq\VirtualMailbox
 */

namespace Meloniq\VirtualMailbox;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Logger class for logging sent emails.
 */
class Logger {

	/**
	 * WP_Mail arguments.
	 *
	 * @var array
	 */
	protected $wp_mail_args = array();

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// hooks for monitoring mails, priority set low so they run after other plugins.
		add_filter( 'wp_mail', array( $this, 'wp_mail' ), 99999 );
		add_action( 'phpmailer_init', array( $this, 'phpmailer_init' ), 99999 );
	}

	/**
	 * Filter wp_mail -- grab a copy of the arguments for later.
	 *
	 * @param array $args Arguments passed to wp_mail().
	 *
	 * @return array
	 */
	public function wp_mail( array $args ): array {
		$this->wp_mail_args = $args;

		return $args;
	}

	/**
	 * Grab a copy of the email for the log.
	 *
	 * @param PHPMailer $phpmailer PHPMailer instance.
	 *
	 * @return void
	 */
	public function phpmailer_init( PHPMailer $phpmailer ): void {
		// get message body.
		$message = $phpmailer->Body;

		// collate additional fields into array.
		$fields               = array();
		$fields['_vmbx_from'] = sprintf( '%s <%s>', $phpmailer->FromName, $phpmailer->From );

		// get alternative message body.
		$fields['_vmbx_altbody'] = $phpmailer->AltBody;

		// detect text/html when content type is text/plain but email has an alternate message (WP e-Commerce, I'm looking at you!).
		$content_type = $phpmailer->ContentType;
		if ( $content_type === 'text/plain' && ! empty( $fields['_vmbx_altbody'] ) ) {
			$content_type = 'text/html';
		}
		$fields['_vmbx_content-type'] = $content_type;

		// pick up recipients from wp_mail() arguments.
		if ( isset( $this->wp_mail_args['to'] ) ) {
			$to = $this->wp_mail_args['to'];
			if ( is_array( $to ) ) {
				$to = implode( ', ', $to );
			}
			$fields['_vmbx_to'] = $to;
		}

		// pick up CC/BCC from wp_mail() arguments, collating them from headers.
		if ( isset( $this->wp_mail_args['headers'] ) ) {
			$cc      = array();
			$bcc     = array();
			$headers = $this->wp_mail_args['headers'];
			if ( ! is_array( $headers ) ) {
				$headers = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
			}
			foreach ( $headers as $header ) {
				if ( $header ) {
					list( $header, $value ) = explode( ':', $header, 2 );
					switch ( strtolower( $header ) ) {
						case 'cc':
							$cc[] = trim( $value );
							break;
						case 'bcc':
							$bcc[] = trim( $value );
							break;
					}
				}
			}

			if ( ! empty( $cc ) ) {
				$fields['_vmbx_cc'] = implode( ', ', $cc );
			}
			if ( ! empty( $bcc ) ) {
				$fields['_vmbx_bcc'] = implode( ', ', $bcc );
			}
		}

		// create email entry for each recipient.
		$user_ids = $this->prepare_user_ids( $fields );
		foreach ( $user_ids as $user_id ) {
			$this->create_log( $user_id, $phpmailer->Subject, $message, $fields );
		}

		// reset recorded wp_mail() arguments.
		$this->wp_mail_args = false;
	}

	/**
	 * Prepare user ids from email addresses.
	 *
	 * @param array $fields Email fields.
	 *
	 * @return array
	 */
	protected function prepare_user_ids( array $fields ): array {
		$user_ids = array();
		$emails   = array();

		$field_keys = array( '_vmbx_to', '_vmbx_cc', '_vmbx_bcc' );
		foreach ( $field_keys as $field_key ) {
			$recipients = $fields[ $field_key ] ?? '';
			if ( empty( $recipients ) ) {
				continue;
			}

			$recipients = explode( ',', $recipients );
			$recipients = array_map( 'trim', $recipients );

			$emails = array_merge( $emails, $recipients );
		}

		if ( empty( $emails ) ) {
			return $user_ids;
		}

		foreach ( $emails as $email ) {
			$user = get_user_by( 'email', $email );
			if ( $user ) {
				$user_ids[] = $user->ID;
			} else {
				// store email of unknown recipient.
				$store_unknown = get_option( 'vmbx_store_unknown' );
				if ( $store_unknown ) {
					$user_ids[] = 0;
				}
			}
		}

		$user_ids = array_unique( $user_ids );
		$user_ids = apply_filters( 'vmbx_email_user_ids', $user_ids, $fields );

		return $user_ids;
	}

	/**
	 * Create a new email log.
	 *
	 * @param int    $user_id User ID.
	 * @param string $subject Email subject.
	 * @param string $message Email message body.
	 * @param array  $fields  Additional fields.
	 *
	 * @return int post ID of new log
	 */
	protected function create_log( int $user_id, string $subject, string $message, array $fields ): int {
		// prevent sanitising of email body and alt-body,
		// so that we can access full email content in raw log view.
		remove_all_filters( 'pre_post_content' );
		remove_all_filters( 'content_save_pre' );
		remove_all_filters( 'sanitize_vmbx_email_meta__vmbx_altbody' );

		// allow plugins to add back some filtering.
		do_action( 'vmbx_email_pre_insert', $user_id, $subject, $message, $fields );

		// create post for message.
		$post_id = wp_insert_post(
			array(
				'post_title'     => $subject,
				'post_content'   => $message,
				'post_author'    => $user_id,
				'post_name'      => $this->generate_email_id(),
				'post_type'      => 'vmbx_email',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			)
		);

		// add field values.
		if ( ! $post_id ) {
			return 0;
		}

		foreach ( $fields as $name => $value ) {
			if ( ! empty( $value ) ) {
				update_post_meta( $post_id, $name, $value );
			}
		}

		// allow plugins to add more meta.
		do_action( 'vmbx_email_insert', $post_id, $user_id, $subject, $message, $fields );

		return $post_id;
	}

	/**
	 * Generate a unique email ID.
	 *
	 * @return string
	 */
	protected function generate_email_id(): string {
		// a-z, A-Z, 0-9.
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		// 7 characters long.
		$length = 7;

		// generate a unique ID.
		$unique_id = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$unique_id .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
		}

		$unique_id = apply_filters( 'vmbx_email_id', $unique_id );

		// make sure it's unique.
		$existing = get_page_by_path( $unique_id, OBJECT, 'vmbx_email' );
		if ( $existing ) {
			return $this->generate_email_id();
		}

		return $unique_id;
	}
}
