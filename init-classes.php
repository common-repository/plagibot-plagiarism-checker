<?php 
namespace WPPBPC\Inc;

use WPPBPC\Inc\Settings\Admin\Admin_functions;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WPPBPC_INCLUDES . "classes/admin-functions.php"; 


Admin_functions::init();

