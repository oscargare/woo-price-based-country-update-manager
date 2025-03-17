<?php
/**
 * Encapsulate an plugin for updates.
 *
 * @since 1.0.0
 * @package Woo_Price_Based_Country_Update_Manager
 */

namespace WCPBC_Update_Manager\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Class
 */
abstract class Plugin {

	/**
	 * Data array.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Returns the plugin file.
	 *
	 * @return string
	 */
	abstract public function get_file();

	/**
	 * Makes an API request and returns the results.
	 *
	 * @return array
	 */
	abstract protected function update_check_request();

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->set_defaults();
	}

	/**
	 * Sets defaults.
	 */
	protected function set_defaults() {
		$plugin_data = get_plugin_data( $this->get_file(), false, false );

		$this->data = [
			'version'     => isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '',
			'name'        => plugin_basename( $this->get_file() ),
			'slug'        => plugin_basename( basename( $this->get_file(), '.php' ) ),
			'new_version' => isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '',
			'tested'      => '',
			'package'     => false,
		];
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
		return isset( $this->data[ $prop ] ) ? $this->data[ $prop ] : '';
	}

	/**
	 * Returns version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->get_property( 'version' );
	}

	/**
	 * Returns name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->get_property( 'name' );
	}

	/**
	 * Returns slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->get_property( 'slug' );
	}

	/**
	 * Returns new_version.
	 *
	 * @return string
	 */
	public function get_new_version() {
		return $this->get_property( 'new_version' );
	}

	/**
	 * Returns tested.
	 *
	 * @return string
	 */
	public function get_tested() {
		return $this->get_property( 'tested' );
	}

	/**
	 * Returns package.
	 *
	 * @return string
	 */
	public function get_package() {
		return $this->get_property( 'package' );
	}

	/**
	 * Returns the plugin check data.
	 */
	public function check() {

		$data = $this->update_check_request();

		if ( is_wp_error( $data ) || ! is_object( $data ) || ! isset( $data->new_version, $data->tested, $data->package ) ) {
			return;
		}

		$this->set_property( 'new_version', $data->new_version );
		$this->set_property( 'tested', $data->tested );
		$this->set_property( 'package', $data->package );
	}
}

