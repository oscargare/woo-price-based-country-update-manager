<?php
/**
 * Update Check controller controller.
 *
 * @version 1.0.0
 * @package Woo_Price_Based_Country_Update_Manager
 */

namespace WCPBC_Update_Manager;

use WCPBC_Update_Manager\Plugins\Pricebasedcountry;
use WCPBC_Update_Manager\Plugins\My_Self;

defined( 'ABSPATH' ) || exit;

/**
 * Update_Check_Controller class.
 */
class Update_Check_Controller {

	/**
	 * Cache data.
	 *
	 * @var array
	 */
	private static $cache = false;

	/**
	 * Init.
	 */
	public static function init() {
		add_filter( 'pre_set_site_transient_update_plugins', [ __CLASS__, 'update_plugins' ], 25 );
		add_filter( 'plugins_api', [ __CLASS__, 'plugin_information' ], 25, 3 );
		add_action( 'wcpbc_update_manager_license_save', [ __CLASS__, 'flush_cache' ] );
	}

	/**
	 * Flush cache.
	 */
	public static function flush_cache() {
		delete_site_transient( __CLASS__ );
	}

	/**
	 * Returns the plugins.
	 */
	private static function get_plugins() {
		return [
			new Pricebasedcountry(),
			new My_Self(),
		];
	}

	/**
	 * Init cache.
	 */
	private static function init_cache() {
		self::$cache = get_site_transient( __CLASS__, [] );

		if ( ! is_array( self::$cache ) || ! isset( self::$cache['data'], self::$cache['timeout'] ) || time() > self::$cache['timeout'] ) {
			self::$cache = [];
		}
	}

	/**
	 * Updates cache.
	 */
	private static function update_cache() {
		if ( isset( self::$cache['dirty'] ) && self::$cache['dirty'] ) {
			unset( self::$cache['dirty'] );
			self::$cache['timeout'] = strtotime( '+10 hours', time() );

			set_site_transient( __CLASS__, self::$cache, DAY_IN_SECONDS );
		}
	}

	/**
	 * Returns the cache data.
	 *
	 * @param string $key Cache key.
	 * @return array Update data
	 */
	private static function get_cache( $key ) {
		if ( false === self::$cache ) {
			self::init_cache();
		}

		if ( ! isset( self::$cache['data'][ $key ] ) ) {
			return false;
		}

		return self::$cache['data'][ $key ];
	}

	/**
	 * Sets the cache data.
	 *
	 * @param string $key Cache key.
	 * @param array  $data Data to set.
	 */
	private static function set_cache( $key, $data ) {
		if ( false === self::$cache ) {
			self::init_cache();
		}

		self::$cache['data'][ $key ] = $data;
		self::$cache['dirty']        = true;
	}

	/**
	 * Checks for available plugins updates.
	 *
	 * @param array $transient Update array build by WordPress.
	 * @return array Modified update array with custom plugin data.
	 */
	public static function update_plugins( $transient ) {

		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		foreach ( self::get_plugins() as $plugin ) {

			$item = self::get_cache( $plugin->get_name() );

			if ( false === $item ) {

				$plugin->check();

				$item = [
					'id'          => wp_parse_url( esc_url_raw( $plugin->get_package() ), PHP_URL_HOST ) . '-' . $plugin->get_slug(),
					'slug'        => $plugin->get_slug(),
					'plugin'      => $plugin->get_name(),
					'new_version' => $plugin->get_new_version(),
					'tested'      => $plugin->get_tested(),
					'package'     => $plugin->get_package(),
				];

				self::set_cache( $plugin->get_name(), $item );
			}

			unset( $transient->no_update[ $plugin->get_name() ], $transient->response[ $plugin->get_name() ] );

			if ( version_compare( $plugin->get_version(), $item['new_version'], '<' ) ) {
				$transient->response[ $plugin->get_name() ] = (object) $item;
			} else {
				$transient->no_update[ $plugin->get_name() ] = (object) $item;
			}
		}

		self::update_cache();

		return $transient;
	}

	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 * @param mixed  $result The result object or array. Default false.
	 * @param string $action The type of information being requested from the Plugin Installation API.
	 * @param object $args Plugin API arguments.
	 * @return object
	 */
	public static function plugin_information( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) ) {
			return $result;
		}

		foreach ( self::get_plugins() as $plugin ) {
			if ( $args->slug !== $plugin->get_slug() ) {
				continue;
			}

			$plugin->information();

			$result = (object) [
				'name'         => $plugin->get_title(),
				'slug'         => $plugin->get_slug(),
				'author'       => $plugin->get_author(),
				'tested'       => $plugin->get_tested(),
				'version'      => $plugin->get_new_version(),
				'homepage'     => $plugin->get_homepage(),
				'sections'     => $plugin->get_sections(),
				'last_updated' => $plugin->get_last_updated(),
			];
		}

		return $result;
	}
}
