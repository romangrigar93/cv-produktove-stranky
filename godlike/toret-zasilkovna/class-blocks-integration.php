<?php
/**
 *
 */

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

define( 'TZAS_BLOCK_VERSION', '1.0.0' );

class Zasilkovna_Blocks_Integration implements IntegrationInterface {

    /**
     * The name of the integration.
     *
     * @return string
     */
    public function get_name() {
        return 'tzas-message';
    }

    /**
     * When called invokes any initialization/setup for the integration.
     */
    public function initialize() {
        $this->register_block_frontend_scripts();
        $this->register_block_editor_scripts();
        $this->register_editor_assets(); // Přidejte volání této metody
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }

    /**
     * Returns an array of script handles to enqueue in the frontend context.
     *
     * @return string[]
     */
    public function get_script_handles() {
        return array( 'checkout-block-frontend' );
    }

    /**
     * Returns an array of script handles to enqueue in the editor context.
     *
     * @return string[]
     */
    public function get_editor_script_handles() {
        return array( 'tzas-message-block-editor' );
    }

    /**
     * An array of key, value pairs of data made available to the block on the client side.
     *
     * @return array
     */
    public function get_script_data() {
        return array();
    }

    /**
     * Register scripts for delivery date block editor.
     *
     * @return void
     */
    public function register_block_editor_scripts() {
        $script_path       = '/build/index.js';
        $script_url        = plugins_url( 'toret-zasilkovna' . $script_path );
        $script_asset_path = plugins_url( 'toret-zasilkovna/build/index.asset.php' );
        $script_asset      = file_exists( $script_asset_path )
            ? require $script_asset_path
            : array(
                'dependencies' => array(),
                'version'      => $this->get_file_version( $script_asset_path ),
            );

        wp_register_script(
            'tzas-message-block-editor',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );
    }

    /**
     * Register scripts for frontend block.
     *
     * @return void
     */
    public function register_block_frontend_scripts() {
        $script_path       = '/build/checkout-block-frontend.js';
        $script_url        = plugins_url( '/toret-zasilkovna' . $script_path );
        $script_asset_path = WP_PLUGIN_DIR . '/toret-zasilkovna/build/checkout-block-frontend.asset.php';

        $script_asset = file_exists( $script_asset_path )
            ? require $script_asset_path
            : array(
                'dependencies' => array(),
                'version'      => $this->get_file_version( $script_asset_path ),
            );
        
        wp_register_script(
            'checkout-block-frontend',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );
    }

    /**
     * Get the file modified time as a cache buster if we're in dev mode.
     *
     * @param string $file Local path to the file.
     * @return string The cache buster value to use for the given file.
     */
    protected function get_file_version( $file ) {
        if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
            return filemtime( $file );
        }
        return TZAS_BLOCK_VERSION;
    }

    public function register_editor_assets() {


        // Přidáme data pro frontend.js
        $zasilkovna_option = get_option('zasilkovna_option');

        wp_localize_script(
            'checkout-block-frontend',
            'zasilkovnaAjax',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'apikey' => isset($zasilkovna_option['api_key']) ? $zasilkovna_option['api_key'] : '',
                'appIdentity' => WOOZASILKOVNAVER,
                'changeShippingAddress' => $zasilkovna_option['change_shipping_address'] ?? 'no',
                'codPointCheck' => $zasilkovna_option['cod_point_check'] ?? 'no',
            )
        );
    }

    public function enqueue_frontend_scripts() {
        if (!tzas_better_is_checkout()) {
            return;
        }

        wp_enqueue_script('tzas-block-parcelshop-frontend');
    }
}