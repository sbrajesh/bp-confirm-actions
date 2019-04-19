<?php
/**
 * Plugin Name: BP Confirm Actions
 * Version: 1.0.4
 * Author: Brajesh Singh
 * Author URI: https://buddydev.com/members/sbrajesh/
 * Plugin URI: https://buddydev.com/plugins/bp-confirm-actions/
 * Description: Makes sure that the user confirm before cancelling friendship/leaving group/unfollowing other users
 * License: GPL
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Confirm actions.
 */
class BPConfirmActionsHelper {

	/**
	 * Singleton instance.
	 *
	 * @var BPConfirmActionsHelper
	 */
	private static $instance;

	/**
	 * Constructor.
	 */
	private function __construct() {
		register_activation_hook( __FILE__, array( $this, 'on_activation' ) );

		add_action( 'bp_init', array( $this, 'load_textdomain' ) );
		add_action( 'plugin_loaded', array( $this, 'load_admin' ), 9996 );

		add_filter( 'bp_get_add_friend_button', array( $this, 'filter_friendship_btn' ) );
		add_filter( 'bp_follow_get_add_follow_button', array( $this, 'filter_follow_btn' ) );
		add_filter( 'bp_get_activity_delete_link', array( $this, 'filter_activity_delete_btn' ) );

		// For Nouveau templates
		add_filter( 'bp_core_get_js_strings', array( $this, 'nouveau_modify_localize_args' ) );

		add_action( 'bp_enqueue_scripts', array( $this, 'load_js' ) );
	}

	/**
	 * Get the singleton instance
	 *
	 * @return BPConfirmActionsHelper
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * On activation
	 */
	public function on_activation() {

		if ( ! get_option( 'bp_confirm_actions_settings' ) ) {
			update_option( 'bp_confirm_actions_settings', array(
				'enabled_for' => array(
					'cancel_friendship' => 'cancel_friendship',
					'leave_group'       => 'leave_group',
				),
			) );
		}
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'bp-confirm-actions', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Load plugin admin section
	 */
	public function load_admin() {

		if ( ! function_exists( 'buddypress' ) || ! is_admin() || wp_doing_ajax() ) {
			return;
		}

		$path = plugin_dir_path( __FILE__ );

		require_once $path . 'admin/pt-settings/pt-settings-loader.php';
		require_once $path . 'admin/class-bp-confirm-actions-admin-settings-helper.php';

		BP_Confirm_Actions_Admin_Settings_Helper::boot();
	}

	/**
	 * Filter nouveau localize confirm message
	 *
	 * @param array $params Params.
	 *
	 * @return array
	 */
	public function nouveau_modify_localize_args( $params ) {

		if ( ! function_exists( 'bp_nouveau' ) ) {
			return $params;
		}

		if ( ! isset( $params['is_friend_confirm'] ) && $this->needs_confirmation( 'cancel_friendship' ) ) {
			$params['is_friend_confirm'] = __( 'Are you sure?', 'bp-confirm-action' );
		}

		if ( ! isset( $params['pending_confirm'] ) && $this->needs_confirmation( 'cancel_friendship_request' ) ) {
			$params['pending_confirm'] = __( 'Are you sure?', 'bp-confirm-action' );
		}

		if ( ! isset( $params['leave_group_confirm'] ) && $this->needs_confirmation( 'leave_group' ) ) {
			$params['leave_group_confirm'] = __( 'Are you sure', 'bp-confirm-action' );
		}

		return $params;
	}

	/**
	 * Modify the button class for friendship buttons
	 *
	 * @param array $btn button args.
	 *
	 * @return array $btn
	 */
	public function filter_friendship_btn( $btn ) {

		if ( function_exists( 'bp_nouveau' ) || ! is_array( $btn ) ) {
			return $btn;
		}

		if ( 'is_friend' == $btn['id'] && $this->needs_confirmation( 'cancel_friendship' ) ) {
			// let us ask the confirm class.
			$btn['link_class'] = 'bp-needs-confirmation ' . $btn['link_class'];
		} elseif ( 'is_pending' == $btn['id'] && $this->needs_confirmation( 'cancel_friendship_request' ) ) {
			// let us ask the confirm class.
			$btn['link_class'] = 'bp-needs-confirmation ' . $btn['link_class'];
		}

		return $btn;
	}

	/**
	 *  Filter follow/un-follow button
	 *
	 * @param array $btn args.
	 *
	 * @return array
	 */
	public function filter_follow_btn( $btn ) {

		if ( ! is_array( $btn ) || ! $this->needs_confirmation( 'unfollow' ) ) {
			return $btn;
		}

		// if it is not leave group, we don't need to do anything.
		if ( 'following' == $btn['id'] && $this->needs_confirmation( 'unfollow' ) ) {
			// let us add the confirm class.
			$btn['link_class'] = 'bp-needs-confirmation ' . $btn['link_class'];
		}

		return $btn;
	}

	/**
	 * @param $link
	 *
	 * @return mixed
	 */
	public function filter_activity_delete_btn( $link ) {

		if ( function_exists( 'bp_nouveau' ) || ! $this->needs_confirmation( 'delete_activity' ) ) {
			return $link;
		}

		if ( strpos( $link, 'class="' ) !== false ) {
			$link = str_replace( 'class="', 'class="bp-needs-confirmation ', $link );
		}

		return $link;
	}

	/**
	 * Check if needs confirmation or not
	 *
	 * @param string $action Action to check.
	 *
	 * @return bool
	 */
	private function needs_confirmation( $action ) {
		$enabled = get_option( 'bp_confirm_actions_settings', array(
			'enabled_for' => array(
				'cancel_friendship' => 'cancel_friendship',
				'leave_group'       => 'leave_group',
			),
		) );

		if ( empty( $enabled['enabled_for'] ) || ! in_array( $action, $enabled['enabled_for'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Load the required javascript file
	 */
	public function load_js() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		// only for logged in user we need to load this file.
		wp_enqueue_script( 'bp-confirm-js', plugin_dir_url( __FILE__ ) . '_inc/bp-confirm.js', array( 'jquery' ) );

		$param = array( 'confirm_message' => __( 'Are you really sure about this?', 'bp-confirm-actions' ) );
		wp_localize_script( 'bp-confirm-js', 'BPConfirmaActions', $param );
	}

}

BPConfirmActionsHelper::get_instance();
