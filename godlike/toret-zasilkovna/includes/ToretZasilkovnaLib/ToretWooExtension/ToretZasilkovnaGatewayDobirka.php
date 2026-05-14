<?php

use ToretZasilkovna\Toret\Library\Dimensions;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
/**
 * @package   WC_Gateway_Dobirka_Plus
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      Toret.cz
 * @copyright 2018 Toret.cz
 *
 * Version: 1.2
 *
 */


if (!function_exists('woocommerce_gateway_dobirka_plus_init')) {

    function woocommerce_gateway_dobirka_plus_init()
    {

        $settings = get_option('woocommerce_dobirka_settings');
        if (empty($settings)) {
            return;
        } else {
            $enabled = $settings['enabled'] ?? 'no';
            if ($enabled == 'no') {
                return;
            }
        }

        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }

        /**
         * Platba při doručení
         *
         * Umožňuje využít platbu při doručení - dobírku
         *
         * @author        toret.cz
         */
        if (!class_exists('WC_Gateway_Dobirka_Plus')) {
            /**
             * @property string $taxable
             */
            class WC_Gateway_Dobirka_Plus extends WC_Payment_Gateway
            {

                /**
                 * privcate variables
                 */
                private $instructions;
                private $enable_for_methods;
                private $enable_dobirka_countries;
                private $order_status;
                private string $taxable;
                private string $show_cod;

                /**
                 * Constructor for the gateway.
                 */
                public function __construct()
                {
                    $this->id = 'dobirka';
                    $this->icon = apply_filters('woocommerce_cod_icon', '');
                    $this->method_title = __('Cash on Delivery', WOOZASILKOVNASLUG);
                    $this->method_description = __('Allows customers to pay for goods on delivery (cash on delivery).', WOOZASILKOVNASLUG);
                    $this->has_fields = false;

                    // Load the settings
                    $this->init_form_fields();
                    $this->init_settings();

                    // Get settings
                    $this->title = $this->get_option('title');
                    $this->description = $this->get_option('description');
                    $this->instructions = $this->get_option('instructions');
                    $this->enable_for_methods = (array)$this->get_option('enable_for_methods', array());
                    $this->enable_dobirka_countries = (array)$this->get_option('enable_dobirka_countries', array());
                    $this->order_status = (string)$this->get_option('order_status', 'on-hold');
                    $this->taxable = $this->get_option('taxable');
                    $this->show_cod = $this->get_option('show_cod');

                    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                        $this,
                        'process_admin_options'
                    ));
                    add_action('woocommerce_thankyou_dobirka', array($this, 'thankyou'));

                    // Customer Emails
                    add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
                }


                /**
                 * Initialise Gateway Settings Form Fields
                 *
                 * @access public
                 */
                public function init_form_fields()
                {
                    if (function_exists('WC')) {
                        if (isset(WC()->countries)) {

                            $shipping_methods = array();
                            if (is_admin()) {

                                foreach (WC_Shipping_Zones::get_zones() as $raw_zone) {
                                    $zones[] = new WC_Shipping_Zone($raw_zone);
                                }

                                $zones[] = new WC_Shipping_Zone(0);

                                foreach (WC()->shipping()->load_shipping_methods() as $method) {

                                    $shipping_methods[$method->get_method_title()] = array();

                                    $shipping_methods[$method->get_method_title()][$method->id] = sprintf(__('Any &quot;%1$s&quot; method', 'woocommerce'), $method->get_method_title());

                                    foreach ($zones as $zone) {

                                        $shipping_method_instances = $zone->get_shipping_methods();

                                        foreach ($shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance) {

                                            if ($shipping_method_instance->id !== $method->id) {
                                                continue;
                                            }

                                            $option_id = $shipping_method_instance->get_rate_id();
                                            $option_instance_title = sprintf(__('%1$s (#%2$s)', 'woocommerce'), $shipping_method_instance->get_title(), $shipping_method_instance_id);
                                            $option_title = sprintf(__('%1$s &ndash; %2$s', 'woocommerce'), $zone->get_id() ? $zone->get_zone_name() : __('Other locations', 'woocommerce'), $option_instance_title);

                                            $shipping_methods[$method->get_method_title()][$option_id] = $option_title;
                                        }
                                    }
                                }

                            }
                            foreach (WC()->shipping->load_shipping_methods() as $method) {
                                $shipping_methods[$method->id] = $method->get_method_title();
                            }

                            $countries = WC()->countries->get_allowed_countries();
                            $wc_get_order_statuses = wc_get_order_statuses();
                            $shop_order_statuses = $this->alter_wc_statuses($wc_get_order_statuses);

                            $this->form_fields = array(
                                'enabled' => array(
                                    'title' => __('Allow cash on delivery', WOOZASILKOVNASLUG),
                                    'label' => __('Allow', WOOZASILKOVNASLUG),
                                    'type' => 'checkbox',
                                    'description' => '',
                                    'default' => 'no'
                                ),
                                'title' => array(
                                    'title' => __('Title', WOOZASILKOVNASLUG),
                                    'type' => 'text',
                                    'description' => __('The name of the payment method that customers see when ordering.', WOOZASILKOVNASLUG),
                                    'default' => __('Payment on delivery', WOOZASILKOVNASLUG),
                                    'desc_tip' => true,
                                ),
                                'description' => array(
                                    'title' => __('Description', WOOZASILKOVNASLUG),
                                    'type' => 'textarea',
                                    'description' => __('Description of the payment method that will appear to the customer on the page.', WOOZASILKOVNASLUG),
                                    'default' => __('Payment in cash after delivery.', WOOZASILKOVNASLUG),
                                ),
                                'instructions' => array(
                                    'title' => __('Instructions', WOOZASILKOVNASLUG),
                                    'type' => 'textarea',
                                    'description' => __('Instructions that appear on a thank you page.', WOOZASILKOVNASLUG),
                                    'default' => __('Make payment only after the delivery of the goods.', WOOZASILKOVNASLUG)
                                ),
                                'enable_for_methods' => array(
                                    'title' => __('Enable delivery method', WOOZASILKOVNASLUG),
                                    'type' => 'multiselect',
                                    'class' => 'chosen_select',
                                    'css' => 'width: 450px;',
                                    'default' => array(),
                                    'description' => __('If the cash on delivery is active, you can define the shipping methods here. To enable all modes of transport, leave the field empty.', WOOZASILKOVNASLUG),
                                    'options' => $shipping_methods,
                                    'desc_tip' => true,
                                ),
                                'enable_dobirka_countries' => array(
                                    'title' => __('Allow for countries', WOOZASILKOVNASLUG),
                                    'type' => 'multiselect',
                                    'class' => 'chosen_select',
                                    'css' => 'width: 450px;',
                                    'default' => array(),
                                    'description' => __('Choose which country the COD will be available for.', WOOZASILKOVNASLUG),
                                    'options' => $countries,
                                    'desc_tip' => true,
                                ),
                                'order_status' => array(
                                    'title' => __('Order Status', WOOZASILKOVNASLUG),
                                    'type' => 'select',
                                    'class' => 'chosen_select',
                                    'css' => 'width: 450px;',
                                    'default' => '',
                                    'description' => __('Order status after payment.', WOOZASILKOVNASLUG),
                                    'options' => $shop_order_statuses,
                                    'desc_tip' => true,
                                ),
                                'taxable' => array(
                                    'title' => __('Calculate tax?', WOOZASILKOVNASLUG),
                                    'label' => __('Calculate tax?', WOOZASILKOVNASLUG),
                                    'type' => 'checkbox',
                                    'description' => __('Calculate a tax for a C.O.D fee?', WOOZASILKOVNASLUG),
                                    'default' => 'yes'
                                ),
                                'show_cod' => array(
                                    'title' => __('Display cash on delivery if order price is 0', WOOZASILKOVNASLUG),
                                    'label' => __('Display cash on delivery if order price is 0', WOOZASILKOVNASLUG),
                                    'type' => 'checkbox',
                                    'description' => __('Display cash on delivery if order price is 0', WOOZASILKOVNASLUG),
                                    'default' => 'yes'
                                )
                            );
                        }
                    }
                }

                /**
                 * Alter order statuses
                 */
                private function alter_wc_statuses(array $array): array
                {
                    $new_array = array();
                    foreach ($array as $key => $value) {
                        $new_array[substr($key, 3)] = $value;
                    }

                    return $new_array;
                }


                /**
                 * Check If The Gateway Is Available For Use
                 */
                public function is_available(): bool
                {
                    if (is_admin()) {
                        return false;
                    } elseif ($this->need_shipping_check() === false) {
                        return false;
                    } elseif ($this->is_available_for_country() === false) {
                        return false;
                    } elseif ($this->is_virtual_product_in_cart() === true) {
                        return false;
                    } elseif ($this->check_allowed_shipping_methods() === false) {
                        return false;
                    }

                    //Return available
                    return parent::is_available();
                }


                /**
                 * Check is shipping methods are allowed
                 */
                private function check_allowed_shipping_methods(): bool
                {
                    if (!empty($this->enable_for_methods)) {

                        $chosen_shipping_methods = $this->get_chosen_shipping_methods();

                        $check_method = false;

                        if (is_page(wc_get_page_id('checkout')) && 0 < get_query_var('order-pay')) {

                            $order_id = absint(get_query_var('order-pay'));
                            $order = wc_get_order($order_id);
                            if ($order->get_shipping_method()) {
                                $check_method = $this->get_shipping_method_id($order_id);
                            }

                        } elseif (empty($chosen_shipping_methods) || sizeof($chosen_shipping_methods) > 1) {

                            $check_method = false;

                        } elseif (sizeof($chosen_shipping_methods) == 1) {

                            $check_method = $chosen_shipping_methods[0];

                        }
                        //return false if not exist selected shippping method
                        if (!$check_method) {
                            return false;
                        }

                        $found = false;
                        //find method in enabled methods
                        foreach ($this->enable_for_methods as $method_id) {
                            if (strpos($check_method, $method_id) === 0) {
                                $found = true;
                                break;
                            }
                        }
                        //return false if method isnt in enable methods
                        if (!$found) {
                            return false;
                        }

                        $shipping_method = tzas_get_shipping_from_cart();
                        if ($shipping_method != 'free_shipping') {

                            $doprava_name = $shipping_method;
                            $doprava_name_parts = explode(':', $doprava_name);
                            $service = tzas_get_service_from_string($doprava_name);

                            if ($service) {
                                $d_name = $service;
                            } else {
                                $d_name = $doprava_name;
                            }
                            if (!empty($service) && trim($service) != '') {

                                $instance_id = WC()->session->get('instance_toret');

                                if ($doprava_name == 'doprava' && !empty($doprava_name_parts[1])) {

                                    $instance_id = $doprava_name_parts[1];
                                    WC()->session->set('instance_toret', $instance_id);

                                }

                                $shipping_methods = WC()->shipping->shipping_methods;

                                if (!empty($shipping_methods)) {
                                    foreach ($shipping_methods as $keys => $item) {
                                        if (is_int($keys)) {
                                            if ($item->id == 'doprava') {
                                                $instance_id = $keys;
                                            }
                                            WC()->session->set('instance_toret', $instance_id);
                                        }
                                    }
                                }

                                $doprava = get_option('woocommerce_doprava_' . $instance_id . '_settings');
                                if (!empty($doprava)) {
                                    foreach ($doprava['doprava'] as $item) {
                                        if (sanitize_title($item['doprava_name']) == trim($d_name)) {
                                            if (empty($item['doprava_dobirka_active'])) {
                                                return false;
                                            }
                                            if ($item['doprava_dobirka_active'] != 'yes') {
                                                return false;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    return true;
                }

                /**
                 * Check is products needs shipping
                 */
                private function need_shipping_check(): bool
                {
                    $needs_shipping = false;
                    if (WC()->cart && WC()->cart->needs_shipping()) {
                        $needs_shipping = true;
                    } elseif (is_page(wc_get_page_id('checkout')) && 0 < get_query_var('order-pay')) {
                        $order_id = absint(get_query_var('order-pay'));
                        $order = wc_get_order($order_id);

                        if (0 < count($order->get_items())) {
                            foreach ($order->get_items() as $item) {
                                $_product = $item->get_product();
                                if ($_product && $_product->needs_shipping()) {
                                    $needs_shipping = true;
                                    break;
                                }
                            }
                        }

                    } elseif (WC()->cart && WC()->cart->needs_shipping()) {
                        $needs_shipping = true;
                    }

                    $needs_shipping = apply_filters('woocommerce_cart_needs_shipping', $needs_shipping);

                    if (!$needs_shipping) {
                        return false;
                    } else {
                        return true;
                    }
                }

                /**
                 * Check is shipping method available for selected country
                 */
                private function is_available_for_country(): bool
                {
                    if (!empty(WC()->customer)) {
                        $country = $this->get_customer_country();
                        if (!empty($this->enable_dobirka_countries)) {
                            if (!in_array($country, $this->enable_dobirka_countries)) {
                                $return = false;
                            } else {
                                $return = true;
                            }
                        } else {
                            $return = true;
                        }
                    } else {
                        $return = false;
                    }

                    return $return;
                }

                /**
                 * Get customer country
                 */
                private function get_customer_country(): string
                {
                    $shipping_country = WC()->customer->get_shipping_country();
                    if (!empty($shipping_country)) {
                        $country = WC()->customer->get_shipping_country();
                    } else {
                        $country = WC()->customer->get_billing_country();
                    }

                    return $country;
                }

                /**
                 * Check if is virtual product in cart
                 */
                private function is_virtual_product_in_cart(): bool
                {
                    $has_virtual = true;
                    $cart_data = $this->get_cart_content();
                    if (!empty($cart_data)) {
                        foreach ($cart_data as $item) {
                            $product = wc_get_product($item['variation_id'] ?: $item['product_id']);
                            if ($product) {
                                if (!$product->is_virtual()) {
                                    $has_virtual = false;
                                }
                            }

                        }
                    }

                    return apply_filters('dobirka_is_virtual_product_in_cart', $has_virtual, $this);
                }

                /**
                 * Získáme obsah košíku
                 */
                private function get_cart_content()
                {
                    $cart_items = array();

                    if (!empty(WC()->session->cart->cart_contents)) {
                        $cart_items = WC()->session->cart->cart_contents;
                    } else {
                        $cart_data = WC()->cart;
                        if (property_exists($cart_data, 'cart_contents') && !empty($cart_data->cart_contents)) {
                            $cart_items = $cart_data->cart_contents;
                        }
                    }

                    return $cart_items;
                }

                /**
                 * Process the payment and return the result
                 */
                public function process_payment($order_id): array
                {
                    $order = wc_get_order($order_id);

                    $order_status = apply_filters('dobirka_order_status', $this->order_status ?? '', $order_id);
                    // Mark as on-hold (we're awaiting the cheque)
                    if ($order_status != '') {
                        $order->update_status($order_status, __('Payment on delivery.', WOOZASILKOVNASLUG));
                    } else {
                        $order->update_status('on-hold', __('Payment on delivery.', WOOZASILKOVNASLUG));
                    }

                    // Reduce stock levels
                    wc_reduce_stock_levels($order_id);

                    // Remove cart
                    WC()->cart->empty_cart();

                    // Add order note
                    $order->add_order_note(__('The customer chose the cash on delivery', WOOZASILKOVNASLUG));

                    // Return thankyou redirect
                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order)
                    );
                }


                /**
                 * Output for the order received page.
                 */
                public function thankyou(): void
                {
                    echo ($this->instructions ?? '') != '' ? wpautop($this->instructions ?? '') : '';
                }

                /**
                 * Add instructions to the WC emails
                 */
                public function email_instructions(object $order, bool $sent_to_admin): void
                {
                    if (!$sent_to_admin && 'dobirka' === $order->get_payment_method()) {
                        if ($this->instructions ?? '') {
                            echo wpautop(wptexturize($this->instructions ?? '')) . PHP_EOL;
                        }
                    }
                }

                /**
                 * Get current gateway
                 */
                public function get_current_gateway()
                {
                    if (empty(WC()->payment_gateways->get_available_payment_gateways())) {
                        return false;
                    }

                    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                    $current_gateway = null;

                    if (!empty($available_gateways)) {
                        //Get Chosen Method
                        $current_gateway = $this->get_selected_shipping_method($available_gateways);
                    }
                    if (!is_null($current_gateway)) {
                        return $current_gateway;
                    } else {
                        return false;
                    }
                }

                /**
                 * Get selected shipping method
                 */
                private function get_selected_shipping_method(array $available_gateways): array
                {

                    $default_gateway = get_option('woocommerce_default_gateway');

                    if (isset(WC()->session->chosen_payment_method) && isset($available_gateways[WC()->session->chosen_payment_method])) {
                        $current_gateway = $available_gateways[WC()->session->chosen_payment_method];
                    } elseif (isset($available_gateways[$default_gateway])) {
                        $current_gateway = $available_gateways[$default_gateway];
                    } else {
                        $current_gateway = current($available_gateways);
                    }

                    return $current_gateway;
                }

                /**
                 * Get if cart has fee
                 */
                public function cart_has_fee(object $cart, string $item_title, float $amount): bool
                {
                    $fees = $cart->get_fees();
                    $item_id = sanitize_title($item_title);
                    $amount = (float)esc_attr($amount);
                    foreach ($fees as $fee) {
                        if ($fee->amount == $amount && $fee->id == $item_id) {
                            return true;
                        }
                    }

                    return false;
                }

                /**
                 * Get if cart has fee
                 */
                private function get_chosen_shipping_methods(): array
                {
                    //Woo 3.0 fix
                    // Only apply if all packages are being shipped via local pickup
                    $chosen_shipping_methods_session = WC()->session->get('chosen_shipping_methods');
                    /*elseif ( class_exists( 'WC_Session' ) ){
                        $chosen_shipping_methods_session = WC_Session::get( 'chosen_shipping_methods' );
                    }*/
                    if (isset($chosen_shipping_methods_session)) {
                        $chosen_shipping_methods = array_unique($chosen_shipping_methods_session);
                    } else {
                        $chosen_shipping_methods = array();
                    }

                    return $chosen_shipping_methods;
                }

                /**
                 * Get shipping method id
                 */
                private function get_shipping_method_id(int $order_id): string
                {
                    $order = wc_get_order($order_id);
                    $shipping = '';
                    foreach ($order->get_shipping_methods() as $shipping_method) {
                        $shipping = $shipping_method->get_method_id();
                    }
                    $array = explode('>', $shipping);
                    if (!empty($array[0])) {
                        return $array[0];
                    } else {
                        return $shipping;
                    }

                }
            }
        }
    }

    add_action('plugins_loaded', 'woocommerce_gateway_dobirka_plus_init');


    function woocommerce_add_gateway_dobirka_plus(array $methods): array
    {
        $methods[] = 'WC_Gateway_Dobirka_Plus';

        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_dobirka_plus');


    add_filter('woocommerce_available_payment_gateways', 'toret_gateway_disable_shipping');


    function is_over_max($service, $country)
    {

        global $woocommerce;
        $amount = $woocommerce->cart->subtotal;
        $zasilkovna_prices = get_option('zasilkovna_prices', array());

        if (tzas_is_native_method($service)) {
            $native_type = tzas_get_native_slug_from_service($service);
            $dob_max = (!empty($zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dobirka-max']) ? $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dobirka-max'] : 99999999999);
        } else {
            $dob_max = (!empty($zasilkovna_prices[$service . '-dobirka-max']) ? $zasilkovna_prices[$service . '-dobirka-max'] : 99999999999);
        }

        $dob_max = ToretZasilkovnaHelper::currency_compatibility($dob_max);
        return $amount >= $dob_max;
    }


    /**
     * disable cod by total price
     */
    function toret_gateway_disable_shipping($available_gateways): array
    {
        if (is_admin()) return $available_gateways;

        if (WC()->session) {

            if (is_wc_endpoint_url('order-pay')) {

                global $wp;

                if (isset($wp->query_vars['order-pay']) && absint($wp->query_vars['order-pay']) > 0) {

                    $order_id = absint($wp->query_vars['order-pay']);

                    $order = wc_get_order($order_id);

                    $zasilkovna_shipping = tzas_get_shipping_method_id($order);
                    $service = tzas_get_service_from_string($zasilkovna_shipping);

                    $saved_meta = Toret_HPOS_Compatibility::get_order_meta($order_id,'zasilkovna_creditCardPayment', true);

                    if(empty($saved_meta)) {
                        foreach (TORET_ZASILKOVNA_COD_METHODS as $cod_method) {
                            unset($available_gateways[$cod_method]);
                        }
                    }

                    /*if (tzas_is_native_method($service)) {
                        foreach (TORET_ZASILKOVNA_COD_METHODS as $cod_method) {
                            unset($available_gateways[$cod_method]);
                        }
                    }*/
                }

                return $available_gateways;
            }


            $chosen_shipping = tzas_get_shipping_from_cart();
            $service = tzas_get_service_from_cart();

            if ($chosen_shipping) {

                if (is_wc_endpoint_url('order-pay') && tzas_is_native_method($service)) {
                    foreach (TORET_ZASILKOVNA_COD_METHODS as $cod_method) {
                        unset($available_gateways[$cod_method]);
                    }
                    return $available_gateways;
                }

                if (!empty($chosen_shipping)) {

                    //if (count($shipping) > 1) {

                    if (!tzas_is_zasilkovna_shipping($chosen_shipping))
                        return $available_gateways;

                    $ToretZasilkovna = ToretZasilkovnaLib();
                    $country = $ToretZasilkovna->Helper->get_customer_country();

                    if (is_over_max($service, $country)) {
                        foreach (TORET_ZASILKOVNA_COD_METHODS as $cod_method) {
                            unset($available_gateways[$cod_method]);
                        }
                    }

                    global $woocommerce;
                    $amount = $woocommerce->cart->total;

                    $multipackage_disable = false;

                    $ToretZasilkovna = ToretZasilkovnaLib();

                    $zasilkovna_option = get_option('zasilkovna_option', array());


                    $weight = (new ToretZasilkovnaDimensionHelper())->get_cart_total_weight();
                    $max_dim = Dimensions::get_cart_max_dimension(false,true);
                    $max_dim_sum = Dimensions::get_cart_max_sides_sum(false,true);
                    $multipackage_data = $ToretZasilkovna->Helper->get_multipackage_data($zasilkovna_option, $weight,$max_dim, $max_dim_sum);
                    if($multipackage_data['enabled']){
                        if ($multipackage_data['codDisabled']) {
                            $multipackage_disable = true;
                        }

                        if ($ToretZasilkovna->Helper->is_empty_weight_product_in_cart()) {
                            $multipackage_disable = true;
                        }
                    }

                    $zasilkovna_creditCardPayment = WC()->session->get('zasilkovna_creditCardPayment');
                    $cod_check = $zasilkovna_option['cod_point_check'] ?? '';
                    if ($cod_check == 'ok') {
                        if ($zasilkovna_creditCardPayment == 'false' && tzas_is_native_method($service)) {
                            foreach (TORET_ZASILKOVNA_COD_METHODS as $cod_method) {
                                unset($available_gateways[$cod_method]);
                            }
                        }
                    }

                    $limit = (new ToretZasilkovnaLimits)->get_country_cod_insurance_limit($country, get_woocommerce_currency(), 'cod');
                    if ($amount > $limit || $multipackage_disable) {
                        foreach (TORET_ZASILKOVNA_COD_METHODS as $cod_method) {
                            unset($available_gateways[$cod_method]);
                        }
                        return $available_gateways;
                    }

                    $is_empty = (new ToretZasilkovnaHelper)->tzas_if_fee_value_empty();
                    $is_empty = apply_filters('toret_zasilkovna_is_cod_fee_empty', $is_empty, $service);
                    if ($is_empty) {
                        foreach (TORET_ZASILKOVNA_COD_METHODS as $cod_method) {
                            unset($available_gateways[$cod_method]);
                        }
                    }

                    if (isset($available_gateways['dobirka']) || isset($available_gateways['cod'])) {
                        $komplet_data = $ToretZasilkovna->Helper->get_komplet_data();
                        foreach ($komplet_data as $data) {
                            if ($data['dobirka'] == 1) {
                                if (0 === strpos($chosen_shipping, $data['prac'])) {
                                    foreach (TORET_ZASILKOVNA_COD_METHODS as $cod_method) {
                                        unset($available_gateways[$cod_method]);
                                    }
                                }
                                return $available_gateways;
                            }
                        }
                    }
                }
            }
        }


        return $available_gateways;
    }

}

