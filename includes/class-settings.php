<?php
/**
 * Settings class
 *
 * @version 1.0.0
 * @package Woo_Price_Based_Country_Update_Manager
 */

namespace WCPBC_Update_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Settings class
 */
final class Settings {

	/**
	 * Instance of License.
	 *
	 * @var License
	 */
	protected $license;

	/**
	 * The single instance of the class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Init settings.
	 *
	 * @return void
	 */
	public static function init() {
		if ( null === static::$instance && is_main_site() && ! is_network_admin() ) {
			static::$instance = new static();
		}
	}

	/**
	 * Constructor
	 *
	 * @return void
	 */
	private function __construct() {
		$this->license = new License();

		$this->init_hooks();
	}

	/**
	 * Add callbacks to hooks.
	 */
	protected function init_hooks() {
		add_action( 'after_plugin_row_' . plugin_basename( WCPBC_UPDATE_MANAGER_PLUGIN_FILE ), [ $this, 'render' ], 20 );
		add_action( 'admin_print_styles-plugins.php', [ $this, 'print_styles' ] );
		add_action( 'admin_print_footer_scripts-plugins.php', [ $this, 'print_scripts' ] );
		add_action( 'wp_ajax_wcpbc_update_manager_settings_save', [ $this, 'save' ] );
	}

	/**
	 * Render settings.
	 */
	public function render() {
		?>
		<tr id="woocommerce-price-based-on-country-update-manager-settings-row" class="active">
			<td colspan="4">
			<div id="woocommerce-price-based-on-country-update-manager-settings">
				<div class="wcpbc-update-manager-settings-form">
				<?php
				$this->render_form();
				?>
				</div>
			</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render settings form.
	 */
	protected function render_form() {
		$form_id = 'wcpbc-update-manager-settings';

		printf(
			'<label for="%1$s">%2$s</label>',
			esc_attr( "{$form_id}-license-key" ),
			esc_html__( 'License key', 'woo-price-based-country-update-manager' )
		);
		printf(
			'<input type="text" form="%1$s" name="%2$s" id="%1$s-%2$s" autocomplete="off" placeholder="%3$s" value="%4$s" %5$s required/>',
			esc_attr( $form_id ),
			$this->license->get_field_name(),
			esc_attr__( 'Paste your license key here', 'woo-price-based-country-update-manager' ),
			( $this->license->get_is_connected() ? esc_attr( '...' . substr( $this->license->get_key(), -5 ) ) : esc_attr( $this->license->get_key() ) ),
			( $this->license->get_is_connected() ? 'disabled' : '' )
		);
		printf(
			'<button class="button" form="%1$s" onclick="(e)=>{e.preventDefaul();}">%2$s</button>',
			esc_attr( $form_id ),
			( $this->license->get_is_connected() ? esc_html__( 'Deactivate', 'woo-price-based-country-update-manager' ) : esc_html__( 'Activate', 'woo-price-based-country-update-manager' ) )
		);
		printf(
			'<input type="hidden" form="%1$s" name="save" id="%1$s-save" value="%2$s" />',
			esc_attr( $form_id ),
			( $this->license->get_is_connected() ? 'deactivate' : '' )
		);
		printf(
			'<input type="hidden" form="%1$s" name="action" id="%1$s-action" value="wcpbc_update_manager_settings_save" />',
			esc_attr( $form_id )
		);
		printf(
			'<input type="hidden" form="%1$s" name="%2$s" id="%1$s-%2$s" value="%3$s" />',
			esc_attr( $form_id ),
			'security',
			esc_attr( wp_create_nonce( "{$form_id}-save" ) )
		);
	}

	/**
	 * Print syles
	 */
	public function print_styles() {
		?>
		<style>
			.plugins tr.active[data-plugin="woo-price-based-country-update-manager/woo-price-based-country-update-manager.php"] td,
			.plugins tr.active[data-plugin="woo-price-based-country-update-manager/woo-price-based-country-update-manager.php"] th {
				box-shadow: none;
			}
			#woocommerce-price-based-on-country-update-manager-settings-row > td {
				border-left: 4px solid #72aee6;
				padding: 0;
			}
			#woocommerce-price-based-on-country-update-manager-settings {
				margin: 0px 40px 15px;
			}

			#woocommerce-price-based-on-country-update-manager-settings > .wcpbc-update-manager-settings-form {
				display: flex;
				flex-direction: row;
				align-items: center;
				gap: 8px;
			}

			#woocommerce-price-based-on-country-update-manager-settings > .wcpbc-update-manager-settings-form input[type="text"] {
				min-width: 20em;
			}
		</style>
		<?php
	}

	/**
	 * Print scripts
	 */
	public function print_scripts() {
		?>
		<form id="wcpbc-update-manager-settings"></form>
		<script>
			;( function( $ ) {
				$('#wcpbc-update-manager-settings').submit( function(event) {
					event.preventDefault();

					$('button[form="wcpbc-update-manager-settings"]').prop('disabled', true);

					const data = $(this).serializeArray();

					$.post({
						url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						data: data,
						dataType: 'html',
						success: function( response ) {
							$('tr#woocommerce-price-based-on-country-update-manager-settings-row').replaceWith(response);
						},
						fail: function() {
							$('button[form="wcpbc-update-manager-settings"]').prop('disabled', false);
						}
					});
				});
			})( jQuery );
		</script>
		<?php

	}

	/**
	 * Save
	 */
	public function save() {
		check_admin_referer( 'wcpbc-update-manager-settings-save', 'security' );
		$this->license->save();
		$this->render();
		exit();
	}

}
