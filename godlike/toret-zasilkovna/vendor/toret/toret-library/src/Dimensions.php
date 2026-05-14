<?php

namespace ToretZasilkovna\Toret\Library;

use WC_Product;

class Dimensions
{

    /**
     * Get the weight unit string from WooCommerce settings.
     * @return string (e.g., 'kg', 'g', 'lbs', 'oz')
     */
    public static function get_weight_unit_setting(): string
    {
        return get_option('woocommerce_weight_unit', 'kg');
    }

    /**
     * Get the dimension unit string from WooCommerce settings.
     * @return string (e.g., 'm', 'cm', 'mm', 'in', 'yd')
     */
    public static function get_dimension_unit_setting(): string
    {
        return get_option('woocommerce_dimension_unit', 'cm');
    }

    /**
     * Get weight multiplier
     */
    public static function get_weight_multiplier()
    {
        $multiplier = 1;

        $unit = self::get_weight_unit_setting();

        if ($unit == 'g') {
            $multiplier = 1000;
        } elseif ($unit == 'lbs') {
            $multiplier = 2.205;
        } elseif ($unit == 'oz') {
            $multiplier = 35.2739619;
        }

        return $multiplier;
    }

    /**
     * Calculate total cart weight
     */
    public static function get_cart_total_weight()
    {
        $multiplier = self::get_weight_multiplier();

        $weight = WC()->cart->cart_contents_weight;

        return $weight / $multiplier;
    }

    /**
     * Calculate total order volume
     */
    public static function get_order_total_volume($order, bool $raw = false)
    {
        return self::get_total_volume($order->get_items(), $raw);
    }

    /**
     * Get cart total volume
     */
    public static function get_total_volume($items, bool $raw = false)
    {
        $cart_prods_m3 = array();

        $multiplier = self::get_dim_multiplier(true);

        foreach ($items as $values) {
            if(!isset($values['data'])){
                $_product = $values->get_product();
            }else {
                $_product = wc_get_product($values['data']->get_id());
            }
            if(empty($_product)){
                continue;
            }
            $prod_m3 = self::get_product_volume($_product);
            $cart_prods_m3[] = $prod_m3;
        }

        if ($raw) {
            return array_sum($cart_prods_m3);
        } else {
            return array_sum($cart_prods_m3) / $multiplier;
        }
    }

    /**
     * Get order weight
     */
    static function get_order_total_weight($order, bool $raw = false)
    {
        $multiplier = self::get_weight_multiplier();

        $total_weight = 0;
        $product_weight = '';

        foreach ($order->get_items() as $product_item) {
            $quantity = $product_item->get_quantity();
            $product = $product_item->get_product();

            if (!empty($product)) {
                if ($product->is_type('variable')) {
                    foreach ($product->get_visible_children() as $variation_id) {
                        $variation = wc_get_product($variation_id);
                        $weight = $variation->get_weight();

                        if ($weight) {
                            $total_weight += floatval($weight * $quantity);
                        }

                    }
                } else {
                    $product_weight = $product->get_weight();
                }
            } else {
                $product_weight = '';
            }

            if ($product_weight != '') {
                $total_weight += floatval($product_weight * $quantity);
            }
        }

        if ($raw) {
            return $total_weight;
        } else {
            return $total_weight / $multiplier;
        }
    }

    /**
     * Get the sum of dimensions (L+W+H) for a single product, converted to a target unit (e.g., 'cm').
     *
     * @param $product
     * @param string $target_unit ('m', 'cm', 'mm', 'in', 'yd') - dimensions will be converted to this unit before summing.
     * @return float Sum of dimensions in the target unit.
     */
    public static function get_product_dimensions_sum($product, string $target_unit = 'cm'): float
    {
        if (empty($product)){
            return 0.0;
        }

        $l = (float) $product->get_length();
        $w = (float) $product->get_width();
        $h = (float) $product->get_height();

        $source_unit = self::get_dimension_unit_setting();

        $convert = function($value, $from, $to) {
            if ($from === $to) return $value;
            $to_cm_multipliers = ['m' => 100, 'cm' => 1, 'mm' => 0.1, 'in' => 2.54, 'yd' => 91.44];
            $from_cm_multipliers = ['m' => 0.01, 'cm' => 1, 'mm' => 10, 'in' => 1/2.54, 'yd' => 1/91.44];

            if (!isset($to_cm_multipliers[$from]) || !isset($from_cm_multipliers[$to])) {
                return $value;
            }
            $value_in_cm = $value * $to_cm_multipliers[$from];
            return $value_in_cm * $from_cm_multipliers[$to];
        };

        $l_converted = $convert($l, $source_unit, $target_unit);
        $w_converted = $convert($w, $source_unit, $target_unit);
        $h_converted = $convert($h, $source_unit, $target_unit);

        return $l_converted + $w_converted + $h_converted;
    }

