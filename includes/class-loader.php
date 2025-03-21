<?php
/**
 * Loads required classes from other plugins.
 *
 * @version 1.0.0
 * @package Woo_Price_Based_Country_Update_Manager
 */

namespace WCPBC_Update_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Loader class
 */
final class Loader {

	/**
	 * The single instance of the class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Init.
	 *
	 * @return bool
	 */
	public static function init() {
		if ( null === static::$instance ) {

			if ( defined( 'WCPBC_PRO_PLUGIN_FILE' ) ) {
				return false;
			}

			try {
				static::$instance = new static();
				return true;
			} catch ( \Exception $e ) {
				return false;
			}
		}
	}

	/**
	 * Constructor
	 *
	 * @throws \Exception When required file is missing.
	 * @return void
	 */
	private function __construct() {

		$file = WP_PLUGIN_DIR . '/woocommerce-price-based-country-pro-addon/woocommerce-price-based-country-pro-addon.php';

		if ( ! is_readable( $file ) ) {
			// Translators: %s File path.
			throw new \Exception( sprintf( __( 'Required file %s does not exist.', 'woo-price-based-country-update-manager' ), $file ) );
		}

		define( 'WCPBC_PRO_PLUGIN_FILE', $file );

		foreach ( $this->get_files() as $classname => $file ) {
			if ( class_exists( $classname ) ) {
				continue;
			}

			if ( ! is_readable( $file ) ) {
				// Translators: %s File path.
				throw new \Exception( sprintf( __( 'Required file %s does not exist.', 'woo-price-based-country-update-manager' ), $file ) );
			}
			require_once $file;
		}

		if ( ! function_exists( 'wc' ) ) {
			include_once dirname( __FILE__ ) . '/required-functions.php';
		}
	}

	/**
	 * Returns files to include.
	 */
	protected function get_files() {
		return [
			'WC_Settings_API'        => $this->wc_plugin_path() . '/includes/abstracts/abstract-wc-settings-api.php',
			'WC_Log_Levels'          => $this->wc_plugin_path() . '/includes/class-wc-log-levels.php',
			'WCPBC_Helper_Options'   => $this->wcpbc_path() . '/includes/class-wcpbc-helper-options.php',
			'WCPBC_Debug_Logger'     => $this->wcpbc_path() . '/includes/class-wcpbc-debug-logger.php',
			'WCPBC_License_Settings' => dirname( WCPBC_PRO_PLUGIN_FILE ) . '/includes/admin/class-wcpbc-license-settings.php',
			'WC_Plugin_API_Wrapper'  => dirname( WCPBC_PRO_PLUGIN_FILE ) . '/includes/admin/class-wc-plugin-api-wrapper.php',
		];
	}

	/**
	 * Returns WooCommerce path.
	 */
	protected function wc_plugin_path() {
		if ( defined( 'WC_PLUGIN_FILE' ) ) {
			return dirname( WC_PLUGIN_FILE );
		} else {
			return WP_PLUGIN_DIR . '/woocommerce';
		}
	}

	/**
	 * Returns WooCommerce Price Based on Country Basic path.
	 */
	protected function wcpbc_path() {
		if ( defined( 'WCPBC_PLUGIN_FILE' ) ) {
			return dirname( WCPBC_PLUGIN_FILE );
		} else {
			return WP_PLUGIN_DIR . '/woocommerce-product-price-based-on-countries';
		}
	}
}

