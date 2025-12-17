<?php
/**
 * Virtual Mailbox List Table class.
 *
 * @package Meloniq\VirtualMailbox
 */

namespace Meloniq\VirtualMailbox;

use WP_Query;
use WP_Post;

/**
 * List Table class for customizing the admin list table for emails.
 */
class ListTable {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		if ( ! $this->is_post_type_page() ) {
			return;
		}

		// custom columns.
		add_filter( 'manage_vmbx_email_posts_columns', array( $this, 'manage_columns' ), 100, 1 );
		add_action( 'manage_vmbx_email_posts_custom_column', array( $this, 'display_columns' ), 10, 2 );
		add_action( 'manage_edit-vmbx_email_sortable_columns', array( $this, 'sortable_columns' ), 10, 1 );

		// available actions.
		add_filter( 'bulk_actions-edit-vmbx_email', array( $this, 'bulk_actions' ), 10, 1 );
		add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_actions_messages' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );

		// query filters.
		add_filter( 'parse_query', array( $this, 'query_order' ), 10, 1 );
		add_filter( 'posts_search', array( $this, 'query_search' ), 10, 2 );
	}

	/**
	 * Manage columns.
	 *
	 * @param array $posts_columns Columns.
	 *
	 * @return array
	 */
	public function manage_columns( array $posts_columns ): array {
		$new_columns = array();

		// preserve bulk action checkbox column.
		if ( isset( $posts_columns['cb'] ) ) {
			$new_columns['cb'] = $posts_columns['cb'];
		}

		// $new_columns['title'] = _x( 'Subject', 'email subject', 'virtual-mailbox' );
		$new_columns['vmbx_title'] = _x( 'Subject', 'email subject', 'virtual-mailbox' );
		$new_columns['vmbx_to']    = _x( 'Recipients', 'email recipients (To:)', 'virtual-mailbox' );
		$new_columns['vmbx_date']  = _x( 'Date', 'email date', 'virtual-mailbox' );

		// preserve date column.
		if ( isset( $posts_columns['date'] ) ) {
			// $new_columns['date'] = $posts_columns['date'];
		}

		$new_columns = apply_filters( 'vmbx_email_manage_columns', $new_columns );

		return $new_columns;
	}

	/**
	 * Display custom columns.
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id Post ID.
	 *
	 * @return void
	 */
	public function display_columns( string $column_name, int $post_id ): void {
		global $mode;

		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		switch ( $column_name ) {
			case 'vmbx_title':
				$view_link = get_edit_post_link( $post_id );
				printf( '<strong><a class="row-title" href="%s">%s</a></strong>', esc_url( $view_link ), esc_html( $post->post_title ) );
				break;
			case 'vmbx_to':
				echo esc_html( get_the_author_meta( 'display_name', $post->post_author ) );
				// echo esc_html( get_post_meta( $post_id, '_vmbx_to', true ) );
				break;
			case 'vmbx_date':
				echo esc_html( get_the_date( 'Y-m-d H:i:s', $post_id ) );
				break;
		}
	}

	/**
	 * Set sortable columns.
	 *
	 * @param array $columns Columns.
	 *
	 * @return array
	 */
	public function sortable_columns( array $columns ): array {
		$columns['vmbx_title'] = 'title';
		$columns['vmbx_to']    = 'vmbx_to';
		$columns['vmbx_date']  = 'date';

		return $columns;
	}

	/**
	 * Available bulk actions.
	 *
	 * @param array $actions Actions.
	 *
	 * @return array
	 */
	public function bulk_actions( array $actions ): array {
		unset( $actions['edit'] );

		return $actions;
	}

	/**
	 * Custom messages for bulk actions.
	 *
	 * @param array $messages Messages.
	 * @param array $bulk_counts Bulk counts.
	 *
	 * @return array
	 */
	public function bulk_actions_messages( array $messages, array $bulk_counts ): array {
		$messages['vmbx_email'] = array(
			// translators: %s: number of emails.
			'deleted'   => _n( '%s email permanently deleted.', '%s emails permanently deleted.', $bulk_counts['deleted'], 'virtual-mailbox' ),
			// translators: %s: number of emails.
			'trashed'   => _n( '%s email moved to the Trash.', '%s emails moved to the Trash.', $bulk_counts['trashed'], 'virtual-mailbox' ),
			// translators: %s: number of emails.
			'untrashed' => _n( '%s email restored from the Trash.', '%s emails restored from the Trash.', $bulk_counts['untrashed'], 'virtual-mailbox' ),
		);

		return $messages;
	}

	/**
	 * Available row actions.
	 *
	 * @param array   $actions Actions.
	 * @param WP_Post $post Post.
	 *
	 * @return array
	 */
	public function row_actions( array $actions, WP_Post $post ): array {
		// remove quick edit.
		unset( $actions['inline hide-if-no-js'] );

		// remove edit and trash links.
		unset( $actions['edit'] );
		unset( $actions['trash'] );

		// add Preview link.
		$link            = '<a href="%s" title="%s">%s</a>';
		$label           = _x( 'Preview', 'preview email', 'virtual-mailbox' );
		$url             = get_edit_post_link( $post->ID );
		$actions['view'] = sprintf( $link, esc_url( $url ), esc_attr( $label ), esc_html( $label ) );

		// add Delete link.
		$link              = '<a href="%s" title="%s" class="submitdelete">%s</a>';
		$label             = _x( 'Delete', 'delete email', 'virtual-mailbox' );
		$url               = get_delete_post_link( $post->ID, '', true );
		$actions['delete'] = sprintf( $link, esc_url( $url ), esc_attr( $label ), esc_html( $label ) );

		return $actions;
	}

	/**
	 * Set default post order and filter by custom meta.
	 *
	 * @param WP_Query $query Query.
	 *
	 * @return WP_Query
	 */
	public function query_order( WP_Query $query ): WP_Query {
		if ( ! $query->is_admin || ( $query->get( 'post_type' ) !== 'vmbx_email' ) ) {
			return $query;
		}

		// default to ID descending.
		if ( empty( $query->query_vars['orderby'] ) ) {
			$query->set( 'orderby', 'ID' );
			$query->set( 'order', 'DESC' );
		}

		// order by custom meta.
		if ( $query->query_vars['orderby'] === 'vmbx_to' ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', '_vmbx_to' );
		}

		// make sure it picks up posts with the custom post status.
		$query->set( 'post_status', 'any' );

		return $query;
	}

	/**
	 * Add custom fields to search query.
	 *
	 * @param string   $search The search string.
	 * @param WP_Query $query The current WP_Query object.
	 *
	 * @return string
	 */
	public function query_search( string $search, WP_Query $query ): string {
		global $wpdb;

		if ( ! $query->is_main_query() || empty( $query->query['s'] ) ) {
			return $search;
		}

		$like = '%' . $wpdb->esc_like( $query->query['s'] ) . '%';

		$like_prep = $wpdb->prepare(
			"
			OR EXISTS (
				SELECT * FROM {$wpdb->postmeta} WHERE post_id={$wpdb->posts}.ID
				AND meta_key in ('_vmbx_from','_vmbx_to','_vmbx_cc','_vmbx_bcc')
				AND meta_value LIKE %s
			)
		",
			$like
		);

		$search = preg_replace( "#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#", $like_prep, $search );

		return $search;
	}

	/**
	 * Check if the current screen is the post type page.
	 *
	 * @return bool
	 */
	protected function is_post_type_page(): bool {
		global $typenow, $pagenow;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( $pagenow === 'edit.php' && ! empty( $_GET['post_type'] ) && $_GET['post_type'] === 'vmbx_email' ) {
			return true;
		}

		$type = $typenow;

		if ( empty( $type ) ) {
			// try to pick it up from the query string.
			if ( ! empty( $_GET['post'] ) ) {
				$post_id = absint( $_GET['post'] );
				$type    = get_post_type( $post_id );
			} elseif ( ! empty( $_GET['post_id'] ) ) {
				$post_id = absint( $_GET['post_id'] );
				$type    = get_post_type( (int) $_GET['post_id'] );
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return ( $type === 'vmbx_email' );
	}
}
