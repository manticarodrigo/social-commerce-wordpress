<?php
/**
 * The admin-setting functionality of the plugin.
 *
 * @link       tatvic.com
 * @since      1.0.0
 *
 * @package    Enhanced_Ecommerce_Google_Analytics
 * @subpackage Enhanced_Ecommerce_Google_Analytics/admin
 */

/**
 * The admin-setting functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Enhanced_Ecommerce_Google_Analytics
 * @subpackage Enhanced_Ecommerce_Google_Analytics/admin
 * @author     Chiranjiv Pathak <chiranjiv@tatvic.com>
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class Enhanced_Ecommerce_Google_Settings {
	
	public static function add_update_settings($settings) {
		if ( !get_option($settings)) {
			$ee_options = array();
			foreach ($_POST as $key => $value) {
				if(!isset($_POST[$key])){
					$_POST[$key] = '';
				}
				if(isset($_POST[$key])) {
					$ee_options[$key] = $_POST[$key];
				}
			}
				add_option( $settings, serialize( $ee_options ) );
		}
		else {
				$get_ee_settings = unserialize(get_option($settings));
				foreach ($get_ee_settings as $key => $value) {
					if(!isset($_POST[$key])){
						$_POST[$key] = '';
					}
					if( $_POST[$key] != $value ) {
						$get_ee_settings[$key] =  $_POST[$key];
					}
					
				}
				foreach($_POST as $key=>$value){
					if(!array_key_exists($key,$get_ee_settings)){
						$get_ee_settings[$key] =  $value;
					}
				}
					update_option($settings, serialize( $get_ee_settings ));
		}
		self::admin_notice__success();
	}
	
	private static function admin_notice__success() {
		$class = 'notice notice-success';
		$message = __( 'Your settings have been saved.', 'sample-text-domain' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		
	}
	
}

?>