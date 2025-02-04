<?php
namespace Meloniq\VirtualMailbox;

class PostType {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// register CPT early, so it beats other plugins that send emails on init
		add_action( 'init', array( $this, 'register' ), 1 );

	}

	/**
	 * Register post type.
	 *
	 * @return void
	 */
	public function register() : void {
		$labels = array(
			'name'               => __( 'Emails', 'virtual-mailbox' ),
			'singular_name'      => __( 'Email', 'virtual-mailbox' ),
			'menu_name'          => __( 'Emails', 'virtual-mailbox' ),
			'search_items'       => __( 'Search email', 'virtual-mailbox' ),
			'add_new'            => __( 'Add New', 'virtual-mailbox' ),
			'add_new_item'       => __( 'Add New Email', 'virtual-mailbox' ),
			'edit_item'          => __( 'Edit Email', 'virtual-mailbox' ),
			'new_item'           => __( 'New Email', 'virtual-mailbox' ),
			'view_item'          => __( 'View Email', 'virtual-mailbox' ),
			'search_items'       => __( 'Search Emails', 'virtual-mailbox' ),
			'not_found'          => __( 'No emails found', 'virtual-mailbox' ),
			'not_found_in_trash' => __( 'No emails found in Trash', 'virtual-mailbox' ),
			'parent_item_colon'  => __( 'Parent Email:', 'virtual-mailbox' ),
		);

		$capabilities = array(
			'create_posts'       => 'do_not_allow',
			'edit_post'          => 'activate_plugins',
			'edit_posts'         => 'edit_others_posts',
			'edit_others_posts'  => 'edit_others_posts',
			'delete_post'        => 'activate_plugins',
			'delete_posts'       => 'edit_others_posts',
			'read_post'          => 'activate_plugins',
			'read_private_posts' => 'do_not_allow',
			'publish_posts'      => 'do_not_allow',
		);

		$show_ui = false;
		if ( current_user_can( 'edit_others_posts' ) ) {
			$show_ui = true;
		}

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Keep temporary records of emails.', 'virtual-mailbox' ),
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'public'              => true,
			'show_ui'             => $show_ui,
			'show_in_admin_bar'   => false,
			'show_in_menu'        => 'tools.php',
			'menu_position'       => 75,
			'hierarchical'        => false,
			'has_archive'         => true,
			'supports'            => array( '__nada__' ),
			'rewrite'             => array( 'slug' => 'vmbx', 'with_front' => false, 'feeds' => false ),
			'query_var'           => false,
			'can_export'          => false,
			'capabilities'        => $capabilities,
			'map_meta_cap'        => false,
		);

		$args = apply_filters( 'vmbx_post_type_args', $args );

		// register the post type
		register_post_type( 'vmbx_email', $args );
	}


}
