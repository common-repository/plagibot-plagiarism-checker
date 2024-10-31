<?php

namespace WPPBPC\Inc\activate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @subpackage Activation
 */
class Activation {

	protected static $instance = null;

	private function __construct() {
      $this->activate();
	}

	public static function init() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function activate(  ) {

		$options = get_option('plagibot_options');

		if( $options )
			return;

		//if it is a bulk activation of multiple plugins, then stop redirect
		if (
			( isset( $_REQUEST['action'] ) && 'activate-selected' === $_REQUEST['action'] ) &&
			( isset( $_POST['checked'] ) && count( $_POST['checked'] ) > 1 ) ) {

			add_option('plagibot_options', array('redirected' => 1, 'api_key' => "", 'post_types' => array('post', 'page')));

		}else{

			add_option('plagibot_options', array('redirected' => 0, 'api_key' => "", 'post_types' => array('post', 'page')));

		}
      
	}




}
