<?php 
/*
Plugin Name: Plagibot Plagiarism Checker
Description: Plagiarism Checker by Plagibot: easily scan your posts for plagiarism before they are published. See results in real-time within seconds.   
Plugin URI: https://plagibot.com
Author: Plagibot
Author URI: https://plagibot.com/about-us
Text Domain: plagibot
Version: 1.0.0
Requires at least: 5.0
Tested up to: 6.0
*/


defined( 'ABSPATH' ) || die();
define( 'WPPBPC_VERSION', '1.0.0' );
define( 'WPPBPC_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "plagibot-plagiarism-checker" . DIRECTORY_SEPARATOR );
define( 'WPPBPC_URL', plugins_url( "",__FILE__ ) . DIRECTORY_SEPARATOR);
define( 'WPPBPC_INCLUDES', WPPBPC_DIR . "include" . DIRECTORY_SEPARATOR );
define( 'WPPBPC_PRO_URL', '#' );

register_activation_hook( __FILE__, 'plagibot_activation_hook' );
function  plagibot_activation_hook(){
	require_once WPPBPC_INCLUDES . "classes". DIRECTORY_SEPARATOR ."activation.php";
	\WPPBPC\Inc\activate\Activation::init();
}



/* Plugin's action button */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'plagibot_plugin_action_link' );
function plagibot_plugin_action_link( $links ) {
	$settings_link = array(
		'<a href="' . admin_url( 'options-general.php?page=plagibot-settings' ) . '">' . __( 'Settings' ) . '</a>',
	);

	return array_merge( $settings_link, $links );
}

/* initalize plugin's classes */
add_action('init', 'plagibot_init_plugin');
function plagibot_init_plugin(){	

	require_once   "init-classes.php";


}



