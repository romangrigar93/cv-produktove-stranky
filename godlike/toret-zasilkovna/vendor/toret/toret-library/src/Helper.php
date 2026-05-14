<?php

namespace ToretZasilkovna\Toret\Library;

use WC_Order;

class Helper
{
    public static function validateNumber($value, $default = 0): bool
    {
        if (!is_numeric($value) || $value <= 0) {
            $value = $default;
        }

        return $value;
    }

    public static function isToretDev(): bool
    {
        return str_contains( home_url(), 'toret.dev' ) || str_contains( home_url(), 'toret.show' );
    }

    public static function buildUrlFromSegments(string $base_url, ...$segments): string
    {
        $url = rtrim($base_url, '/');

        $normalized_segments = array_map(function ($segment) {
            return trim((string)$segment, '/');
        }, $segments);

        $filtered_segments = array_filter($normalized_segments, function ($segment) {
            return $segment !== '';
        });

        if (!empty($filtered_segments)) {
            $url .= '/' . implode('/', $filtered_segments);
        }

        return $url;
    }

    public static function getSiteUrl(): string
    {
        $site_url = get_site_url();
        if (is_multisite()) {
            $site_url = network_site_url();
        }

        return $site_url;
    }

    public static function getWooCountryName($country_code)
    {
        $countries = WC()->countries->get_countries();

        return $countries[$country_code] ?? null;
    }

    public static function getShippingCountryFromSource($cart = true, $order_id = ''): string
    {
        $shipping_country = '';

        if ($cart && isset(WC()->customer)) {
            $shipping_country = WC()->customer->get_shipping_country();

            if ($shipping_country == '') {

                $shipping_country = WC()->customer->get_billing_country();

            }

        } elseif ($order_id != '') {

            $order = wc_get_order($order_id);

            $shipping_country = $order->get_shipping_country();
            if ($shipping_country == '') {

                $shipping_country = $order->get_billing_country();

            }

        }

        return $shipping_country;
    }

    public static function getBillingCountryFromSource($cart = true, $order_id = ''): string
    {
        $shipping_country = '';

        if ($cart && isset(WC()->customer)) {
            $shipping_country = WC()->customer->get_billing_country();

            if ($shipping_country == '') {

                $shipping_country = WC()->customer->get_billing_country();

            }

        } elseif ($order_id != '') {

            $order = wc_get_order($order_id);

            $shipping_country = $order->get_billing_country();
            if ($shipping_country == '') {

                $shipping_country = $order->get_billing_country();

            }

        }

        return $shipping_country;
    }

    public static function getCartSubtotal($inbcludeVat,$includeShippings = true, $includeFees = true, $includeDiscounts = true): float
    {
        $items = WC()->cart->get_subtotal();

        $discounts = 0;
        if ($includeDiscounts) {
            $discounts = wc_format_decimal(WC()->cart->get_discount_total(), 2);
        }

        if ($inbcludeVat) {
            $items += WC()->cart->get_subtotal_tax();
        }

        return $items - $discounts;
    }

    public static function treatMissingOptionInSettingsArray($data, $option, $value, $group)
    {
        if (!isset($data[$option])) {
            $data[$option] = $value;
            update_option($group, $data);
        }
        return $data;
    }

    /**
     * @param WC_Order $order
     * @return bool
     */
    public static function is_order_only_virtual(WC_Order $order): bool
    {
        $is_only_virtual = true;

        if (count($order->get_items()) > 0) {
            foreach ($order->get_items() as $item_id => $order_item) {
                $product_id = $order_item->get_variation_id() ? $order_item->get_variation_id() : $order_item->get_product_id();

                $product = wc_get_product($product_id);

                if ($product && !$product->is_virtual()) {
                    $is_only_virtual = false;
                    break;
                }
            }
        } else {
            $is_only_virtual = false;
        }

        return $is_only_virtual;
    }

}