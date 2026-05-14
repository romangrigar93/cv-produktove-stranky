<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

function woocommerce_zasilkovna_shipping_init()
{


    if (!class_exists('WC_Zasilkovna_Shipping_Method')) {
        class WC_Zasilkovna_Shipping_Method extends WC_Shipping_Method
        {

            private $zasilkovna_title;

            /**
             * Constructor for your shipping class
             *
             * @access public
             *
             * @param int $instance_id
             */
            public function __construct($instance_id = 0)
            {

                $licence_status = get_option('woo-zasilkovna-licence');
                if (!empty($licence_status)) {


                    $this->id = 'zasilkovna';
                    $this->instance_id = absint($instance_id);
                    $this->enabled = "yes";

                    $this->supports = array(
                        'shipping-zones',
                        'instance-settings',
                        'instance-settings-modal',
                    );

                    $this->init();
                    $this->method_title = __('Packeta', WOOZASILKOVNASLUG);

                    $this->method_description = $this->zasilkovna_title;

                }
            }

            /**
             * Init your settings
             *
             * @access public
             * @return void
             */
            function init()
            {
                // Load the settings API
                $this->init_form_fields();
                $this->init_settings();

                $this->zasilkovna_title = $this->get_option('zasilkovna_title');
                $this->title = $this->get_option('title');

                add_action('woocommerce_update_options_shipping_' . $this->id, array(
                    $this,
                    'process_admin_options'
                ));

            }


            function init_form_fields()
            {
                $this->instance_form_fields = array(
                    'enabled' => array(
                        'title' => __('Allow Packeta', WOOZASILKOVNASLUG),
                        'type' => 'checkbox',
                        'label' => __('Enable this method', WOOZASILKOVNASLUG),
                        'default' => 'no',
                    ),
                    'title' => array(
                        'title' => __('The name of the carrier', WOOZASILKOVNASLUG),
                        'type' => 'text',
                        'description' => __('The name of the carrier', WOOZASILKOVNASLUG),
                        'default' => __('Packeta', WOOZASILKOVNASLUG),
                    ),
                    'zasilkovna_title' => array(
                        'title' => __('Packeta title', WOOZASILKOVNASLUG),
                        'type' => 'text',
                        'description' => __('The text the customer sees', WOOZASILKOVNASLUG),
                        'default' => __('Packeta', WOOZASILKOVNASLUG),
                    )
                );
            }


            /**
             * calculate_shipping function.
             *
             * @param array $package
             */
            public function calculate_shipping($package = array())
            {

                $ToretZasilkovna = ToretZasilkovnaLib();

                $zasilkovna_lic = get_option('woo-zasilkovna-licence');

                if ($zasilkovna_lic == 'active') {

                    $subtotal = $ToretZasilkovna->Helper->get_correct_cart_subtotal();

                    $country = strtolower(toret_get_customer_country() ?? '');

                    $aviable = $ToretZasilkovna->Helper->IsPacketaAviable($country);

                    if ($aviable === true) {

                        foreach (TORET_ZASILKOVNA_NATIVE_TYPES as $NATIVE_TYPE) {

                            $method = TORET_ZASILKOVNA_NATIVE_SHIPPINGS[$NATIVE_TYPE] ?? 'z-points';

                            $disable = $ToretZasilkovna->Helper->DisableShipping($method, $country);
                            $disable = apply_filters('zasilkovna_packeta_disabled', $disable, $country, $method);

                            if ($disable === false) {

                                $weight = ToretZasilkovnaDimensionHelper::get_weight(WC()->cart->get_cart_contents_weight());
                                $weight = apply_filters('zasilkovna_packeta_weight', $weight,$method);
                                $max_dim = (new ToretZasilkovnaDimensionHelper)->get_max_dimension(WC()->cart->get_cart(), false, true);

                                $zasilkovna_option = get_option('zasilkovna_option', array());
                                $zasilkovna_prices = get_option('zasilkovna_prices', array());
                                $cost = $ToretZasilkovna->Helper->GetPacketaShippingPrice($method,$zasilkovna_option, $zasilkovna_prices, $country, $weight, $max_dim, $subtotal);
                                $cost = apply_filters('zasilkovna_shipping_cost', $cost, $country, $weight, $method);
                                if ($cost != -1) {

                                    if ($ToretZasilkovna->Helper->CheckIfForFree($method, $country)) {
                                        $cost = 0;
                                    }

                                    $cost = $ToretZasilkovna->Helper->PacketaFreeShipping($method, $zasilkovna_prices, $cost, $country);
                                    $cost = apply_filters('zasilkovna_shipping_cost_free', $cost, $country, $weight, $method);
                                    $label = $ToretZasilkovna->Helper->PacketaLabel($cost, $country, $method);

                                    $rates = array(
                                        'id' => $this->id . '>' . $method,
                                        'label' => $label,
                                        'cost' => apply_filters('zasilkovna_shipping_cost_final', $cost, $country, $weight, $method),
                                        'calc_tax' => 'per_order'
                                    );

                                    $this->add_rate($rates);
                                }
                            }
                        }
                    }
                    // Register the rate
                    $dataServices = $ToretZasilkovna->Helper->GetServicesByCountry($country, false);
                    foreach ($dataServices as $data) {

                        $disable = $ToretZasilkovna->Helper->DisableShipping($data['slug'], $country);

                        $disable = apply_filters('zasilkovna_packeta_disabled', $disable, $country, $data['slug']);

                        if ($disable === false) {
                            $weight = ToretZasilkovnaDimensionHelper::get_weight(WC()->cart->get_cart_contents_weight());
                            $weight = apply_filters('zasilkovna_packeta_weight', $weight);
                            $max_dim = (new ToretZasilkovnaDimensionHelper)->get_max_dimension(WC()->cart->get_cart(), false, true);

                            $zasilkovna_option = get_option('zasilkovna_option');
                            $zasilkovna_prices = get_option('zasilkovna_prices', array());
                            $cost = $ToretZasilkovna->Helper->GetServiceCost($zasilkovna_option, $zasilkovna_prices, $country, $weight, $max_dim, $subtotal, $data);
                            $cost = apply_filters($data['slug'] . '_shipping_cost', $cost, $country, $weight);

                            if ($cost != -1) {

                                if ($ToretZasilkovna->Helper->CheckIfForFree($data['slug'], $country)) {
                                    $cost = 0;
                                }

                                if (empty($cost))
                                    $cost = 0;

                                $cost = $ToretZasilkovna->Helper->ServiceFreeShipping($zasilkovna_option, $zasilkovna_prices, $cost, $data);
                                $cost = apply_filters($data['slug'] . '_shipping_cost_free', $cost, $country, $weight);
                                $zasilkovna_services = get_option('zasilkovna_services');
                                $label = $ToretZasilkovna->Helper->ServiceLabel($zasilkovna_services, $cost, $data['key']);

                                $rates = array(
                                    'id' => $this->id . '>' . $data['slug'],
                                    'label' => $label,
                                    'cost' => apply_filters('zasilkovna_shipping_cost_final', $cost, $country, $weight, $data['slug']),
                                    'calc_tax' => 'per_order'
                                );
                                $this->add_rate($rates);
                            }
                        }
                    }
                }
            }

        }//End class
    }

}

add_action('woocommerce_shipping_init', 'woocommerce_zasilkovna_shipping_init');

function add_woo_zasilkovna_shipping_method($methods)
{
    $methods['zasilkovna'] = 'WC_Zasilkovna_Shipping_Method';

    return $methods;
}

//add_filter('woocommerce_shipping_methods', 'add_woo_zasilkovna_shipping_method');