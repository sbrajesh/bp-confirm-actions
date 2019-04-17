<?php
/**
 * Admin settings helper class for plugin
 *
 * @package bp-confirm-actions
 */

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Press_Themes\PT_Settings\Page;

/**
 * Class BP_Confirm_Actions_Admin_Settings_Helper
 */
class BP_Confirm_Actions_Admin_Settings_Helper {

	/**
	 * Page object
	 *
	 * @var Page
	 */
	private $page;

	/**
	 * Page slug
	 *
	 * @var string
	 */
	private $page_slug = '';

	/**
	 * Boot class
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Setup
	 */
	private function setup() {
		$this->page_slug = 'bp-confirm-actions-settings';

		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_setting_page' ) );
	}

	/**
	 * Initialize the admin settings panel and fields
	 */
	public function init() {
		global $pagenow;

		$page_slug = isset( $_GET['page'] ) ? trim( $_GET['page'] ) : '';

		if ( 'options.php' === $pagenow || $this->page_slug === $page_slug ) {
			$this->register_settings();
		}
	}

	/**
	 * Initialize settings
	 */
	private function register_settings() {
		$page = new Page( 'bp_confirm_actions_settings', __( 'BP confirm actions settings', 'bp-confirm-actions' ) );

		$panel   = $page->add_panel( 'general', __( 'General', 'bp-confirm-actions' ) );
		$section = $panel->add_section( 'general_settings', __( 'General settings', 'bp-confirm-actions' ) );

		$section->add_field(
			array(
				'name'    => 'enabled_for',
				'label'   => __( 'Enabled for', 'bp-confirm-actions' ),
				'type'    => 'multicheck',
				'options' => array(
					'cancel_friendship'         => __( 'Cancel friendship button', 'bp-confirm-actions' ),
					'cancel_friendship_request' => __( 'Cancel friendship request button', 'bp-confirm-actions' ),
					'leave_group'               => __( 'Leave group button', 'bp-confirm-actions' ),
					'unfollow'                  => __( 'Unfollow button', 'bp-confirm-actions' ),
					'delete_activity'           => __( 'Delete activity button', 'bp-confirm-actions' ),
				),
				'default' => array(
					'cancel_friendship' => 'cancel_friendship',
					'leave_group'       => 'leave_group',
				),
				'desc'    => __( 'This settings will not override default confirm actions by BuddyPress', 'bp-confirm-actions' ),
			)
		);

		$this->page = $page;

		do_action( 'bp_confirm_actions_admin_settings', $page );

		// allow enabling options.
		$page->init();
	}

	/**
	 * Render page
	 */
	public function render() {
		$this->page->render();
	}

	/**
	 * Add menu
	 */
	public function add_setting_page() {
		add_options_page(
			__( 'BP Confirm Actions Aettings', 'bp-confirm-actions' ),
			__( 'BP Confirm Actions', 'bp-confirm-actions' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'render' )
		);
	}
}