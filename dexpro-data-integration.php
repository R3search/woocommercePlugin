<?php
/*
Plugin Name: Dexpro Data API Integration
Plugin URI: https://dwalltechnosoft.com/
Description: Plugin is used to integrate dexpro data api to woocommerce product
Version: 1.1
Author: Sikander Singh Shekhawat
Author URI: https://reenav.dexplay.com.au
License: GPLv2 or later
Text Domain: dexpro_data
*/
if ( ! function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a Dexpro Data API Integration plugin, not much I can do when called directly.';
	exit;
}

define( 'DEXPRO__VERSION', '1.0' );
define( 'DEXPRO__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

function dexpro_plugin_activation() {

	// Activation code here...
}
function dexpro_plugin_deactivation() {
	// De Activation code here...
}
register_activation_hook( __FILE__, 'dexpro_plugin_activation' );
register_deactivation_hook( __FILE__, 'dexpro_plugin_deactivation' );

require_once DEXPRO__PLUGIN_DIR . 'includes/admin_functions.php';




