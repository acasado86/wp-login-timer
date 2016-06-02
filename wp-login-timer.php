<?php

/*
Plugin Name: wp-login-timer
Plugin URI: https://github.com/acasado86/wp-login-timer
Description: Simple security against brute-force attacks on login and register
Version: 1.0.1
Author: acasado
Author URI: 
License: GPLv2
*/

/* 
Copyright (C) 2016 acasado

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

class WPLoginTimer {
    static $instance = null;
    var $timer_wait = 1;
    
    static function & getInstance() {
        if (null == WPLoginTimer::$instance) {
            WPLoginTimer::$instance = new WPLoginTimer();
        }

        return WPLoginTimer::$instance;
    }
    
    function WPLoginTimer(){
        //Login Form
        add_action( 'wp_authenticate_user', array( $this, 'check_custom_authentication' ), 10, 1 );
        //Register Form
        add_filter( 'registration_errors', array( $this, 'filter_registration' ), 10, 1 );
        
        add_action( 'wp_ajax_nopriv_load_wp_login_nonce', array(&$this, 'ajax_load_wp_login_nonce') );
        add_action( 'login_enqueue_scripts',  array(&$this, 'login_enqueue_script') );
    }
    
    function login_enqueue_script() {
        $ajaxurl = admin_url( 'admin-ajax.php', 'relative' );
        wp_register_script('wp-login-timer-js', plugins_url("wp-login-timer.js", __FILE__), array('jquery'), '1.0.1');
        wp_enqueue_script('wp-login-timer-js');
        wp_localize_script('wp-login-timer-js', 'ajaxurl', $ajaxurl);
    }
    
    function ajax_load_wp_login_nonce(){
        sleep( $this->timer_wait );
        wp_nonce_field( 'login_timer', 'login_nonce' );
        die();
    }
    
    function check_custom_authentication($user) {
        if ( ! isset( $_POST['login_nonce'] )
                || ! wp_verify_nonce( $_POST['login_nonce'], 'login_timer' )
                ){
            return new WP_Error();
        }
        return $user;
    }
    
    function filter_registration($errors) {
        if ( ! isset( $_POST['login_nonce'] )
                || ! wp_verify_nonce( $_POST['login_nonce'], 'login_timer' )
                ){
            $errors->add( 'registerfail', sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you&hellip; please contact the <a href="mailto:%s">webmaster</a> !' ), get_option( 'admin_email' ) ) );
        }
        return $errors;
    }

    function activate_plugin() {
    }

    function desactivate_plugin() {
    }
    
}

$oWPLoginTimer = WPLoginTimer::getInstance();
register_activation_hook(__FILE__, array($oWPLoginTimer, 'activate_plugin'));
register_deactivation_hook(__FILE__, array( $oWPLoginTimer, 'desactivate_plugin'));
