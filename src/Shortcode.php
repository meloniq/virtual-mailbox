<?php
/**
 * Virtual Mailbox Shortcode class.
 *
 * @package Meloniq\VirtualMailbox
 */

namespace Meloniq\VirtualMailbox;

use WP_Query;

/**
 * Shortcode class for displaying mailbox.
 */
class Shortcode {

	/**
	 * Max number of pages.
	 *
	 * @var int
	 */
	protected $max_num_pages = 0;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_shortcode( 'vmbx_mailbox', array( $this, 'shortcode_mailbox' ) );
	}

	/**
	 * Shortcode: Mailbox.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function shortcode_mailbox( array $atts ): string {
		// get current user id.
		$user_id = get_current_user_id();

		// for non logged in users, return login link.
		if ( ! $user_id ) {
			return sprintf(
				'<a href="%s">%s</a>',
				wp_login_url( get_permalink() ),
				__( 'Login to view mailbox', 'virtual-mailbox' )
			);
		}

		// display mailbox.
		return $this->display_mailbox( $user_id );
	}

	/**
	 * Display mailbox.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return string
	 */
	protected function display_mailbox( int $user_id ): string {
		// get emails for user.
		$emails = $this->get_emails( $user_id );

		// display emails, table (title, date).
		$output = '<table class="vmbx-emails-table">';

		$output .= '<thead>';
		$output .= '<tr><th>' . __( 'Subject', 'virtual-mailbox' ) . '</th><th>' . __( 'Date', 'virtual-mailbox' ) . '</th></tr>';
		$output .= '</thead>';

		$output .= '<tbody>';
		foreach ( $emails as $email ) {
			$output .= '<tr>';
			$output .= '<td><a href="' . get_permalink( $email->ID ) . '">' . esc_html( $email->post_title ) . '</a></td>';
			$output .= '<td>' . get_the_date( 'Y-m-d H:i:s', $email->ID ) . '</td>';
			$output .= '</tr>';
		}
		$output .= '</tbody>';

		$output .= '</table>';

		// add pagination.
		$output .= $this->pagination();

		return $output;
	}

	/**
	 * Get emails for user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array
	 */
	protected function get_emails( int $user_id ): array {
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

		$args_query = array(
			'post_type' => 'vmbx_email',
			'author'    => $user_id,
			'orderby'   => 'date',
			'order'     => 'DESC',
			'paged'     => $paged,
		);

		$query = new WP_Query( $args_query );

		// set pagination data.
		$this->max_num_pages = $query->max_num_pages;

		return $query->posts;
	}

	/**
	 * Pagination.
	 *
	 * @return string
	 */
	protected function pagination(): string {
		$base    = esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) );
		$current = max( 1, get_query_var( 'paged' ) );
		$total   = $this->max_num_pages;

		$args = array(
			'base'      => $base,
			'format'    => '',
			'add_args'  => false,
			'current'   => max( 1, $current ),
			'total'     => $total,
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
			'type'      => 'list',
			'end_size'  => 3,
			'mid_size'  => 3,
		);

		$pagination = paginate_links( $args );

		$output  = '<div class="vmbx-pagination">';
		$output .= $pagination;
		$output .= '</div>';

		return $output;
	}
}
