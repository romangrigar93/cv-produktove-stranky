<?php

if (!class_exists('Toret_HPOS_Compatibility')) {
    class Toret_HPOS_Compatibility
    {
        private static ?Toret_HPOS_Compatibility $instance = null;
        private static ?float $number;
        private static $hpos_enabled = false;


        public static function get_instance()
        {
            if (self::$instance == null) {
                self::$instance = new Toret_HPOS_Compatibility();
            }
            return self::$instance;
        }


        /**
         * Check if HPOS is enabled
         * @return bool|null
         */
        public static function is_wc_hpos_enabled()
        {
            if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil')) {
                self::$hpos_enabled = Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
            } else {
                self::$hpos_enabled = false;
            }
            return self::$hpos_enabled;
        }


        /**
         * Get WC_Order object from the given value
         */
        public static function get_order($order)
        {
            return (is_int($order) || is_string($order) ? wc_get_order($order) : $order);
        }


        /**
         * Get order id from the given value
         */
        public static function get_order_id($order)
        {

            if (empty($order)) return null;

            return (is_int($order) || is_string($order) ? (int)$order : $order->get_id());
        }


        /**
         * Get orders based on the arguments provided
         */
        public static function get_orders($args)
        {
            return wc_get_orders($args);
        }


        /**
         * Get order meta value.
         */
        public static function get_order_meta($order, $meta_key, $single = true, $default = '')
        {
            if (self::is_wc_hpos_enabled()) {
                $order = self::get_order($order);
                if (!$order) {
                    return $default;
                }
                $meta_value = $order->get_meta($meta_key);
                return (!$meta_value ? get_post_meta($order->get_id(), $meta_key, $single) : $meta_value);
            } else {
                $order_id = self::get_order_id($order);
                $meta_value = get_post_meta($order_id, $meta_key, $single);
                if (!$meta_value) {
                    $order = wc_get_order($order_id);
                    return $order ? $order->get_meta($meta_key) : $default;
                } else {
                    return $meta_value;
                }
            }
        }


        /**
         * Update order meta
         */
        public static function update_order_meta($order, $meta_key, $value, $save = true)
        {
            if (self::is_wc_hpos_enabled()) {
                $order = self::get_order($order);
                $order->update_meta_data($meta_key, $value);
                if ($save)
                    $order->save();
            } else {
                $order_id = self::get_order_id($order);
                update_post_meta($order_id, $meta_key, $value);
            }
        }


        /**
         * Delete order meta
         */
        public static function delete_order_meta($order, $meta_key, $save = true)
        {
            $order = self::get_order($order);
            if (self::is_wc_hpos_enabled()) {
                $order->delete_meta_data($meta_key);
                if ($save)
                    $order->save();
            } else {
                $order_id = self::get_order_id($order);
                delete_post_meta($order_id, $meta_key);
            }
        }


    }
}