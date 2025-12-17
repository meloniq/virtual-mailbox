<?php
/**
 * Virtual Mailbox Admin Page.
 *
 * @package Meloniq\VirtualMailbox
 */

namespace Meloniq\VirtualMailbox;

/**
 * Admin Page class.
 */
class AdminPage {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ), 10 );
	}

	/**
	 * Add menu page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		add_submenu_page(
			'options-general.php',
			__( 'Virtual Mailbox', 'virtual-mailbox' ),
			__( 'Virtual Mailbox', 'virtual-mailbox' ),
			'manage_options',
			'virtual-mailbox',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Virtual Mailbox', 'virtual-mailbox' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'vmbx_settings' );
				do_settings_sections( 'vmbx_settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
