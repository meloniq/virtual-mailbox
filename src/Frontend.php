<?php
namespace Meloniq\VirtualMailbox;

use WP_Post;

class Frontend {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'the_content', array( $this, 'email_content' ), 10, 1 );
		add_action( 'template_redirect', array( $this, 'limit_access' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Modify email content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function email_content( string $content ) : string {
		// check if we are on single email page
		if ( ! is_singular( 'vmbx_email' ) ) {
			return $content;
		}

		// check if we are in the loop and main query
		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$content = get_the_content();
		$escaped = htmlspecialchars( $content, ENT_QUOTES, 'UTF-8' );

		$iframe = '<iframe srcdoc=\'' . esc_attr( $escaped ) . '\' frameborder="0" allowfullscreen="" style="width: 100%; height: 500px;"></iframe>';
		$output = '<div class="vmbx-email-content">' . $iframe . '</div>';

		return $output;
	}

	/**
	 * Limit access to email.
	 *
	 * @return void
	 */
	public function limit_access() : void {
		if ( ! is_singular( 'vmbx_email' ) ) {
			return;
		}

		$post = get_post();
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		// check if user is email author or admin
		$user = wp_get_current_user();
		if ( $user->ID === $post->post_author || current_user_can( 'manage_options' ) ) {
			return;
		}

		// if not, redirect to login page
		nocache_headers();
		wp_redirect( wp_login_url( $this->get_current_url() ) );
		exit;
	}

	/**
	 * Enqueue styles.
	 *
	 * @return void
	 */
	public function enqueue_styles() : void {
		// enqueue styles only on page with shortcode
		if ( ! has_shortcode( get_the_content(), 'vmbx_mailbox' ) ) {
			return;
		}

		wp_enqueue_style( 'vmbx-front-email-single', VMBX_PLUGIN_URL . 'assets/front-email-single.css', array(), '1.0' );
	}

	/**
	 * Get the current, full URL.
	 *
	 * @return string
	 */
	public function get_current_url() {
		$http_host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$protocol    = is_ssl() ? 'https://' : 'http://';

		return $protocol . $http_host . $request_uri;
	}


}
