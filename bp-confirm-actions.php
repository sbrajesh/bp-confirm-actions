<?php
/**
 * Plugin Name: BP Confirm Actions
 * Version: 1.0.3
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

		add_action( 'bp_init', array( $this, 'load_textdomain' ) );

		add_filter( 'bp_get_add_friend_button', array( $this, 'filter_friendship_btn' ) );
		add_filter( 'bp_get_group_join_button', array( $this, 'filter_groups_membership_btn' ) );
		add_filter( 'bp_follow_get_add_follow_button', array( $this, 'filter_follow_btn' ) );

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
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'bp-confirm-actions', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Modify the button class for friendship buttons
	 *
	 * @param array $btn button args.
	 *
	 * @return array $btn
	 */
	public function filter_friendship_btn( $btn ) {

		if ( ! is_array( $btn ) ) {
			return $btn;
		}

		if ( ! ( $btn['id'] == 'is_friend' || $btn['id'] == 'is_pending' ) ) {
			return $btn;
		}

		// let us ask the confirm class.
		$btn['link_class'] = 'bp-needs-confirmation ' . $btn['link_class'];

		return $btn;
	}

	/**
	 *  Filter group friendship button
	 *
	 * @param array $btn button args.
	 *
	 * @return array
	 */
	public function filter_groups_membership_btn( $btn ) {

		if ( ! is_array( $btn ) ) {
			return $btn;
		}

		// if it is not leave group, we don't need to do anything.
		if ( $btn['id'] !== 'leave_group' ) {
			return $btn;
		}

		// let us add the confirm class.
		$btn['link_class'] = 'bp-needs-confirmation ' . $btn['link_class'];

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

		if ( ! is_array( $btn ) ) {
			return $btn;
		}

		// if it is not for un-follow, no need to do anything.
		if ( $btn['id'] != 'following' ) {
			return $btn;
		}

		// if we are here, we are modifying it for un-follow.
		$btn['link_class'] = 'bp-needs-confirmation ' . $btn['link_class'];

		return $btn;
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
