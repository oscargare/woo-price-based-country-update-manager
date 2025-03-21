<?php
/**
 * Required functions (from WooCommerce)
 *
 * Loads this file only if WooCommerce is not active.
 *
 * @version 1.0.0
 * @package Woo_Price_Based_Country_Update_Manager
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_string_to_bool' ) ) {

	/**
	 * Converts a string (e.g. 'yes' or 'no') to a bool.
	 *
	 * @param string|bool $string String to convert. If a bool is passed it will be returned as-is.
	 * @return bool
	 */
	function wc_string_to_bool( $string ) {
		$string = $string ?? '';
		return is_bool( $string ) ? $string : ( 'yes' === strtolower( $string ) || 1 === $string || 'true' === strtolower( $string ) || '1' === $string );
	}
}

if ( ! function_exists( 'wc_clean' ) ) {

	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $var Data to sanitize.
	 * @return string|array
	 */
	function wc_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'wc_clean', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}
}
