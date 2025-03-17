<?php
/**
 * Price Based on Country PRO plugin.
 *
 * @since 1.0.0
 * @package Woo_Price_Based_Country_Update_Manager
 */

namespace WCPBC_Update_Manager\Plugins;

use WCPBC_Update_Manager\License;

defined( 'ABSPATH' ) || exit;

/**
 * Pricebasedcountry Class
 */
class Pricebasedcountry extends Plugin {

	/**
	 * Returns the plugin file.
	 *
	 * @return string
	 */
	public function get_file() {
		return WCPBC_PRO_PLUGIN_FILE;
	}

	/**
	 * Makes an API request and returns the results.
	 *
	 * @return stdClass
	 */
	protected function update_check_request() {
		$license = new License();

		if ( ! $license->get_is_valid() ) {
			return false;
		}

		$response = \WC_Plugin_API_Wrapper::update_check(
			1450,
			$license->get_token(),
			$license->get_key(),
			$this->get_version()
		);

		return $response;
	}
}
