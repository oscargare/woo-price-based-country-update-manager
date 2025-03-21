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
	 * Makes an update check API request and returns the results.
	 *
	 * @return object
	 */
	abstract protected function update_check_request();

	/**
	 * Returns the plugin information.
	 *
	 * @return object
	 */
	abstract protected function information_request();

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
		global $wp_version;

		$plugin_data = get_plugin_data( $this->get_file(), false, false );

		$this->data = [
			'title'        => isset( $plugin_data['Name'] ) ? $plugin_data['Name'] : '',
			'homepage'     => isset( $plugin_data['PluginURI'] ) ? $plugin_data['PluginURI'] : '',
			'author'       => sprintf( '<a href="%s">%s</a>', ( isset( $plugin_data['AuthorURI'] ) ? $plugin_data['AuthorURI'] : '#' ), ( isset( $plugin_data['Author'] ) ? $plugin_data['Author'] : '' ) ),
			'version'      => isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '',
			'name'         => plugin_basename( $this->get_file() ),
			'slug'         => plugin_basename( basename( $this->get_file(), '.php' ) ),
			'new_version'  => isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '',
			'tested'       => $wp_version,
			'package'      => false,
			'sections'     => [],
			'last_updated' => '',
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
	 * Returns title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->get_property( 'title' );
	}

	/**
	 * Returns homepage.
	 *
	 * @return string
	 */
	public function get_homepage() {
		return $this->get_property( 'homepage' );
	}

	/**
	 * Returns author.
	 *
	 * @return string
	 */
	public function get_author() {
		return $this->get_property( 'author' );
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
	 * Returns sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		return $this->get_property( 'sections' );
	}

	/**
	 * Returns last_updated.
	 *
	 * @return string
	 */
	public function get_last_updated() {
		return $this->get_property( 'last_updated' );
	}

	/**
	 * Updates the plugin with the latest update.
	 */
	public function check() {

		$data = $this->update_check_request();

		if ( is_wp_error( $data ) || ! is_object( $data ) ) {
			return;
		}

		foreach ( [ 'new_version', 'tested', 'package' ] as $prop ) {
			if ( ! isset( $data->{$prop} ) ) {
				continue;
			}
			$this->set_property( $prop, $data->{$prop} );
		}
	}

	/**
	 * Update the plugin with information.
	 */
	public function information() {
		$data = $this->information_request();

		if ( is_wp_error( $data ) || ! is_object( $data ) ) {
			return;
		}

		foreach ( [ 'new_version', 'sections', 'last_updated' ] as $prop ) {
			if ( ! isset( $data->{$prop} ) ) {
				continue;
			}
			$this->set_property( $prop, $data->{$prop} );
		}
	}

}
