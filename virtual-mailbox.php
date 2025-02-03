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
	global $virtual_mailbox;

	// Only in admin area.
	if ( is_admin() ) {
		$virtual_mailbox['admin-page']   = new AdminPage();
		$virtual_mailbox['settings']     = new Settings();
		$virtual_mailbox['list-table']   = new ListTable();
		$virtual_mailbox['email-single'] = new EmailSingle();
	}

	$virtual_mailbox['logger']    = new Logger();
	$virtual_mailbox['cron']      = new Cron();
	$virtual_mailbox['post-type'] = new PostType();
	$virtual_mailbox['shortcode'] = new Shortcode();
	$virtual_mailbox['frontend']  = new Frontend();

}
add_action( 'after_setup_theme', 'Meloniq\VirtualMailbox\setup' );

