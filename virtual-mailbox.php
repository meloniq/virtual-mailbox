<?php
/*
 * Plugin Name:       Virtual Mailbox
 * Plugin URI:        https://blog.meloniq.net/virtual-mailbox/
 *
 * Description:       Virtual Mailbox - log all outgoing emails, and allow to browse them.
 * Tags:              wp mail, email, mailbox, logger, user emails
 *
 * Requires at least: 4.9
 * Requires PHP:      7.4
 * Version:           1.0
 *
 * Author:            MELONIQ.NET
 * Author URI:        https://meloniq.net/
 *
 * License:           GPLv2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain:       virtual-mailbox
 */

namespace Meloniq\VirtualMailbox;

// If this file is accessed directly, then abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'VMBX_TD', 'virtual-mailbox' );
define( 'VMBX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VMBX_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Include the autoloader so we can dynamically include the rest of the classes.
require_once trailingslashit( dirname( __FILE__ ) ) . 'vendor/autoload.php';


/**
 * Setup plugin data.
 *
 * @return void
 */
function setup() {
	global $vmbx_instance;

	// Only in admin area.
	if ( is_admin() ) {
		$vmbx_instance['admin-page']   = new AdminPage();
		$vmbx_instance['settings']     = new Settings();
		$vmbx_instance['list-table']   = new ListTable();
		$vmbx_instance['email-single'] = new EmailSingle();
	}

	$vmbx_instance['logger']    = new Logger();
	$vmbx_instance['cron']      = new Cron();
	$vmbx_instance['post-type'] = new PostType();

	// Only in frontend.
	if ( ! is_admin() ) {
		$vmbx_instance['shortcode'] = new Shortcode();
		$vmbx_instance['frontend']  = new Frontend();
	}

}
add_action( 'after_setup_theme', 'Meloniq\VirtualMailbox\setup' );

