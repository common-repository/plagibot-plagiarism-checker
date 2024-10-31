<?php

namespace WPPBPC\Inc\Settings\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @subpackage Admin_functions
 */


class Admin_functions {

	protected static $instance = null;

	private $options;

	private function __construct() {

		$this->options = get_option('plagibot_options');
		
		add_action( 'admin_init', array( $this, 'plugin_activation_redirect'));

		add_action( 'admin_menu', array( $this, 'add_settings_page') );
			

		add_action ( 'save_post', array($this, 'plagibot_save_post'), 10, 2 );



		$post_type =  (! isset($_GET['post_type']) ) ? 'post' : $_GET['post_type'];

		if( ! empty($this->options['api_key']) && in_array( $post_type, $this->options['post_types'])) {

			//the js  adds the plagiarism checker button for block editor
			add_action( 'admin_enqueue_scripts', array( $this, 'plagibot_admin_scripts' ) );

			//add plagibot plagiarism checker button for classic editor
			add_action( 'edit_form_after_title', array( $this, 'plagibot_pc_button') );

			//add input field for classic editor to detect if the plagiarism button is clicked
			add_action( 'post_submitbox_misc_actions', array( $this, 'plagibot_pc_field') );

			add_filter( 'post_row_actions', array( $this, 'add_quick_action_button'), 10, 2);

		}

		add_action( 'admin_init',  array( $this, 'save_settings')  );
		add_action( 'admin_init',  array( $this, 'plagiarism_checker_page')  );
		

		add_action( 'wp_ajax_parse_text', array( $this, 'parse_text') );
		// add_action( 'wp_ajax_nopriv_parse_text', array( $this, 'parse_text' )); 


		if( empty($this->options['api_key']) && ( ! isset($_GET['page']) || $_GET['page'] != 'plagibot-settings') ){
			add_action('admin_notices', array($this, 'wppbpc_admin_notice'));
		}

		if( get_transient('plagibot_message') ){
			add_action('admin_notices', array($this, 'plagibot_notices'));
		}

	}
	

	public static function init() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	function add_settings_page(){
		add_options_page( "Plagibot - Settings", "Plagibot", "manage_options", "plagibot-settings", array($this, "plagibot_settings") );
	}

	function plagibot_api_request( $api_endpoint, $request_data = null ){


		$request['headers'] 		= array( 'Content-Type' => 'Application/Json', 'url' => site_url() );
		$request['body'] 			=  wp_json_encode($request_data);
		$result 	= wp_remote_post($api_endpoint,$request);

		if( ! is_wp_error( $result ) ) {
			$response 	= wp_remote_retrieve_body($result);
			$res_array 	= json_decode($response,true);
			$http_code 		= $result['response']['code'];
			return array('http_code' => $http_code, 'response_json' => $response, 'response_array' => $res_array );
		}	
		else
			return array('http_code' => 404, 'response_json' => '', 'response_array' => array() );
			
		
	}


	public function wppbpc_admin_notice(){
		?>
			<div class="notice notice-warning is-dismissible">
				<p><strong>Plagibot Plagiarism Checker</strong> is not yet connected to your <a target="_blank" href="https://plagibot.com">Plagibot</a> account. To complete the connection please visit the <a href="<?php echo admin_url("options-general.php?page=plagibot-settings");?>">plugin settings page</a>.</p>
			</div>
		<?php
	}

	public function plagibot_notices(){
		?>
			<div class="notice notice-<?php echo esc_attr(get_transient('plagibot_message')['type']); ?> is-dismissible">
				<p><?php echo esc_attr(get_transient('plagibot_message')['message']); ?></p>
			</div>
		<?php

	}




	public function plagibot_admin_scripts(){

		wp_enqueue_script( 'plagibot-admin-js', WPPBPC_URL . 'assets/js/plagibot.min.js', '', '1.0.0', false);
		wp_enqueue_style( 'plagibot-admin-css', WPPBPC_URL . 'assets/css/plagibot.min.css' , '', '1.0.0', false);

		if( isset($_GET['page']) && $_GET['page'] == 'plagibot-settings' ){
			wp_enqueue_script("plagibot-script",   WPPBPC_URL . 'assets/js/chosen.jquery.min.js',[], '1.8.7', false  );
			wp_enqueue_style("plagibot-chosen-css",  WPPBPC_URL . 'assets/css/chosen.min.css', [], '1.8.7' );
		}

	}


