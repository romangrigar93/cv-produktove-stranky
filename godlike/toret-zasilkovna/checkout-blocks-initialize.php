<?php

use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;

add_action(
    'woocommerce_blocks_loaded',
    function() {
        require_once 'class-blocks-integration.php';
        add_action(
            'woocommerce_blocks_checkout_block_registration',
            function( $integration_registry ) {
                $integration_registry->register( new Zasilkovna_Blocks_Integration() );
            }
        );

        if ( function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
            woocommerce_store_api_register_endpoint_data(
                array(
                    'endpoint'        => CheckoutSchema::IDENTIFIER,
                    'namespace'       => 'tzas-block-parcelshop',
                    'data_callback'   => 'tzas_data_callback',
                    'schema_callback' => 'tzas_schema_callback',
                    'schema_type'     => ARRAY_A,
                )
            );
        }
    }
);


/**
 * Callback function to register endpoint data for blocks.
 *
 * @return array
 */
function tzas_data_callback() {
    return array(
        'tzas_message' => '',
    );
}

/**
 * Callback function to register schema for data.
 *
 * @return array
 */
function tzas_schema_callback() {
    return array(
        'tzas_message'  => array(
            'description' => __( 'tzas-selected-branch', 'tzas-block-parcelshop' ),
            'type'        => array( 'string', 'null' ),
            'readonly'    => true,
        ),
    );
}