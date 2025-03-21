<?php
/**
 * Autoupdates from the GitHub repository.
 *
 * @since 1.0.0
 * @package Woo_Price_Based_Country_Update_Manager
 */

namespace WCPBC_Update_Manager\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * My_Self Class
 */
class My_Self extends Plugin {

	/**
	 * Returns the plugin file.
	 *
	 * @return string
	 */
	public function get_file() {
		return WCPBC_UPDATE_MANAGER_PLUGIN_FILE;
	}

	/**
	 * Runs an API request and returns the result.
	 */
	protected function request() {
		$response = wp_safe_remote_get( 'https://api.github.com/repos/oscargare/woo-price-based-country-update-manager/releases/latest' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return json_decode( $response['body'] );
	}

	/**
	 * Makes an API request and returns the results.
	 *
	 * @return stdClass
	 */
	protected function update_check_request() {

		$data = $this->request();

		if ( ! ( is_object( $data ) && isset( $data->tag_name, $data->assets[0], $data->assets[0]->browser_download_url ) ) ) {
			return false;
		}

		return (object) [
			'new_version' => $data->tag_name,
			'package'     => $data->assets[0]->browser_download_url,
		];
	}

	/**
	 * Returns the plugin information.
	 *
	 * @return object
	 */
	protected function information_request() {
		$data = $this->request();

		if ( ! ( is_object( $data ) && isset( $data->tag_name, $data->html_url ) ) ) {
			return false;
		}

		return (object) [
			'new_version'  => $data->tag_name,
			'last_updated' => isset( $data->published_at ) ? $data->published_at : '',
			'sections'     => [
				'changelog' => sprintf(
					'<p>%s<br>%s</p>',
					esc_html__( 'See the changelog for this release on GitHub:', 'woo-price-based-country-update-manager' ),
					sprintf( '<a href="%1$s">%1$s</a>', esc_url( $data->html_url ) )
				),
			],
		];
	}
}