	public function plagiarism_checker_page(){


		if ( !empty($_GET['p']) && isset($_GET['action']) && $_GET['action'] === 'plagibot-plagiarism-checker' ) {
			// load the file if exists
			if( get_post($_GET['p']) ){

				$response = $this->plagibot_api_request('https://plagibot.com/wordpress/usage' , array('key' => $this->options['api_key']));

				require_once WPPBPC_INCLUDES . DIRECTORY_SEPARATOR ."pages" . DIRECTORY_SEPARATOR . "plagiarism_checker.php";
				exit();
			}

		}

	}




	public function save_settings(){

		if( ! isset($_POST['wppbpc_setup_nonce_field']))
			return;

		if( ! wp_verify_nonce($_POST['wppbpc_setup_nonce_field'], 'wppbpc_nounce_action') ){
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) { 
			return;
		}
		

		if( empty($_POST['plagibot_key']) ){
			set_transient('plagibot_message', array('type' => 'error', 'message' => "Please enter the valid API key"),3);
			exit( wp_redirect( admin_url( 'options-general.php?page=plagibot-settings' ) ));
		}

		if( empty($_POST['plagibot_post_type']) || ! is_array($_POST['plagibot_post_type']) ){
			set_transient('plagibot_message', array('type' => 'error', 'message' => "Please choose atleast one post type"),3);
			exit( wp_redirect( admin_url( 'options-general.php?page=plagibot-settings' ) ));
		}

		$this->options['post_types'] 	= map_deep( $_POST['plagibot_post_type'], 'sanitize_text_field' );
		$this->options['api_key']		= sanitize_text_field($_POST['plagibot_key']);

		//verify api key
		$response = $this->plagibot_api_request('https://plagibot.com/wordpress/key_validation' , array('key' => $this->options['api_key']) );
		if( $response['response_json'] == 'failed'){
			set_transient('plagibot_message', array('type' => 'error', 'message' => "Invalid API Key. Please e-mail us at <a href='mailto:hello@plagibot.com'>hello@plagibot.com</a> for additional support."),3);
			exit( wp_redirect( admin_url( 'options-general.php?page=plagibot-settings' ) ));
		}


		update_option('plagibot_options', $this->options);

		set_transient('plagibot_message', array('type' => 'success', 'message' => "Settings Saved! Plugin has been activated successfully."),2);

		exit( wp_redirect( admin_url( 'options-general.php?page=plagibot-settings' ) ));

	}

	function parse_text(){

		$response = $this->plagibot_api_request('https://plagibot.com/wordpress/search' , array('search_string' => $_POST['search_string'], 'key' => $this->options['api_key']) );
		
		if($response == 'limit_reached')
			exit();

		header("Content-type:application/json");
		exit($response['response_json']);

	}


	function plagibot_pc_button(){
		?>
		<div id="plagibot_r3fg24_switch-mode" style="">
			<button id="plagibot_r3fg24_switch-mode-button" onclick="document.getElementById('plagibot-metabox-btn').click()"  type="button" class="">
				<span id="plagibot_r3fg24_preview-btn-text"><?php echo esc_html_e( 'Plagiarism Check', 'plagibot' ); ?></span>
			</button>
		</div>				
		<?php 
	}

	function plagibot_pc_field(){
		echo "<div style='display:none'><input type='hidden' name='plagibot_button_click'><input type='button' name='save' id='plagibot-metabox-btn'></div>";
	}

	function plagibot_save_post($post_id, $post){

		if( !empty($_POST['plagibot_button_click']) ){
			wp_redirect("?p=" . (($post->post_parent) ?: $post_id) . "&action=plagibot-plagiarism-checker");
			exit();
		}

	}

	function plagibot_settings(){
		include_once WPPBPC_INCLUDES . 'pages/plagibot_settings.php';
	}

	function plugin_activation_redirect(){

		//redirect to settings page after activation
		if ( ! $this->options['redirected']) {
			$this->options['redirected'] = 1;
			update_option('plagibot_options', $this->options);
			exit( wp_redirect("options-general.php?page=plagibot-settings") );
		}

	}

	function add_quick_action_button ( $actions, $post ){
		$link = admin_url( "post.php?p={$post->ID}&action=plagibot-plagiarism-checker");
		$actions['publish'] = "<a href='$link'>Plagiarism Check</a>";
		return $actions;
	}


}



