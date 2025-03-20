<?php
/**
 * Plugin Name:  WooCommerce Price Based on Country Pro -  Update Manager
 * Plugin URI: https://www.pricebasedcountry.com/
 * Description:  Receive updates from Pricebasedcountry.com
 * Version: 0.1.0
 * Author: Oscar Gare
 * Author URI: https://www.pricebasedcountry.com/
 * Domain Path: /languages/
 * Text Domain: woo-price-based-country-update-manager
 * Requires PHP: 7.0
 * License: GPLv2
 *
 * @package Woo_Price_Based_Country_Update_Manager
 */

namespace WCPBC_Update_Manager;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WCPBC_UPDATE_MANAGER_PLUGIN_FILE' ) ) {
	define( 'WCPBC_UPDATE_MANAGER_PLUGIN_FILE', __FILE__ );
}


(function() {

	if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
		return;
	}

	/**
	 * Plugin activation.
	 */
	register_activation_hook(
		__FILE__,
		function() {
			if ( ! is_network_admin() ) {
				wp_die( 'Install this plugin only in the network admin!' );
			}
		}
	);
	/**
	 * Register autoload.
	 */
	spl_autoload_register(
		function( $classname ) {

			if ( substr( $classname, 0, 21 ) !== 'WCPBC_Update_Manager\\' ) {
				return;
			}

			$pieces    = array_map( 'strtolower', explode( '\\', $classname ) );
			$pieces[0] = '/includes';
			$filename  = '/class-' . str_replace( '_', '-', strtolower( array_pop( $pieces ) ) ) . '.php';
			$path      = dirname( __FILE__ ) . implode( '/', $pieces ) . $filename;

			if ( is_readable( $path ) ) {
				include_once $path;
			}
		}
	);

	/**
	 * Init the plugin.
	 */
	add_action(
		'plugins_loaded',
		function() {
			if ( ! Loader::init() ) {
				return;
			}

			Settings::init();
			Update_Check_Controller::init();
		},
		100
	);
})();
