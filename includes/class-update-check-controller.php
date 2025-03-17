<?php
/**
 * Update Check controller controller.
 *
 * @version 1.0.0
 * @package Woo_Price_Based_Country_Update_Manager
 */

namespace WCPBC_Update_Manager;

use WCPBC_Update_Manager\Plugins\Pricebasedcountry;

defined( 'ABSPATH' ) || exit;

/**
 * WCPBC_Blocks_Controller class.
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
		add_action( 'wcpbc_update_manager_license_save', [ __CLASS__, 'flush_cache' ] );
	}

	/**
	 * Flush cache.
	 */
	public static function flush_cache() {
		delete_site_option( __CLASS__ );
	}

	/**
	 * Returns the plugins.
	 */
	private static function get_plugins() {
		return [ new Pricebasedcountry() ];
	}

	/**
	 * Init cache.
	 */
	private static function init_cache() {
		self::$cache = get_site_option( __CLASS__, [] );
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

			update_site_option( __CLASS__, self::$cache );
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
}
