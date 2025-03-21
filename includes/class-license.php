<?php
/**
 * Encapsulate a license data
 *
 * @version 1.0.0
 * @package Woo_Price_Based_Country_Update_Manager
 */

namespace WCPBC_Update_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * License class
 */
final class License {

	/**
	 * Data.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * License settings instance.
	 *
	 * @var \WCPBC_License_Settings
	 */
	protected $license_settings;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->license_settings = \WCPBC_License_Settings::instance();
		$this->set_defaults();
		$this->read();
	}

	/**
	 * Sets defaults.
	 */
	protected function set_defaults() {
		$this->data = [
			'key'          => '',
			'token'        => '',
			'expired'      => false,
			'expiring'     => false,
			'is_connected' => false,
			'expires'      => '',
			'is_valid'     => false,
		];
	}

	/**
	 * Read.
	 */
	protected function read() {
		$props   = [];
		$license = $this->license_settings->get_license_data();

		$props['key']          = $this->license_settings->get_license_key();
		$props['token']        = $this->license_settings->get_api_key();
		$props['expired']      = 'active' !== $license['status'];
		$props['expiring']     = 'yes' === $license['renewal_period'];
		$props['is_connected'] = ! empty( $props['key'] ) && false === $props['expired'] && $this->license_settings->is_license_active();
		$props['expires']      = empty( $license['expires'] ) ? '' : date_i18n( get_option( 'date_format' ), strtotime( $license['expires'] ) );
		$props['is_valid']     = $props['is_connected'] && $this->license_settings->is_valid_key();

		$this->set_properties( $props );
	}

	/**
	 * Set a collection of props in one go. Only sets using public methods.
	 *
	 * @param array $props Key value pairs to set. Key is the prop and should map to a setter function name.
	 */
	protected function set_properties( $props ) {
		foreach ( $props as $prop => $value ) {
			$setter = "set_$prop";
			if ( is_callable( [ $this, $setter ] ) ) {
				$this->{$setter}( $value );
			}
		}
	}

	/**
	 * Sets a single property.
	 *
	 * @param string $prop Property.
	 * @param mixed  $value Value.
	 */
	protected function set_property( $prop, $value ) {
		if ( isset( $this->data[ $prop ] ) && $this->data[ $prop ] !== $value ) {
			$this->data[ $prop ] = $value;
		}
	}

	/**
	 * Gets a single property.
	 *
	 * @param string $prop Property.
	 * @return mixed
	 */
	protected function get_property( $prop ) {
		return isset( $this->data[ $prop ] ) ? $this->data[ $prop ] : null;
	}

	/**
	 * Set key
	 *
	 * @param string $value Value.
	 */
	protected function set_key( $value ) {
		$this->set_property( 'key', $value );
	}

	/**
	 * Get key
	 *
	 * @return string
	 */
	public function get_key() {
		return $this->get_property( 'key' );
	}

	/**
	 * Set token
	 *
	 * @param string $value Value.
	 */
	protected function set_token( $value ) {
		$this->set_property( 'token', $value );
	}

	/**
	 * Get token
	 *
	 * @return string
	 */
	public function get_token() {
		return $this->get_property( 'token' );
	}

	/**
	 * Set expired
	 *
	 * @param bool $value Value.
	 */
	protected function set_expired( $value ) {
		$this->set_property( 'expired', $value );
	}

	/**
	 * Get expired
	 *
	 * @return bool
	 */
	public function get_expired() {
		return $this->get_property( 'expired' );
	}

	/**
	 * Set expiring
	 *
	 * @param bool $value Value.
	 */
	protected function set_expiring( $value ) {
		$this->set_property( 'expiring', $value );
	}

	/**
	 * Get expiring
	 *
	 * @return bool
	 */
	public function get_expiring() {
		return $this->get_property( 'expiring' );
	}

	/**
	 * Set is_connected
	 *
	 * @param bool $value Value.
	 */
	protected function set_is_connected( $value ) {
		$this->set_property( 'is_connected', $value );
	}

	/**
	 * Get is_connected
	 *
	 * @return bool
	 */
	public function get_is_connected() {
		return $this->get_property( 'is_connected' );
	}

	/**
	 * Set is_valid
	 *
	 * @param bool $value Value.
	 */
	protected function set_is_valid( $value ) {
		$this->set_property( 'is_valid', $value );
	}

	/**
	 * Get is_valid
	 *
	 * @return bool
	 */
	public function get_is_valid() {
		return $this->get_property( 'is_valid' );
	}

	/**
	 * Set expires
	 *
	 * @param string $value Value.
	 */
	protected function set_expires( $value ) {
		$this->set_property( 'expires', $value );
	}

	/**
	 * Get expires
	 *
	 * @return string
	 */
	public function get_expires() {
		return $this->get_property( 'expires' );
	}

	/**
	 * Returns the license key field name.
	 *
	 * @return string
	 */
	public function get_field_name() {
		return $this->license_settings->get_field_key( 'license_key' );
	}

	/**
	 * Saves the license from post data.
	 */
	public function save() {
		$this->license_settings->process_admin_options();
		$this->read();
		do_action( 'wcpbc_update_manager_license_save' );
	}

}
