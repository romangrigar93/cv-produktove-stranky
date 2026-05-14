<?php

namespace ToretZasilkovna\Toret\Library;

use WC_Tax;
use WC_Order;
use WC_Product;
use WC_Order_Item_Product;
use WC_Cart;

if (!class_exists('WooCommerce')) {
    return;
}

class Taxes
{
    private function calculate_tax_data(float $amount, bool $is_vat_included, string $tax_rule, array $items, bool $is_shipping = false): array
    {
        $tax_info = $this->get_tax_rate_info($tax_rule, $items);

        $tax_rate_percent = $tax_info['rate'];
        $tax_class_slug_applied = $tax_info['class'];

        $taxes_on_amount = WC_Tax::calc_tax($amount, [['rate' => $tax_rate_percent, 'shipping' => $is_shipping ? 'yes' : 'no','compound' => 'no']], $is_vat_included);
        $tax_amount = WC_Tax::get_tax_total($taxes_on_amount);

        $amount_exclusive_of_tax = $is_vat_included ? $amount - $tax_amount : $amount;

        return [
            'amount_excl_tax' =>$amount_exclusive_of_tax,
            'tax_amount' => $tax_amount,
            'tax_rate_percent' => $tax_rate_percent,
            'tax_class_applied' => $tax_class_slug_applied,
        ];
    }

    private function get_tax_rate_info(string $tax_rule, array $items): array
    {
        if (strtolower($tax_rule) === 'inherit') {
            return $this->get_highest_tax_info_from_items($items);
        }

        $rates = WC_Tax::get_rates($tax_rule);
        if (!empty($rates)) {
            $rate_details = reset($rates);
            return [
                'rate' => (float) ($rate_details['rate'] ?? 0.0),
                'class' => $tax_rule
            ];
        }

        return ['rate' => 0.0, 'class' => $tax_rule];
    }

    private function get_highest_tax_info_from_items(array $items): array
    {
        $default_tax_info = ['rate' => 0.0, 'class' => ''];

        if (get_option('woocommerce_calc_taxes') !== 'yes') {
            return $default_tax_info;
        }

        if (empty($items)) {
            $std_rates = WC_Tax::get_rates();
            if (!empty($std_rates)) {
                $first_std_rate = reset($std_rates);
                return [
                    'rate' => (float) ($first_std_rate['rate'] ?? 0.0),
                    'class' => ''
                ];
            }
            return $default_tax_info;
        }

        $_tax_instance = new WC_Tax();
        $item_tax_details = [];

        foreach ($items as $item_obj_or_array) {
            $product_for_tax = null;

            if ($item_obj_or_array instanceof WC_Order_Item_Product) {
                $product_for_tax = $item_obj_or_array->get_product();
            } elseif (is_array($item_obj_or_array) && isset($item_obj_or_array['data']) && $item_obj_or_array['data'] instanceof WC_Product) {
                $product_for_tax = $item_obj_or_array['data'];
            }

            if (!$product_for_tax instanceof WC_Product || !$product_for_tax->is_taxable()) {
                continue;
            }

            if ($product_for_tax->is_virtual()) {
                continue;
            }

            if ($product_for_tax->is_type('variation')) {
                $parent_product = wc_get_product($product_for_tax->get_parent_id());
                if ($parent_product && $parent_product->is_virtual()) {
                    continue;
                }
            }

            $tax_class_slug = $product_for_tax->get_tax_class();
            $rates_for_item_class = $_tax_instance->get_rates($tax_class_slug);

            if (!empty($rates_for_item_class)) {
                $rate_details = reset($rates_for_item_class);
                $item_tax_details[] = [
                    'rate' => (float) ($rate_details['rate'] ?? 0.0),
                    'class' => $tax_class_slug,
                ];
            } else {
                $item_tax_details[] = [
                    'rate' => 0.0,
                    'class' => $tax_class_slug,
                ];
            }
        }

        if (empty($item_tax_details)) {
            return $default_tax_info;
        }

        usort($item_tax_details, fn($a, $b) => $b['rate'] <=> $a['rate']);
        return $item_tax_details[0];
    }

    public function get_fee_tax_data_for_cart(float $fee, bool $is_cod_fee = false, bool $is_fee_vat_included = false, string $cod_fee_tax_class_setting = 'inherit'): array
    {
        if (get_option('woocommerce_calc_taxes') !== 'yes') {
            return ['fee_excl_tax' => $fee, 'tax_amount' => 0.0, 'tax_rate_percent' => 0.0, 'tax_class_applied' => ''];
        }

        $base_tax_rule = $is_cod_fee ? $cod_fee_tax_class_setting : 'inherit';
        $effective_tax_rule = apply_filters('toret_fee_tax_rule', $base_tax_rule, $fee, $is_cod_fee, $is_fee_vat_included);

        $cart_instance = WC()->cart;
        $cart_items = ($cart_instance instanceof WC_Cart) ? $cart_instance->get_cart() : [];

        $tax_data = $this->calculate_tax_data($fee, $is_fee_vat_included, $effective_tax_rule, $cart_items);

        $tax_data['fee_excl_tax'] = $tax_data['amount_excl_tax'];
        unset($tax_data['amount_excl_tax']);

        return $tax_data;
    }

    public function get_shipping_tax_data(float $shipping_cost, bool $is_cost_vat_included = false): array
    {
        $shipping_tax_class_slug = get_option('woocommerce_shipping_tax_class', 'inherit');
        if (get_option('woocommerce_calc_taxes') !== 'yes' || !wc_shipping_enabled() || $shipping_cost <= 0) {
            return ['cost_excl_tax' => $shipping_cost, 'tax_amount' => 0.0, 'tax_rate_percent' => 0.0, 'tax_class_applied' => $shipping_tax_class_slug];
        }

        $cart_instance = WC()->cart;
        $cart_items = ($cart_instance instanceof WC_Cart) ? $cart_instance->get_cart() : [];

        $tax_data = $this->calculate_tax_data($shipping_cost, $is_cost_vat_included, $shipping_tax_class_slug, $cart_items, true);

        $tax_data['cost_excl_tax'] = $tax_data['amount_excl_tax'];
        unset($tax_data['amount_excl_tax']);
        $tax_data['tax_class_applied'] = $shipping_tax_class_slug;

        return $tax_data;
    }

    public function get_fee_tax_data_for_order($order, float $fee, bool $is_fee_vat_included = false): array
    {
        if (get_option('woocommerce_calc_taxes') !== 'yes' || !$order instanceof WC_Order) {
            return ['fee_excl_tax' => $fee, 'tax_amount' => 0.0, 'tax_rate_percent' => 0.0, 'tax_class_applied' => ''];
        }

        $product_items = $order->get_items('line_item');

        $tax_data = $this->calculate_tax_data($fee, $is_fee_vat_included, 'inherit', $product_items);

        $tax_data['fee_excl_tax'] = $tax_data['amount_excl_tax'];
        unset($tax_data['amount_excl_tax']);

        return $tax_data;
    }
}