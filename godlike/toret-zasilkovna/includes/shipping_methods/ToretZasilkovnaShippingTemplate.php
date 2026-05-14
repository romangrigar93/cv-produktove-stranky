<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function tzas_init_single_shipping_methods()
{
    if (!class_exists('Toret_Zasilkovna_Base_Shipping_Method')) {
        class Toret_Zasilkovna_Base_Shipping_Method extends WC_Shipping_Method
        {

            private $method_type;
            private $method_country;
            private string $unique_id;

            public function __construct($instance_id = 0, $method_id = '', $method_title = '', $title = '', $type = '', $country = '')
            {
                $this->id = 'zasilkovna>' . $method_id;
                $this->unique_id = $method_id;
                $this->instance_id = $instance_id;
                $this->method_title = $method_title;
                $this->method_description = __("Custom shipping method for: ", WOOZASILKOVNASLUG) . $method_title;
                $this->enabled = 'yes';
                $this->title = $title;
                $this->method_type = $type;
                $this->method_country = $country;
                $this->supports = array('shipping-zones');

                $this->init();
            }

            public function init()
            {
                $this->init_form_fields();
                $this->init_settings();
                $this->title = $this->get_option('title');
                $this->enabled = $this->get_option('enabled');

                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            public function is_available($package)
            {

                $zasilkovna_lic = get_option('woo-zasilkovna-licence');
                if ($zasilkovna_lic != 'active') {
                    return false;
                }

                $ToretZasilkovna = ToretZasilkovnaLib();

                $country = strtolower(toret_get_customer_country() ?? '');

                $country = apply_filters('zasilkovna_packeta_is_available_country', $country, $this->unique_id);

                if ($this->method_type == 'native') {
                    $aviable = $ToretZasilkovna->Helper->IsPacketaAviable($country);
                    if ($aviable === false) {
                        return false;
                    }
                } else if ($this->method_country != strtoupper($country)) {
                    return false;
                }

                $disable = $ToretZasilkovna->Helper->DisableShipping($this->unique_id, $country);
                $disable = apply_filters('zasilkovna_packeta_disabled', $disable, $country, $this->unique_id);

                if ($disable) {
                    return false;
                }

                return parent::is_available($package);
            }

            public function calculate_shipping($package = array())
            {
                $ToretZasilkovna = ToretZasilkovnaLib();

                if ($this->method_type == 'native') {
                    $method = 'z-points';
                } else {
                    $method = $this->unique_id;
                }

                $country = strtolower(toret_get_customer_country() ?? '');
                $weight = ToretZasilkovnaDimensionHelper::get_weight(WC()->cart->get_cart_contents_weight());
                $weight = apply_filters('zasilkovna_packeta_weight', $weight, $method);
                $max_dim = (new ToretZasilkovnaDimensionHelper)->get_max_dimension(WC()->cart->get_cart(), false, true);
                $subtotal = $ToretZasilkovna->Helper->get_correct_cart_subtotal();


                $zasilkovna_option = get_option('zasilkovna_option', array());
                $zasilkovna_prices = get_option('zasilkovna_prices', array());

                if ($this->method_type == 'native') {

                    $cost = $ToretZasilkovna->Helper->GetPacketaShippingPrice($method, $zasilkovna_option, $zasilkovna_prices, $country, $weight, $max_dim, $subtotal);
                    $cost = apply_filters('zasilkovna_shipping_cost', $cost, $country, $weight, $method);

                    if ($cost != -1) {

                        if ($ToretZasilkovna->Helper->CheckIfForFree($method, $country)) {
                            $cost = 0;
                        }

                        $cost = $ToretZasilkovna->Helper->PacketaFreeShipping($method, $zasilkovna_prices, $cost, $country);
                        $cost = apply_filters('zasilkovna_shipping_cost_free', $cost, $country, $weight, $method);
                        $label = $ToretZasilkovna->Helper->PacketaLabel($cost, $country, $method);

                        $rates = array(
                            'id' => $this->id,
                            'label' => $label,
                            'cost' => apply_filters('zasilkovna_shipping_cost_final', $cost, $method, $country, $weight),
                            'calc_tax' => 'per_order'
                        );
                        $rates['meta_data']['has_branches'] = 1;
                        $this->add_rate($rates);
                    }

                } else {
                    $data = $ToretZasilkovna->Helper->GetServiceBySlug($method);

                    $cost = $ToretZasilkovna->Helper->GetServiceCost($zasilkovna_option, $zasilkovna_prices, $country, $weight, $max_dim, $subtotal, $data);
                    $cost = apply_filters('zasilkovna_shipping_cost', $cost, $country, $weight, $method);

                    if ($cost != -1) {

                        if ($ToretZasilkovna->Helper->CheckIfForFree($data['slug'], $country)) {
                            $cost = 0;
                        }

                        if (empty($cost)) {
                            $cost = 0;
                        }

                        $cost = $ToretZasilkovna->Helper->ServiceFreeShipping($zasilkovna_option, $zasilkovna_prices, $cost, $data);
                        $cost = apply_filters('zasilkovna_shipping_cost_free', $cost, $country, $weight, $method);
                        $zasilkovna_services = get_option('zasilkovna_services');
                        $label = $ToretZasilkovna->Helper->ServiceLabel($zasilkovna_services, $cost, $data['key']);


                        $rates = array(
                            'id' => $this->id,
                            'label' => $label,
                            'cost' => apply_filters('zasilkovna_shipping_cost_final', $cost, $method, $country, $weight),
                            'calc_tax' => 'per_order'
                        );
                        $rates['meta_data']['has_branches'] = (isset($data['pobocky']) && $data['pobocky'] == 1) ? 1 : 0;


                        if($this->id == 'zasilkovna>cz-zasilkovna-domu-hd'){
                            $zasilkovna_option = get_option('zasilkovna_option');
                            if ($zasilkovna_option['enableHDChecker'] ?? '' == 'ok') {
                                $rates['meta_data']['has_branches'] = 1;
                            }else{
                                $rates['meta_data']['has_branches'] = 0;
                            }
                        }

                        $this->add_rate($rates);
                    }
                }

            }

            public function init_form_fields()
            {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable/Disable', WOOZASILKOVNASLUG),
                        'type' => 'checkbox',
                        'label' => __('Enable this shipping method', WOOZASILKOVNASLUG),
                        'default' => 'yes',
                    ),
                    'title' => array(
                        'title' => __('Title', WOOZASILKOVNASLUG),
                        'type' => 'text',
                        'description' => __('This controls the title which the user sees during checkout.', WOOZASILKOVNASLUG),
                        'default' => $this->method_title,
                        'desc_tip' => true,
                    ),
                    'cost' => array(
                        'title' => __('Cost', WOOZASILKOVNASLUG),
                        'type' => 'number',
                        'description' => __('Cost of this shipping method.', WOOZASILKOVNASLUG),
                        'default' => '0',
                        'desc_tip' => true,
                    ),
                    'tax_status' => array(
                        'title'   => __( 'Tax status', 'woocommerce' ),
                        'type'    => 'select',
                        'class'   => 'wc-enhanced-select',
                        'default' => 'taxable',
                        'options' => array(
                            'taxable' => __( 'Taxable', 'woocommerce' ),
                            'none'    => _x( 'None', 'Tax status', 'woocommerce' ),
                        ),
                    )
                );
            }
        }
    }

    $ToretZasilkovna = ToretZasilkovnaLib();
    $shipping_methods = $ToretZasilkovna->Helper->get_all_shipping_methods(true);

    foreach ($shipping_methods as $method_id => $method) {
        $method_title = $method['original_title'];
        $title = $method['title'];
        $type = $method['type'];
        $country = $method['country'];
        $method_title = htmlspecialchars($method_title, ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $type = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
        $country = htmlspecialchars($country, ENT_QUOTES, 'UTF-8');

        $class_name = str_replace('-', '_', $method_id);
        if (!class_exists($class_name)) {
            eval("
            class $class_name extends Toret_Zasilkovna_Base_Shipping_Method {
                public function __construct( \$instance_id = 0 ) {
                    parent::__construct( \$instance_id, '$method_id', '$method_title','$title','$type','$country' );
                }
            }
            ");

            add_filter('woocommerce_shipping_methods', function ($methods) use ($method_id, $class_name) {
                $methods['zasilkovna>' . $method_id] = $class_name;
                return $methods;
            });
        }
    }
}

add_action('woocommerce_shipping_init', 'tzas_init_single_shipping_methods');
