<?php
namespace Meloniq\VirtualMailbox;

class Settings {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ), 10 );

	}

	/**
	 * Initialize settings.
	 *
	 * @return void
	 */
	public function init_settings() : void {
		// Section: General Settings.
		add_settings_section(
			'vmbx_section',
			__( 'General Settings', 'virtual-mailbox' ),
			array( $this, 'render_section' ),
			'vmbx_settings'
		);

		// Option: Days limit.
		$this->register_field_days_limit();

		// Option: Store unknown recipient emails.
		$this->register_field_store_unknown();

		// Info: Shortcode.
		$this->register_field_shortcode();
	}

	/**
	 * Render section.
	 *
	 * @return void
	 */
	public function render_section() : void {
		esc_html_e( 'Settings for the virtual mailbox.', 'virtual-mailbox' );
	}

	/**
	 * Register settings field Days limit.
	 *
	 * @return void
	 */
	public function register_field_days_limit() : void {
		$field_name    = 'vmbx_days_limit';
		$section_name  = 'vmbx_section';
		$settings_name = 'vmbx_settings';

		// phpcs:disable PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic
		register_setting(
			$settings_name,
			$field_name,
			array(
				'label'             => __( 'Days limit', 'virtual-mailbox' ),
				'description'       => __( 'Enter the number of days to keep emails.', 'virtual-mailbox' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => '',
				'show_in_rest'      => false,
			),
		);
		// phpcs:enable PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic

		add_settings_field(
			$field_name,
			__( 'Days limit', 'virtual-mailbox' ),
			array( $this, 'render_field_days_limit' ),
			$settings_name,
			$section_name,
			array(
				'label_for' => $field_name,
			),
		);
	}

	/**
	 * Register settings field Store unknown recipient emails.
	 *
	 * @return void
	 */
	public function register_field_store_unknown() : void {
		$field_name    = 'vmbx_store_unknown';
		$section_name  = 'vmbx_section';
		$settings_name = 'vmbx_settings';

		// phpcs:disable PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic
		register_setting(
			$settings_name,
			$field_name,
			array(
				'label'             => __( 'Store unknown recipient emails', 'virtual-mailbox' ),
				'description'       => __( 'Store emails of users that are not registered on the site.', 'virtual-mailbox' ),
				'type'              => 'boolean',
				'sanitize_callback' => 'boolval',
				'default'           => '',
				'show_in_rest'      => false,
			),
		);
		// phpcs:enable PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic

		add_settings_field(
			$field_name,
			__( 'Store unknown recipient emails', 'virtual-mailbox' ),
			array( $this, 'render_field_store_unknown' ),
			$settings_name,
			$section_name,
			array(
				'label_for' => $field_name,
			),
		);
	}

	/**
	 * Register settings field Shortcode.
	 *
	 * @return void
	 */
	public function register_field_shortcode() : void {
		$field_name    = 'vmbx_shortcode';
		$section_name  = 'vmbx_section';
		$settings_name = 'vmbx_settings';

		add_settings_field(
			$field_name,
			__( 'Shortcode', 'virtual-mailbox' ),
			array( $this, 'render_field_shortcode' ),
			$settings_name,
			$section_name,
			array(
				'label_for' => $field_name,
			),
		);
	}

	/**
	 * Render settings field Days limit.
	 *
	 * @return void
	 */
	public function render_field_days_limit() : void {
		$field_name = 'vmbx_days_limit';

		$days_limit = get_option( $field_name, '30' );
		?>
		<input type="number" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $days_limit ); ?>" class="regular-text">
		<p class="description"><?php esc_html_e( 'Enter the number of days to keep emails.', 'virtual-mailbox' ); ?></p>
		<?php
	}

	/**
	 * Render settings field Store unknown recipient emails.
	 *
	 * @return void
	 */
	public function render_field_store_unknown() : void {
		$field_name = 'vmbx_store_unknown';

		$store_unknown = get_option( $field_name, false );
		?>
		<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $store_unknown ); ?>>
		<p class="description"><?php esc_html_e( 'Store emails of users that are not registered on the site.', 'virtual-mailbox' ); ?></p>
		<?php
	}

	/**
	 * Render settings field Shortcode.
	 *
	 * @return void
	 */
	public function render_field_shortcode() : void {
		?>
		<p><?php esc_html_e( 'Use the following shortcode to display the mailbox:', 'virtual-mailbox' ); ?></p>
		<code>[vmbx_mailbox]</code>
		<?php
	}


}
