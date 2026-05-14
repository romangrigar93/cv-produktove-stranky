<?php
/**
 * Plugin Name: Fix Zásilkovna Pickup Point Validation (Blocks Checkout)
 * Description: Adds server-side validation for Zásilkovna pickup point selection in WooCommerce Blocks checkout. Drop into wp-content/mu-plugins/.
 * Version: 1.0.0
 * Author: GODLIKE
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_store_api_checkout_update_order_from_request', 'godlike_validate_zasilkovna_pickup_point', 5, 2);

function godlike_validate_zasilkovna_pickup_point($order, $request) {
    $chosen = WC()->session->get('chosen_shipping_methods', []);
    $method = $chosen[0] ?? '';

    // Only apply to Zásilkovna methods
    if (strpos($method, 'zasilkovna>') !== 0) {
        return;
    }

    // Extract service slug (e.g. "cz-zasilkovna-domu-hd", "z-points", "packeta-zpoints-cz")
    $parts = explode('>', $method);
    $service_slug = explode(':', $parts[1] ?? '')[0];

    // Determine if this method requires a pickup point
    $needs_pickup = false;

    // 1) Check via Toret's own function
    if (function_exists('tzas_is_native_pickup_method')) {
        $needs_pickup = tzas_is_native_pickup_method($service_slug);
    }

    // 2) Check carrier DB table (pobocky = 1 means pickup required)
    if (!$needs_pickup) {
        global $wpdb;
        $table = $wpdb->prefix . 'zasilkovna_dopravci';

        $has_branches = $wpdb->get_var($wpdb->prepare(
            "SELECT pobocky FROM {$table} WHERE slug = %s AND pobocky = 1 LIMIT 1",
            $service_slug
        ));

        if ($has_branches) {
            $needs_pickup = true;
        }
    }

    if (!$needs_pickup) {
        return;
    }

    // Validate that pickup data was submitted
    $data = $request['extensions']['tzas-block-parcelshop'] ?? [];
    $pickup_json = $data['tzas_message'] ?? '';

    if (empty($pickup_json)) {
        throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
            'zasilkovna_missing_pickup_point',
            __('Pro zvolenou dopravu je nutné vybrat výdejní místo.', 'godlike'),
            400
        );
    }

    $pickup = json_decode($pickup_json, true);

    if (!is_array($pickup) || empty($pickup['id'])) {
        throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
            'zasilkovna_invalid_pickup_point',
            __('Pro zvolenou dopravu je nutné vybrat výdejní místo.', 'godlike'),
            400
        );
    }
}
