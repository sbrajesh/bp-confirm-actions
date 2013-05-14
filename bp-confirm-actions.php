<?php
/**
 * Plugin Name: BP Confirm Actions
 * Version: 1.0.2
 * Author: Brajesh Singh
 * Author URI: http://buddydev.com/members/sbrajesh/
 * Plugin URI: http://buddydev.com/plugins/bp-confirm-actions/
 * Description: Makes sure that the user confirm before cancelling friendship/leaving group/unfollowing other users 
 * License: GPL
 * Last Updated: May 14, 2013
 */

class BPConfirmActionsHelper{
    
    private static $instance;
    
    private function __construct() {
        
        add_action('plugins_loaded',array($this,'load_textdomain'));
        
        add_filter('bp_get_add_friend_button',array($this,'filter_friendship_btn'));
        add_filter('bp_get_group_join_button',array($this,'filter_groups_membership_btn'));
        add_filter('bp_follow_get_add_follow_button',array($this,'filter_follow_btn'));
        
        add_action('bp_enqueue_scripts',array($this,'load_js'));
    }
    
    /**
     * get the singleton instance
     * 
     * @return BPConfirmActionsHelper
     */
    public static function get_instance(){
        
        if(!isset(self::$instance))
            self::$instance=new self();
        
        return self::$instance;
    }
    
    //load text domain
    function load_textdomain() {
        
       
         $locale = apply_filters( 'bp_confirm_actions_load_textdomain_get_locale', get_locale() );
        // if load .mo file
        if ( !empty( $locale ) ) {
            $mofile_default = sprintf( '%slanguages/%s.mo', plugin_dir_path(__FILE__), $locale );
            $mofile = apply_filters( 'bp_confirm_actions_load_textdomain_mofile', $mofile_default );
                    
                    // make sure file exists, and load it
                    if ( file_exists( $mofile ) ) 
                       
                        load_textdomain( 'bp-confirm-actions', $mofile );


        }
    }
    
    /**
     * Modify the button class for friendshi buttons
     * @param array $btn 
     * @return array $btn
     */
    function filter_friendship_btn($btn){
        if(!($btn['id']=='is_friend'||$btn['id']=='is_pending'))
            return $btn;
        //let us ask the confirm class

        $btn['link_class']='bp-needs-confirmation '.$btn['link_class'];

        return $btn;
    }
    
   /**
    *  FGilter group friendship button
    * @param array $btn
    * @return string
    */
    function filter_groups_membership_btn($btn){
        //if it is not leave group, we don't need to do anything
        if($btn['id']!='leave_group')
            return $btn;
        
        //let us add the confirm class
        $btn['link_class']='bp-needs-confirmation '.$btn['link_class'];

        return $btn;
    }
    /**
     *  Filter follow/unfollow button
     * 
     * @param array $btn
     * @return string
     */
    function filter_follow_btn($btn){
        //if it is not for unfollow, no need to do anything
        if($btn['id']!='following')
            return $btn;
        
        //if we are here, we are modifying it for unfollow
        $btn['link_class']='bp-needs-confirmation '.$btn['link_class'];

        return $btn;
    
    }

    /**
     * Load the required javascript file
     * 
     */
    public function load_js(){
        if(!is_user_logged_in())
            return ;
        //only for logged in user we need to load this file
        
        wp_enqueue_script('bp-confirm-js', plugin_dir_url(__FILE__).'_inc/bp-confirm.js', array('jquery'));
        
        $param=array('confirm_message'=>__('Are you really sure about this?','bp-confirm-actions'));
        wp_localize_script('bp-confirm-js', 'BPConfirmaActions', $param);
    }
    
    
    
    
}

BPConfirmActionsHelper::get_instance();

?>