    /**
     * Get dimension multiplier
     */
    public static function get_dim_multiplier($volume = false)
    {
        $unit = self::get_dimension_unit_setting();

        $multiplier = 1;

        if ($volume) {
            if ($unit == 'mm') {
                $multiplier = 1e9;
            } elseif ($unit == 'cm') {
                $multiplier = 1e6;
            } elseif ($unit == 'in') {
                $multiplier = 61024;
            } elseif ($unit == 'yd') {
                $multiplier = 1.30795;
            }
        } else {
            if ($unit == 'mm') {
                $multiplier = 1000;
            } elseif ($unit == 'cm') {
                $multiplier = 100;
            } elseif ($unit == 'in') {
                $multiplier = 39.3701;
            } elseif ($unit == 'yd') {
                $multiplier = 1.09361;
            }
        }

        return $multiplier;
    }

    /**
     * Get product volume
     */
    public static function get_product_volume($product)
    {
        return array_product(self::get_product_dimensions($product));
    }

    /**
     * Get product dimensions
     */
    public static function get_product_dimensions(WC_Product $_product): array
    {
        $l = (float) $_product->get_length();
        $w = (float) $_product->get_width();
        $h = (float) $_product->get_height();

        return [$l, $w, $h];
    }

    /**
     * Calculate total cart volume
     */
    public static function get_cart_total_volume(bool $raw = false)
    {
        return self::get_total_volume(WC()->cart->get_cart(), $raw);
    }

    /**
     * Get order maximal dimension
     */
    public static function get_order_max_dimension($order, $raw = false)
    {
        return self::get_max_dimension($order->get_items(), $raw);
    }

    /**
     * Get max dimension from items
     */
    public static function get_max_dimension($items, $raw = false)
    {
        $cart_dimension = 0;

        $multiplier = self::get_dim_multiplier();
        foreach ($items as $values) {

            if(!isset($values['data'])){
                $_product = $values->get_product();
            }else {
                $_product = wc_get_product($values['data']->get_id());
            }
            if(empty($_product)){
                continue;
            }

            $product_dimension = self::get_product_max_dimension($_product);

            if ($product_dimension > $cart_dimension) {
                $cart_dimension = $product_dimension;
            }
        }

        if ($raw) {
            return $cart_dimension;
        } else {
            return $cart_dimension / $multiplier;
        }
    }

    /**
     * Get product max dimension
     */
    public static function get_product_max_dimension($_product)
    {
        return max(self::get_product_dimensions($_product));
    }

    /**
     * Get order maximum dimensions
     */
    public static function get_dimensions($order, bool $cart = false): array
    {
        $cart_dimension = array(0, 0, 0);

        $multiplier = self::get_dim_multiplier();

        if ($cart) {

            $items = WC()->cart->get_cart();

            foreach ($items as $values) {
                if(!isset($values['data'])){
                    $product = $values->get_product();
                }else {
                    $product = wc_get_product($values['data']->get_id());
                }
                if(empty($product)){
                    continue;
                }
                $cart_dimension = self::update_max_cart_dimensions($product, $cart_dimension);
            }

        } else {
            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                $cart_dimension = self::update_max_cart_dimensions($product, $cart_dimension);
            }
        }

        $cart_dimension[0] = $cart_dimension[0] / $multiplier;
        $cart_dimension[1] = $cart_dimension[1] / $multiplier;
        $cart_dimension[2] = $cart_dimension[2] / $multiplier;

        return $cart_dimension;
    }

    public static function update_max_cart_dimensions($product, $cart_dimension)
    {
        $product_dimension = self::get_product_dimensions($product);

        if ($product_dimension[0] > $cart_dimension[0]) {
            $cart_dimension[0] = $product_dimension[0];
        }

        if ($product_dimension[1] > $cart_dimension[1]) {
            $cart_dimension[1] = $product_dimension[1];
        }

        if ($product_dimension[2] > $cart_dimension[2]) {
            $cart_dimension[2] = $product_dimension[2];
        }

        return $cart_dimension;
    }

    /**
     * Get cart maximum dimension
     */
    public static function get_cart_max_dimension($raw = false)
    {
        return self::get_max_dimension(WC()->cart->get_cart(), $raw);
    }

    /**
     * Get max sum of three sides from items
     */
    public static function get_max_sides_sum($items, $raw = false)
    {
        $max_sides_sum = 0;

        $multiplier = self::get_dim_multiplier();

        foreach ($items as $values) {
            if(!isset($values['data'])){
                $_product = $values->get_product();
            }else {
                $_product = wc_get_product($values['data']->get_id());
            }
            if(empty($_product)){
                continue;
            }

            $product_dimensions = self::get_product_dimensions($_product);
            $current_sum = array_sum($product_dimensions);

            if ($current_sum > $max_sides_sum) {
                $max_sides_sum = $current_sum;
            }
        }

        if ($raw) {
            return $max_sides_sum;
        } else {
            return $max_sides_sum / $multiplier;
        }
    }

    /**
     * Get order maximal sum of three sides
     */
    public static function get_order_max_sides_sum($order, $raw = false)
    {
        return self::get_max_sides_sum($order->get_items(), $raw);
    }

    /**
     * Get cart maximal sum of three sides
     */
    public static function get_cart_max_sides_sum($raw = false)
    {
        return self::get_max_sides_sum(WC()->cart->get_cart(), $raw);
    }

}