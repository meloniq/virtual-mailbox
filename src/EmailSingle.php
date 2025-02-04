<?php
namespace Meloniq\VirtualMailbox;

use WP_Post;

class EmailSingle {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'add_meta_boxes_vmbx_email', array( $this, 'add_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

	}

	/**
	 * Enqueue styles.
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function enqueue_styles( string $hook ) : void {
		if ( 'post.php' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'vmbx-admin-email-single', VMBX_PLUGIN_URL . 'assets/admin-email-single.css', array(), '1.0' );
	}

	/**
	 * Add meta boxes.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		// remove default meta boxes
		remove_meta_box( 'submitdiv', 'vmbx_email', 'side' );
		remove_meta_box( 'slugdiv', 'vmbx_email', 'normal' );

		// display email data
		add_meta_box( 'vmbx_email_data', __( 'Email Data', 'virtual-mailbox' ), array( $this, 'meta_box_email_data' ), 'vmbx_email', 'normal', 'high' );

		// display email body
		add_meta_box( 'vmbx_email_body', __( 'Email Body', 'virtual-mailbox' ), array( $this, 'meta_box_email_body' ), 'vmbx_email', 'normal', 'high' );
	}

	/**
	 * Meta box: Email data.
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function meta_box_email_data( WP_Post $post ) : void {
		$data = array(
			'subject'   => get_the_title( $post->ID ),
			'content'   => get_post_meta( $post->ID, '_vmbx_content-type', true ),
			'from'      => get_post_meta( $post->ID, '_vmbx_from', true ),
			'to'        => get_post_meta( $post->ID, '_vmbx_to', true ),
			'cc'        => get_post_meta( $post->ID, '_vmbx_cc', true ),
			'bcc'       => get_post_meta( $post->ID, '_vmbx_bcc', true ),
			'date'      => get_the_date( 'Y-m-d H:i:s', $post->ID ),
			'slug'      => $post->post_name, // todo: view link
			'user_id'   => $post->post_author, // todo: edit link
			'user_name' => get_the_author_meta( 'display_name', $post->post_author ),
		);
		$data = apply_filters( 'vmbx_metabox_email_data', $data, $post );

		$headers = array(
			'subject'   => __( 'Subject:', 'virtual-mailbox' ),
			'content'   => __( 'Content Type:', 'virtual-mailbox' ),
			'from'      => __( 'From:', 'virtual-mailbox' ),
			'to'        => __( 'To:', 'virtual-mailbox' ),
			'cc'        => __( 'CC:', 'virtual-mailbox' ),
			'bcc'       => __( 'BCC:', 'virtual-mailbox' ),
			'date'      => __( 'Date:', 'virtual-mailbox' ),
			'slug'      => __( 'Slug:', 'virtual-mailbox' ),
			'user_id'   => __( 'User ID:', 'virtual-mailbox' ),
			'user_name' => __( 'User Name:', 'virtual-mailbox' ),
		);
		$headers = apply_filters( 'vmbx_metabox_email_data_headers', $headers );

		echo '<ul>';
		foreach ( $data as $key => $value ) {
			$field_class = 'item_' . $key;
			$header = isset( $headers[ $key ] ) ? $headers[ $key ] : $key;

			echo '<li class="' . esc_attr( $field_class ) . '">';
			echo '<div class="item_header">' . esc_html( $header ) . '</div>';
			echo '<div class="item_content">' . esc_html( $value ) . '</div>';
			echo '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Meta box: Email body.
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function meta_box_email_body( WP_Post $post ) : void {
		$content = get_the_content( '', false, $post->ID );
		$escaped = htmlspecialchars( $content, ENT_QUOTES, 'UTF-8' );

		echo '<iframe srcdoc=\'' . esc_attr( $escaped ) . '\' frameborder="0" allowfullscreen="" style="width: 100%; height: 500px;">';
		echo '</iframe>';
	}

}
