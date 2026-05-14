<?php

use ToretZasilkovna\Toret\Library\Dimensions;
use ToretZasilkovna\Toret\Library\ExchangeRates;
use ToretZasilkovna\Toret\Library\Taxes;

if (!defined('ABSPATH')) {
    exit;
}

class ToretZasilkovnaHelper
{

    private static $komplet_data = null;

    public static function get_komplet_data()
    {
        if (self::$komplet_data === null) {
            self::$komplet_data = self::komplet_data();
        }
        return self::$komplet_data;
    }

    public static function reset_komplet_data()
    {
        self::$komplet_data = null;
    }


    /**
     * Get weight by weight unit
     */
    public static function DisableShipping(string $service, string $country): bool
    {
        $disable = false;
        if (self::DisableByWeight($service, $country) === true) {
            //error_log('DisableByWeight: ' . $service);
            $disable = true;
        }
        if (self::DisableProduct($service, $country) === true) {
            //error_log('DisableProduct: ' . $service);
            $disable = true;
        }
        if (self::DisableBySettings($service, $country) === true) {
            //error_log('DisableBySettings: ' . $service);
            $disable = true;
        }
        if (self::DisableByDim($service, $country, true) === true) {
            //error_log('DisableByDim: ' . $service);
            $disable = true;
        }
        if (self::DisableByTotalPrice($service, $country, true) === true) {
            // error_log('DisableByTotalPrice: ' . $service);
            $disable = true;
        }

        return $disable;
    }

    /**
     * Disable by weight
     */
    public static function DisableByWeight(string $service, string $country): bool
    {
        $zasilkovna_option = get_option('zasilkovna_option');
        $Weight = ToretZasilkovnaDimensionHelper::get_weight(WC()->cart->get_cart_contents_weight());
        $Weight = apply_filters('zasilkovna_packeta_weight', $Weight);

        $disable = false;

        if (tzas_is_native_method($service)) {
            $native_type = tzas_get_native_slug_from_service($service);
            $data = self::GetZPointsData(strtolower($country), $native_type);

            if (isset($data['hmotnost']) && $data['hmotnost'] !== '') {
                $MaxWeight = $data['hmotnost'];
            } elseif (isset($zasilkovna_option['max_weight']) && $zasilkovna_option['max_weight'] !== '') {
                $MaxWeight = 30;
            } else {
                $MaxWeight = 15;
            }
        } else {
            $data = self::GetServiceBySlug($service);
            if (isset($data['hmotnost']) && $data['hmotnost'] !== '') {
                $MaxWeight = $data['hmotnost'];
            } elseif (isset($data['vaha'])) {
                $MaxWeight = $data['vaha'];
            } else {
                $MaxWeight = 15;
            }
        }

        if ($Weight > $MaxWeight) {
            $disable = true;
        }

        return $disable;
    }

    function tzas_if_fee_value_empty(): bool
    {
        $fee = -1;
        $zasilkovna_option = get_option('zasilkovna_option');
        $zasilkovna_prices = get_option('zasilkovna_prices', array());
        $country = toret_get_customer_country();
        $service = tzas_get_service_from_cart();

        if (empty($service)) {
            return -1;
        }

        if (tzas_is_native_method($service)) {
            $native_type = tzas_get_native_slug_from_service($service);
            $fee_price_type = tzas_get_cod_fee_type($zasilkovna_option, $zasilkovna_prices, 'zasilkovna' . $native_type . '-' . strtolower($country));
        } else {
            $fee_price_type = tzas_get_cod_fee_type($zasilkovna_option, $zasilkovna_prices, $service);
        }

        if ($fee_price_type == 'total') {

            $amount = WC()->cart->get_subtotal();

            if (tzas_is_native_method($service)) {
                $native_type = tzas_get_native_slug_from_service($service);
                $dob_max = (!empty($zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dobirka-max']) ? $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dobirka-max'] : 99999999999);
                $fee = (!empty($zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dobirka']) ? $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dobirka'] : -1);
                if (isset($zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dobirka']) && $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dobirka'] == 0)
                    $fee = 0;
                if ($amount <= $dob_max) {
                    if (!empty($zasilkovna_prices['zasilkovna' . $native_type . '-feeo-' . strtolower($country)])) {
                        foreach ($zasilkovna_prices['zasilkovna' . $native_type . '-feeo-' . strtolower($country)] as $key => $hmo) {
                            if (($hmo != '') || ($zasilkovna_prices['zasilkovna' . $native_type . '-feed-' . strtolower($country)][$key] != '') || ($zasilkovna_prices['zasilkovna' . $native_type . '-cenafee-' . strtolower($country)][$key] != '')) {
                                if ($amount >= $hmo && $amount <= $zasilkovna_prices['zasilkovna' . $native_type . '-feed-' . strtolower($country)][$key]) {
                                    $fee = $zasilkovna_prices['zasilkovna' . $native_type . '-cenafee-' . strtolower($country)][$key];
                                }
                            }
                        }
                    }
                } else {
                    $fee = -1;
                }
            } else {
                $dob_max = (!empty($zasilkovna_prices[$service . '-dobirka-max']) ? $zasilkovna_prices[$service . '-dobirka-max'] : 99999999999);
                $fee = (!empty($zasilkovna_prices[$service . '-dobirka']) ? $zasilkovna_prices[$service . '-dobirka'] : -1);
                if (isset($zasilkovna_prices[$service . '-dobirka']) && $zasilkovna_prices[$service . '-dobirka'] == 0)
                    $fee = 0;

                if ($amount <= $dob_max) {
                    if (!empty($zasilkovna_prices[$service . '-feeo-' . strtolower($country)])) {
                        foreach ($zasilkovna_prices[$service . '-feeo-' . strtolower($country)] as $key => $hmo) {
                            if (($hmo != '') || ($zasilkovna_prices[$service . '-feed-' . strtolower($country)][$key] != '') || ($zasilkovna_prices[$service . '-cenafee-' . strtolower($country)][$key] != '')) {
                                if ($amount >= $hmo && $amount <= $zasilkovna_prices[$service . '-feed-' . strtolower($country)][$key]) {
                                    $fee = $zasilkovna_prices[$service . '-cenafee-' . strtolower($country)][$key];
                                }
                            }
                        }
                    }
                } else {
                    $fee = -1;
                }
            }

            if ($fee == '')
                $fee = -1;

        } else {
            if (tzas_is_native_method($service)) {
                $ToretZasilkovna = ToretZasilkovnaLib();
                $native_type = tzas_get_native_slug_from_service($service);
                foreach ($ToretZasilkovna->Helper->komplet_staty_kont() as $stat => $stateNazev) {
                    if (in_array(strtoupper($stat), $zasilkovna_option['povolene_staty'])) {
                        $aviable = $ToretZasilkovna->Helper->IsPacketaAviableAdmin($stat);
                        if ($aviable) {
                            if (toret_get_customer_country() == $stat) {
                                $fee = $this->tzas_is_empty_price('zasilkovna' . $native_type . '-' . strtolower($stat) . '-dobirka');
                            }
                        }
                    }
                }
            } else {
                $fee = $this->tzas_is_empty_price($service . '-dobirka');
            }

        }

        return $fee == -1;
    }

    function tzas_is_empty_price(string $option_name)
    {
        $zasilkovna_prices = get_option('zasilkovna_prices', array());

        if (!empty($zasilkovna_prices) && isset($zasilkovna_prices[$option_name])) {
            $fee = $zasilkovna_prices[$option_name];
        } else {
            $fee = -1;
        }

        return $fee;
    }

    /**
     * check disable product
     */
    static function CheckIfForFree(string $service, string $country, $cart_items = null): bool
    {
        $disable = false;
        $order = true;
        if (empty($cart_items)) {
            $cart_items = WC()->cart->get_cart();
            $order = false;
        }

        foreach ($cart_items as $item_ov) {
            if ($order)
                $product_id = $item_ov->get_product_id();
            else
                $product_id = $item_ov['data']->get_id();

            $variationID = '';

            $product_kont = wc_get_product($product_id);
            if (!$product_kont) {
                continue;
            }
            if (!$product_kont->is_type('simple')) {
                $variationID = $product_id;
                $product_id = $product_kont->get_parent_id();

                if (tzas_is_native_method($service)) {
                    $native_type = tzas_get_native_slug_from_service($service);
                    $free_product = get_post_meta($product_id, '_zasilkovna' . $native_type . '_' . strtolower($country) . '_is_for_free', true);
                } else {
                    $data = self::GetServiceBySlug($service);
                    $free_product = get_post_meta($product_id, '_' . $data['key'] . '_is_for_free', true);
                }

                if (!empty($free_product) && ($free_product == 'yes')) {
                    return true;
                }
            }

            $terms = get_the_terms($product_id, 'product_cat');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $product_cat_id = $term->term_id;
                    if (tzas_is_native_method($service)) {
                        $native_type = tzas_get_native_slug_from_service($service);
                        $free_category = get_term_meta($product_cat_id, '_zasilkovna' . $native_type . '_' . strtolower($country) . '_is_for_free', true);
                    } else {
                        $data = self::GetServiceBySlug($service);
                        $free_category = get_term_meta($product_cat_id, '_' . $data['key'] . '_is_for_free', true);
                    }
                }
            }


            $checked_id = $variationID != '' ? $variationID : $product_id;

            if (tzas_is_native_method($service)) {
                $native_type = tzas_get_native_slug_from_service($service);
                $free_product = get_post_meta($checked_id, '_zasilkovna' . $native_type . '_' . strtolower($country) . '_is_for_free', true);
            } else {
                $data = self::GetServiceBySlug($service);
                $free_product = get_post_meta($checked_id, '_' . $data['key'] . '_is_for_free', true);
            }


            if (!empty($free_product) && ($free_product == 'yes')) {
                $disable = true;
            }

            if (!empty($free_variable) && ($free_variable == 'yes')) {
                $disable = true;
            }

            if (!empty($free_category) && ($free_category == 'yes')) {
                $disable = true;
            }
        }

        return $disable;
    }

    private static function DisableProduct(string $service, string $country): bool
    {
        $items_in_cart = WC()->cart->get_cart();

        foreach ($items_in_cart as $cart_item) {
            $product_data = $cart_item['data'];
            $product_id = $product_data->get_id();
            $parent_id = $product_data->get_parent_id();

            $main_product_id = $parent_id ? $parent_id : $product_id;

            $checked_id = $product_id;

            $is_disabled_on_product = self::isShippingDisabledForPost($checked_id, $service, $country);
            if ($is_disabled_on_product) {
                return true;
            }

            if ($parent_id) {
                $is_disabled_on_parent = self::isShippingDisabledForPost($parent_id, $service, $country);
                if ($is_disabled_on_parent) {
                    return true;
                }
            }

            $terms = get_the_terms($main_product_id, 'product_cat');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $product_cat_id = $term->term_id;

                    $data_key = self::getServiceDataKey($service, $country);
                    if (!$data_key) continue;

                    $is_disabled_on_category = get_term_meta($product_cat_id, '_' . $data_key . '_vypnuti', true);

                    if (!empty($is_disabled_on_category) && $is_disabled_on_category == 'yes') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Pomocná funkce pro zjištění, zda je doprava vypnutá pro dané ID (produkt/varianta).
     */
    private static function isShippingDisabledForPost(int $post_id, string $service, string $country): bool
    {
        $data_key = self::getServiceDataKey($service, $country);
        if (!$data_key) {
            return false;
        }

        $disabled_meta = get_post_meta($post_id, '_' . $data_key . '_vypnuti', true);
        return (!empty($disabled_meta) && $disabled_meta == 'yes');
    }

    /**
     * Pomocná funkce pro získání klíče pro meta data.
     */
    private static function getServiceDataKey(string $service, string $country): ?string
    {
        if (tzas_is_native_method($service)) {
            $native_type = tzas_get_native_slug_from_service($service);
            return 'zasilkovna' . $native_type . '_' . strtolower($country);
        } else {
            $data = self::GetServiceBySlug($service);
            return $data['key'] ?? null;
        }
    }

    /**
     * check disable shipping
     */
    public static function DisableBySettings(string $service, string $country): bool
    {
        $disable = false;
        $zasilkovna_services = get_option('zasilkovna_services');

        if (tzas_is_native_method($service)) {
            $native_type = tzas_get_native_slug_from_service($service);
            if (empty($zasilkovna_services['vydejnimista' . $native_type . '-active' . $country])) {
                $disable = true;
            }
        } else {
            $data = self::GetServiceBySlug($service);
            if (isset($zasilkovna_services['service-active-' . $data['key']]) && empty($zasilkovna_services['service-active-' . $data['key']])) {
                $disable = true;
            }
        }

        return $disable;
    }

    /**
     * check disable shipping
     */
    public static function IsPacketaAviable(string $country): bool
    {
        $zasilkovna_services = get_option('zasilkovna_services');
        foreach (TORET_ZASILKOVNA_NATIVE_TYPES as $native_type) {
            if (!empty($zasilkovna_services['vydejnimista' . $native_type . '-active' . $country])) {
                return true;
            }
        }
        return false;
    }

    /**
     * check disable shipping
     */
    public static function IsPacketaAviableAdmin(string $country): bool
    {
        $aviable = false;
        if (in_array(strtolower($country), self::zasilkovna_kde())) {
            $aviable = true;
        }
        return $aviable;
    }

    /**
     * Get Service by Slug
     */
    public static function GetServiceBySlug(string $service): array
    {
        $data = self::komplet_data();
        $return = array();
        foreach ($data as $key => $d) {
            if ($d['slug'] == $service) {
                $return = $d;
                $return['key'] = $key;
            }
        }

        if (!empty($return)) {
            $return = self::ReturnServiceData($service, $return);
        }

        return $return;
    }

    /**
     * Get Service by ID
     */
    public static function GetServiceByID(string $serviceID): array
    {
        $data = self::komplet_data();

        $return = array();

        if (!empty($serviceID) && $serviceID != 'null') {
            $return = $data[$serviceID];
            $return['key'] = $serviceID;
            $service = $return['slug'];

            if (!empty($return) && !empty($service)) {
                $return = self::ReturnServiceData($service, $return);
            }
        }

        return $return;
    }

    /**
     * Get Service by ID
     */
    public static function GetServicesByCountry(string $country, bool $branches): array
    {
        $return = $keys = array();
        $data = self::komplet_data();
        $zasilkovna_services = get_option('zasilkovna_services');
        foreach ($data as $key => $service) {
            if ($branches !== true) {
                $active = $zasilkovna_services['service-active-' . $key] ?? '';
                if ((strtolower($service['stat']) == $country) && ($active != '')) {
                    $keys[] = $key;
                }
            }
        }
        foreach ($keys as $key) {
            $return[] = self::GetServiceByID($key);
        }

        return $return;
    }

    /**
     * calculate_shipping function.
     */
    public static function GetPacketaShippingPrice($service, array $zasilkovna_option, array $zasilkovna_prices, string $country, $weight, $max_dim, $subtotal, $items = null)
    {
        $cost = -1;

        $native_type = tzas_get_native_slug_from_service($service);

        $calc_type = tzas_get_rate_fee_type($zasilkovna_option, $zasilkovna_prices, 'zasilkovna' . $native_type . '-' . $country);

        $shortcut = '';
        $price_shortcut = '';
        $limiter = $weight;
        if ($calc_type == 'weight') {
            $shortcut = 'hm';
        } else if ($calc_type == 'dimension') {
            $shortcut = $price_shortcut = 'dm';
            $limiter = $max_dim;
        } else if ($calc_type == 'price') {
            $shortcut = $price_shortcut = 'pr';
            $limiter = $subtotal;
        }

        if ($calc_type == 'single') {
            if (isset($zasilkovna_prices['zasilkovna' . $native_type . '-' . $country . '-celk']) && ($zasilkovna_prices['zasilkovna-' . $country . '-celk'] !== '')) {
                $cost = $zasilkovna_prices['zasilkovna' . $native_type . '-' . $country . '-celk'] ?? -1;
            }
        } else {
            if ($shortcut != '') {
                $allEmpty = true;
                if (isset($zasilkovna_prices['zasilkovna' . $native_type . '-cena' . $price_shortcut . '-' . $country]) && isset($zasilkovna_prices['zasilkovna' . $native_type . '-' . $shortcut . 'd-' . $country]) && isset($zasilkovna_prices['zasilkovna' . $native_type . '-' . $shortcut . 'o-' . $country]) && ($zasilkovna_prices['zasilkovna' . $native_type . '-' . $shortcut . 'o-' . $country] !== '')) {
                    foreach ($zasilkovna_prices['zasilkovna' . $native_type . '-' . $shortcut . 'o-' . $country] as $key => $hmo) {
                        if (($hmo != '') || ($zasilkovna_prices['zasilkovna' . $native_type . '-' . $shortcut . 'd-' . $country][$key] != '') || ($zasilkovna_prices['zasilkovna' . $native_type . '-cena' . $price_shortcut . '-' . $country][$key] != '')) {
                            if ($limiter >= $hmo && $limiter <= $zasilkovna_prices['zasilkovna' . $native_type . '-' . $shortcut . 'd-' . $country][$key]) {
                                $cost = $zasilkovna_prices['zasilkovna' . $native_type . '-cena' . $price_shortcut . '-' . $country][$key];
                                $allEmpty = false;
                                break;
                            }
                        }
                    }
                } else {
                    $cost = $zasilkovna_prices['zasilkovna' . $native_type . '-' . $country . '-celk'] ?? -1;
                }
            }
        }

        if (isset($allEmpty) && $allEmpty) {
            $cost = $zasilkovna_prices['zasilkovna' . $native_type . '-' . $country . '-celk'] ?? -1;
        }

        if (($cost != -1) && ($cost != '')) {
            $vatIncluded = (!empty($zasilkovna_option['price_with_vat']) && $zasilkovna_option['price_with_vat'] == 'ok');
            $fee_data = (new Taxes())->get_shipping_tax_data((float)$cost, $vatIncluded);
            $cost = (empty($fee_data['cost_excl_tax']) ? 0 : $fee_data['cost_excl_tax']);
        }

        if (empty($cost) && $cost != 0)
            $cost = -1;

        return $cost;
    }

    /**
     * calculate_shipping function.
     */
    public static function GetServiceCost(array $zasilkovna_option, array $zasilkovna_prices, string $country, $weight, $max_dim, $subtotal, array $data, $items = null)
    {
        $cost = -1;

        $calc_type = tzas_get_rate_fee_type($zasilkovna_option, $zasilkovna_prices, $data['slug']);

        $shortcut = '';
        $price_shortcut = '';
        $limiter = $weight;
        if ($calc_type == 'weight') {
            $shortcut = 'hm';
        } else if ($calc_type == 'dimension') {
            $shortcut = $price_shortcut = 'dm';
            $limiter = $max_dim;
        } else if ($calc_type == 'price') {
            $shortcut = $price_shortcut = 'pr';
            $limiter = $subtotal;
        }

        $limiter = (float)$limiter;

        if ($calc_type == 'single') {
            if (isset($zasilkovna_prices[$data['slug'] . '-celk'])) {
                $cost = $zasilkovna_prices[$data['slug'] . '-celk'] ?? -1;
            }
        } else {
            if ($shortcut != '') {

                $allEmpty = true;
                if (isset($zasilkovna_prices[$data['slug'] . '-cena' . $price_shortcut . '-' . $country]) && isset($zasilkovna_prices[$data['slug'] . '-' . $shortcut . 'd-' . $country]) && isset($zasilkovna_prices[$data['slug'] . '-' . $shortcut . 'o-' . $country]) && ($zasilkovna_prices[$data['slug'] . '-' . $shortcut . 'o-' . $country] !== '')) {
                    foreach ($zasilkovna_prices[$data['slug'] . '-' . $shortcut . 'o-' . $country] as $klic => $hmo) {
                        if (($hmo !== '') || ($zasilkovna_prices[$data['slug'] . '-' . $shortcut . 'd-' . $country][$klic] !== '') || ($zasilkovna_prices[$data['slug'] . '-cena' . $price_shortcut . '-' . $country][$klic] !== '')) {
                            if ($limiter >= (float)$hmo && $limiter <= (float)$zasilkovna_prices[$data['slug'] . '-' . $shortcut . 'd-' . $country][$klic]) {
                                $cost = $zasilkovna_prices[$data['slug'] . '-cena' . $price_shortcut . '-' . $country][$klic];
                                $allEmpty = false;
                            }
                        }
                    }
                } else {
                    $cost = $zasilkovna_prices[$data['slug'] . '-celk'] ?? -1;
                }
            }
        }

        if (isset($allEmpty) && $allEmpty) {
            $cost = $zasilkovna_prices[$data['slug'] . '-celk'] ?? -1;
        }

        if (empty($cost) && $cost != 0)
            $cost = -1;

        if (($cost != -1) && ($cost != '')) {
            $vatIncluded = (!empty($zasilkovna_option['price_with_vat']) && $zasilkovna_option['price_with_vat'] == 'ok');
            $fee_data = (new Taxes())->get_shipping_tax_data((float)$cost, $vatIncluded);
            $cost = (empty($fee_data['cost_excl_tax']) ? 0 : $fee_data['cost_excl_tax']);
        }

        return $cost;
    }


    /**
     * check price vat settings
     */
    public static function ZasilkovnaShippingCheckVat(array $zasilkovna_option, $cost, $items = null)
    {
        if (wc_tax_enabled()) {

            $shippingBasicTax = get_option('woocommerce_shipping_tax_class', '');
            $_tax = new WC_Tax();

            $ProductTaxPercent = 0;

            if ($shippingBasicTax == 'inherit') {

                if (empty($items))
                    $items = WC()->cart->get_cart();

                $ProductTaxPercentArray = array();

                foreach ($items as $item) {
                    $_product = wc_get_product($item['variation_id'] ?: $item['product_id']);
                    $array = $_tax->get_rates($_product->get_tax_class());
                    $ProductTax = reset($array);
                    if (isset($ProductTax['rate'])) {
                        $ProductTaxPercentArray[] = $ProductTax['rate'];
                    }
                }

                if (!empty($ProductTaxPercentArray)) {
                    $ProductTaxPercent = max($ProductTaxPercentArray);
                } else {
                    $rates_all = $_tax->get_rates();
                    if (!empty($rates_all)) {
                        $ProductTax = reset($rates_all);
                        $ProductTaxPercent = $ProductTax['rate'];
                    }
                }

            } else {
                $getTax = $_tax->get_rates($shippingBasicTax);
                $taxRate = reset($getTax);
                if (isset($taxRate['rate'])) {
                    $ProductTaxPercent = $taxRate['rate'];
                } else {
                    $rates_all = $_tax->get_rates();
                    if (!empty($rates_all)) {
                        $ProductTax = reset($rates_all);
                        $ProductTaxPercent = $ProductTax['rate'];
                    }
                }
            }
            if (!empty($zasilkovna_option['price_with_vat']) && $zasilkovna_option['price_with_vat'] == 'ok') {
                $cost = ($cost / (100 + $ProductTaxPercent) * 100);
            }
        }

        return $cost;
    }

    /**
     * check free shipping
     */
    public static function PacketaFreeShipping($service, array $zasilkovna_prices, $cost, string $moje_country, $subtotal = null, $subtotalTax = null)
    {
        $native_type = tzas_get_native_slug_from_service($service);
        if (!empty($zasilkovna_prices['zasilkovna' . $native_type . '-' . $moje_country . '-free'])) {
            $free = $zasilkovna_prices['zasilkovna' . $native_type . '-' . $moje_country . '-free'];
            $cost = self::PacketaFreeCurrencyCompatibility($free, $cost, $subtotal, $subtotalTax);
        }
        return $cost;
    }

    /**
     * check free shipping for services
     */
    public static function ServiceFreeShipping(array $zasilkovna_option, array $zasilkovna_prices, $cost, array $data, $subtotal = null, $subtotalTax = null)
    {
        if (!empty($zasilkovna_prices[$data['slug'] . '-free'])) {
            $free = $zasilkovna_prices[$data['slug'] . '-free'];
            $cost = self::PacketaFreeCurrencyCompatibility($free, $cost, $subtotal, $subtotalTax);
        }

        return $cost;
    }

    /**
     * create label
     */
    public static function PacketaLabel($cost, string $country, $method): string
    {
        $native_type = tzas_get_native_slug_from_service($method);
        $zasilkovna_services = get_option('zasilkovna_services');
        $label = $zasilkovna_services['vydejnimista' . $native_type . $country] ?? tzas_get_native_label($native_type);
        if ($cost == 0) {
            $text = __('Free', WOOZASILKOVNASLUG);
            $label = $label . ': ' . apply_filters('zasilkovna_free_shipping_label', $text, $method);
            $label = apply_filters('zasilkovna_shipping_label', $label, $method);
        }

        return apply_filters('toret_packeta_label', $label, $method);
    }

    /**
     * label for services
     */
    public static function ServiceLabel(array $zasilkovna_services, $cost, int $key): string
    {
        if ($cost == 0) {
            $text = __('Free', WOOZASILKOVNASLUG);
            $label = $zasilkovna_services['service-label-' . $key] . ': ' . apply_filters('zasilkovna_free_shipping_label', $text);
            $label = apply_filters('zasilkovna_shipping_label', $label);
        } else {
            $label = $zasilkovna_services['service-label-' . $key];
        }

        return (string)apply_filters('toret_packeta_service_label', $label);
    }

    /**
     * change free shipping currency
     */
    private static function PacketaFreeCurrencyCompatibility($free, $cost, $subtotal = null, $subtotalTax = null)
    {
        $free = self::currency_compatibility($free);

        $free = apply_filters('zasilkovana_free_shipping_filter', $free);

        $subtotal = ToretZasilkovnaHelper::get_correct_cart_subtotal($subtotal);

        if (!empty($subtotal) && $subtotal >= $free) {
            $cost = 0;
        }

        return $cost;
    }

    static function get_correct_cart_subtotal($subtotal = null)
    {
        if (isset(WC()->cart)) {

            if (empty($subtotal)) {
                $subtotal = WC()->cart->get_subtotal();
            }

            if (!empty(WC()->cart->applied_coupons)) {
                $coupons = WC()->cart->get_applied_coupons();
                foreach ($coupons as $coupon) {
                    $subtotal -= WC()->cart->get_coupon_discount_amount($coupon, false);
                }
            }

            if (get_option('woocommerce_calc_taxes') === 'yes') {
                $subtotalTax = WC()->cart->get_subtotal_tax();
                $subtotal += $subtotalTax;
            }

        }
        return $subtotal;
    }

    /**
     * return service data
     */
    private static function ReturnServiceData(string $service, array $return): array
    {
        $zasilkovna_prices = get_option('zasilkovna_prices');
        $zasilkovna_services = get_option('zasilkovna_services');
        $return['aktivovano'] = $zasilkovna_services['service-active-' . $return['key']] ?? '';

        if (isset($zasilkovna_prices[$service . '-celk']) && ($zasilkovna_prices[$service . '-celk'] !== '')) {
            $return['hmo'] = $zasilkovna_prices[$service . '-hmo-' . strtolower($return['stat'])] ?? '';
            $return['hmd'] = $zasilkovna_prices[$service . '-hmd-' . strtolower($return['stat'])] ?? '';
            $return['dmo'] = $zasilkovna_prices[$service . '-dmo-' . strtolower($return['stat'])] ?? '';
            $return['dmd'] = $zasilkovna_prices[$service . '-dmd-' . strtolower($return['stat'])] ?? '';
            $return['pro'] = $zasilkovna_prices[$service . '-pro-' . strtolower($return['stat'])] ?? '';
            $return['prd'] = $zasilkovna_prices[$service . '-prd-' . strtolower($return['stat'])] ?? '';
            //$return['cena'] = $zasilkovna_prices[$service . '-cena-' . strtolower($return['stat'])] ?? '';
            //$return['cenadm'] = $zasilkovna_prices[$service . '-cenadm-' . strtolower($return['stat'])] ?? '';
            $return['cenapr'] = $zasilkovna_prices[$service . '-cenapr-' . strtolower($return['stat'])] ?? '';
            $return['feeo'] = $zasilkovna_prices[$service . '-feeo-' . strtolower($return['stat'])] ?? '';
            $return['feed'] = $zasilkovna_prices[$service . '-feed-' . strtolower($return['stat'])] ?? '';
            $return['cenafee'] = $zasilkovna_prices[$service . '-cenafee-' . strtolower($return['stat'])] ?? '';
            $return['celk'] = $zasilkovna_prices[$service . '-celk'] ?? '';
            $return['hmotnost'] = $zasilkovna_prices[$service . '-hmotnost'] ?? '';
            $return['totalprice'] = $zasilkovna_prices[$service . '-totalprice'] ?? '';
            $return['dim-check'] = $zasilkovna_prices[$service . '-dim-check'] ?? '';
            $return['dim-check-box'] = $zasilkovna_prices[$service . '-dim-check-box'] ?? '';
            $return['dim-one'] = $zasilkovna_prices[$service . '-dim-one'] ?? '';
            $return['dim-one-l'] = $zasilkovna_prices[$service . '-dim-one-l'] ?? '';
            $return['dim-one-h'] = $zasilkovna_prices[$service . '-dim-one-h'] ?? '';
            $return['dim-one-w'] = $zasilkovna_prices[$service . '-dim-one-w'] ?? '';
            $return['dim-sum'] = $zasilkovna_prices[$service . '-dim-sum'] ?? '';
            $return['dobirkacena'] = $zasilkovna_prices[$service . '-dobirka'] ?? '';
            $return['dobirkamax'] = $zasilkovna_prices[$service . '-dobirka-max'] ?? '';
            $return['free'] = $zasilkovna_prices[$service . '-free'] ?? '';
            $return['type'] = $return['type'] ?? 'carrier';
        }

        return $return;
    }

    /**
     * get z-points data
     */
    private static function GetZPointsData(string $country, $native_type = ''): array
    {
        $zasilkovna_prices = get_option('zasilkovna_prices');
        $return = array();

        if (isset($zasilkovna_prices['zasilkovna-' . strtolower($country) . '-celk']) && ($zasilkovna_prices['zasilkovna-' . strtolower($country) . '-celk'] !== '')) {
            $return['hmo'] = $zasilkovna_prices['zasilkovna' . $native_type . '-hmo-' . strtolower($country)] ?? '';
            $return['hmd'] = $zasilkovna_prices['zasilkovna' . $native_type . '-hmd-' . strtolower($country)] ?? '';
            $return['dmo'] = $zasilkovna_prices['zasilkovna' . $native_type . '-dmo-' . strtolower($country)] ?? '';
            $return['dmd'] = $zasilkovna_prices['zasilkovna' . $native_type . '-dmd-' . strtolower($country)] ?? '';
            $return['pro'] = $zasilkovna_prices['zasilkovna' . $native_type . '-pro-' . strtolower($country)] ?? '';
            $return['prd'] = $zasilkovna_prices['zasilkovna' . $native_type . '-prd-' . strtolower($country)] ?? '';
            $return['cena'] = $zasilkovna_prices['zasilkovna' . $native_type . '-cena-' . strtolower($country)] ?? '';
            //$return['cenahm'] = $zasilkovna_prices['zasilkovna' . $native_type . '-cenahm-' . strtolower($country)] ?? '';
            $return['cenapr'] = $zasilkovna_prices['zasilkovna' . $native_type . '-cenapr-' . strtolower($country)] ?? '';
            $return['feeo'] = $zasilkovna_prices['zasilkovna' . $native_type . '-feeo-' . strtolower($country)] ?? '';
            $return['feed'] = $zasilkovna_prices['zasilkovna' . $native_type . '-feed-' . strtolower($country)] ?? '';
            $return['cenafee'] = $zasilkovna_prices['zasilkovna' . $native_type . '-cenafee-' . strtolower($country)] ?? '';
            $return['celk'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-celk'] ?? '';
            $return['hmotnost'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-hmotnost'] ?? '';
            $return['totalprice'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-totalprice'] ?? '';
            $return['dim-check'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dim-check'] ?? '';
            $return['dim-check-box'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dim-check-box'] ?? '';
            $return['dim-one'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dim-one'] ?? '';
            $return['dim-one-l'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dim-one-l'] ?? '';
            $return['dim-one-h'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dim-one-h'] ?? '';
            $return['dim-one-w'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dim-one-w'] ?? '';
            $return['dim-sum'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dim-sum'] ?? '';
            $return['dobirkacena'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dobirka'] ?? '';
            $return['dobirkamax'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-dobirka-max'] ?? '';
            $return['free'] = $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($country) . '-free'] ?? '';
            $return['stat'] = strtolower($country);
            $return['type'] = 'native';
        }

        return $return;
    }

    /**
     * return packet status
     */
    public static function GetPacketStatus($order_id, $meta_status, $meta_package, $single = true, $packetIds = array(), $package_order_now = 0)
    {
        return self::fetchPacketkaStatuses($order_id, $meta_package, $meta_status, $single, $package_order_now, $packetIds);
    }

    static function fetchPacketkaStatuses($order_id, $meta_package, $meta_status, $single, $package_order_now, $packetIds)
    {
        $gw = new SoapClient("https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl");
        $zasilkovna_option = get_option('zasilkovna_option');
        $apiPassword = $zasilkovna_option['api_password'];

        if (!$single) {
            $packetIds = explode(';', Toret_HPOS_Compatibility::get_order_meta($order_id, $meta_package));
        }

        $i = 1;

        $zasilkovna_order_status = Toret_HPOS_Compatibility::get_order_meta($order_id, $meta_status, true);

        if (!$single) {
            $zasilkovna_order_status = '';
        }

        foreach ($packetIds as $id) {

            if ($id != '') {

                try {
                    $packet = $gw->packetStatus($apiPassword, $id);
                    if (isset($packet->statusCode)) {
                        if ($single) {
                            $zasilkovna_order_status .= ($package_order_now == 1 ? '' : ';') . $packet->statusCode;
                        } else {
                            $zasilkovna_order_status .= ($i == 1 ? '' : ';') . $packet->statusCode;
                        }
                    }

                } catch (SoapFault $e) {
                    error_log($e->getMessage());
                }
            }

            $i++;
        }

        Toret_HPOS_Compatibility::update_order_meta($order_id, $meta_status, $zasilkovna_order_status);

        return $zasilkovna_order_status;
    }

    /**
     * Get current payment gateway
     */
    public static function get_current_gateway()
    {
        if (empty(WC()->payment_gateways->get_available_payment_gateways()))
            return false;

        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        $current_gateway = null;

        $default_gateway = get_option('woocommerce_default_gateway');
        if (!empty($available_gateways)) {

            // Chosen Method
            if (isset(WC()->session->chosen_payment_method) && isset($available_gateways[WC()->session->chosen_payment_method])) {

                $current_gateway = $available_gateways[WC()->session->chosen_payment_method];

            } elseif (isset($available_gateways[$default_gateway])) {
                $current_gateway = $available_gateways[$default_gateway];
            } else {
                $current_gateway = current($available_gateways);
            }
        }

        if (!is_null($current_gateway)) {
            return $current_gateway;
        } else {
            return false;
        }
    }

    /**
     * Current payment gateway settings
     */
    public
    static function get_current_gateway_settings()
    {
        if ($current_gateway = self::get_current_gateway()) {
            return $current_gateway->settings;
        } else {
            return false;
        }
    }

    /**
     * Check if is disable product in cart
     */
    public static function set_fee_by_dobirka_free_shipping($fee, $shipping_total)
    {
        $zasilkovna_option = get_option('zasilkovna_option');

        if ($shipping_total == 0) {
            if (!empty($zasilkovna_option['priplatek_dobirka']) && $zasilkovna_option['priplatek_dobirka'] == 'ano') {
                return $fee;
            } else {
                return 0;
            }
        } else {
            return $fee;
        }
    }

    /**
     * return array of countries where Zasilkovna have brunches
     */
    static public function zasilkovna_kde(): array
    {
        return TORET_ZASILKOVNA_NATIVE_COUNTRIES;
    }

    /**
     * return array of countries
     */
    static public function komplet_staty(): array
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'zasilkovna_staty';

        $staty = $wpdb->get_results("SELECT * FROM $table_name");

        $komplet = array();
        foreach ($staty as $stat) {
            $komplet[$stat->stat] = $stat->statnazev;
        }

        return $komplet;
    }

    /**
     * return array of countries
     */
    static public function get_all_packetka_countries(): array
    {
        global $wpdb;

        $countries = WC()->countries->countries;

        $table_name = $wpdb->prefix . 'zasilkovna_staty';

        $staty = $wpdb->get_results("SELECT DISTINCT(stat) FROM $table_name");

        $komplet = array();
        foreach ($staty as $stat) {
            $komplet[$stat->stat] = $countries[$stat->stat];
        }

        return $komplet;
    }

    /**
     * return array of countries
     */
    public function get_povolene_staty(): array
    {
        $povolene_staty = array();

        $zasilkovna_services = get_option('zasilkovna_services');

        $NATIVE_TYPE = '';
        foreach (TORET_ZASILKOVNA_NATIVE_COUNTRIES as $country) {
            $slug_active = 'vydejnimista' . $NATIVE_TYPE . '-active' . strtolower($country);
            if (!empty($zasilkovna_services[$slug_active])) {
                $povolene_staty[] = strtoupper($country);
            }
        }

        $carriers = $this->komplet_data();
        foreach ($carriers as $key => $service) {
            $active = !empty($zasilkovna_services['service-active-' . $key]);
            if ($active) {
                $povolene_staty[] = strtoupper($service['stat']);
            }
        }

        return $povolene_staty;
    }

    /**
     * return array of all deliveries
     */
    static public function komplet_data(): array
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zasilkovna_dopravci';

        $dopravci = $wpdb->get_results("SELECT * FROM $table_name ORDER BY stat, nazev");

        $komplet = array();

        foreach ($dopravci as $dop) {

            if ($dop->removed == 0) {
                $komplet[$dop->dopravce_id] = array(
                    "stat" => $dop->stat,
                    "statnazev" => __($dop->statnazev, 'zasilkovna'),
                    "nazev" => $dop->nazev,
                    "preklad" => __($dop->nazev, 'zasilkovna'),
                    "dobirka" => $dop->dobirka,
                    "deklarace" => $dop->deklarace,
                    "rozmery" => $dop->rozmery,
                    "vaha" => $dop->vaha,
                    "pobocky" => $dop->pobocky,
                    "slug" => $dop->slug,
                    "prac" => $dop->prac,
                    "active" => $dop->active,
                    "api" => $dop->api,
                    "id" => $dop->dopravce_id,
                    "type" => $dop->type,
                );
            }
        }

        return $komplet;
    }

    /**
     * return array of statuses
     */
    static public function zasilkovna_statuses(): array
    {
        return array(
            1 => __('Awaiting delivery by the shop', 'zasilkovna'),
            2 => __('Received', 'zasilkovna'),
            3 => __('Ready for delivery', 'zasilkovna'),
            4 => __('Order is being shipped', 'zasilkovna'),
            5 => __('Ready for pickup', 'zasilkovna'),
            6 => __('Dispatched by carrier', 'zasilkovna'),
            7 => __('Delivered', 'zasilkovna'),
            9 => __('Refund', 'zasilkovna'),
            10 => __('Returned to the consignor', 'zasilkovna'),
            11 => __('Shipment cancelled', 'zasilkovna'),
            12 => __('The shipment has been received and is on its way to the depot', 'zasilkovna'),
            15 => __('Reverse packet has been accepted at our branch', 'zasilkovna'),
            16 => __('Packeta Home made unsuccessful delivery attempt of packet', 'zasilkovna'),
            17 => __('Packeta Home delivery attempt of packet end in rejected by recipient response', 'zasilkovna'),
            18 => __('Packet rejected by recipient (non HD rejection)', 'zasilkovna'),
            19 => __('Packeta Home delivery made unsuccessful delivery attempt of packet and there was no branch for redirection nearby.', 'zasilkovna'),
            20 => __('Storage time expired and packet will be returned.', 'zasilkovna'),
            21 => __('Packet was cancelled but it was consigned later on. Packet will be returned.', 'zasilkovna'),
            22 => __('Packet does not meet the shipping conditions (overlimit) and will be returned.', 'zasilkovna'),
            23 => __('Unsuccessful delivery attempt of packet into Z-BOX.', 'zasilkovna'),
            24 => __('Last delivery attempt to the box has been made. A change in the delivery method will be required.', 'zasilkovna'),
            25 => __('First delivery attempt made by external carrier.', 'zasilkovna'),
            26 => __('Packet is currently being reviewed.', 'zasilkovna'),
            27 => __('Review of the packet has been completed.', 'zasilkovna'),
            999 => __('Not exported to Packeta', 'zasilkovna')
        );
    }

    /**
     * return array used states
     */
    static public function komplet_admin_staty(): array
    {
        $staty = array();
        $staty_pre = self::komplet_staty();
        foreach (self::get_komplet_data() as $data) {
            $staty[$data['stat']] = $staty_pre[$data['stat']];
        }

        return $staty;
    }

    /**
     * return array of states slugs
     */
    static public function komplet_staty_kont(): array
    {
        $staty_kont = array();
        $staty_pre = self::komplet_staty();
        foreach (self::get_komplet_data() as $data) {
            $staty_kont[$data['stat']] = sanitize_title($staty_pre[$data['stat']]);
        }
        asort($staty_kont);

        return $staty_kont;
    }

    /**
     * Get customer country
     */
    static public function get_customer_country(): string
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
     * Set shipping services
     */
    public static function set_services(): array
    {
        $komplet_data = self::komplet_data();
        return array_keys($komplet_data);
    }

    /**
     * Set shipping services ids for order
     */
    public static function set_order_shipping_ids(): array
    {
        $komplet_data = self::komplet_data();

        $ids = array();
        foreach (TORET_ZASILKOVNA_NATIVE_SHIPPINGS as $value) {
            $ids[] = 'zasilkovna>' . $value;
        }

        foreach ($komplet_data as $data) {
            $ids[] = $data['prac'];
        }

        return $ids;
    }

    public static function DisableByDim(string $service, string $country, $cart = false, $order = null)
    {
        $zasilkovna_option = get_option('zasilkovna_option');

        $disable = false;

        $ZasilkovnaLib = new ToretZasilkovnaLib();

        if (tzas_is_native_method($service)) {
            $native_type = tzas_get_native_slug_from_service($service);
            $data = self::GetZPointsData(strtolower($country), $native_type);
        } else {
            $data = self::GetServiceBySlug($service);
        }

        $check_enabled = (isset($data['dim-check']) && $data['dim-check'] == 'ok');
        //$check_for_box_enabled = (isset($data['dim-check-box']) && $data['dim-check-box'] == 'ok');

        if ($check_enabled) {

            if ($cart) {
                $one_dim = $ZasilkovnaLib->DimensionHelper->get_cart_max_dimension();
                $sum_dim = $ZasilkovnaLib->DimensionHelper->get_max_dimension_sum($cart, $order);
            } else {
                $one_dim = Toret_HPOS_Compatibility::get_order_meta($order->get_id(), 'zasilkovna_custom_dim_one', true);
                $sum_dim = Toret_HPOS_Compatibility::get_order_meta($order->get_id(), 'zasilkovna_custom_dim_sum', true);
                if ($one_dim == '') {
                    $one_dim = $ZasilkovnaLib->DimensionHelper->get_order_max_dimension($order);
                }
                if ($sum_dim == '') {
                    $sum_dim = $ZasilkovnaLib->DimensionHelper->get_max_dimension_sum($cart, $order);
                }
            }

            $one_dim = apply_filters('zasilkovna_packeta_max_side_dim', $one_dim);
            $sum_dim = apply_filters('zasilkovna_packeta_max_sides_sum_dim', $sum_dim);

            $max_one_dim = (isset($data['dim-one']) ? ($data['dim-one'] !== '' ? ($data['dim-one'] == '' ? ($zasilkovna_option['max_dim_one'] ?? "" != '' ? 0.7 : 10) : $data['dim-one']) : ($zasilkovna_option['max_dim_one'] ?? "" !== '' ? 0.7 : 10)) : ($zasilkovna_option['max_dim_one'] !== '' ? 0.7 : 10));
            $max_sum_dim = (isset($data['dim-sum']) ? ($data['dim-sum'] !== '' ? ($data['dim-sum'] == '' ? ($zasilkovna_option['max_dim_sum'] ?? "" != '' ? 1.2 : 10) : $data['dim-sum']) : ($zasilkovna_option['max_dim_sum'] ?? "" !== '' ? 1.2 : 10)) : ($zasilkovna_option['max_dim_sum'] !== '' ? 1.2 : 10));

            if (!empty($max_one_dim)) {
                if ((float)$one_dim > (float)$max_one_dim) {
                    $disable = true;
                }
            }
            if (!empty($max_sum_dim)) {
                if ((float)$sum_dim > (float)$max_sum_dim) {
                    $disable = true;
                }
            }
        }

        /*if ($check_for_box_enabled) {
            if ($cart) {
                $dimensions = Dimensions::get_max_package_dimensions();
            } else {
                $dimensions = Dimensions::get_max_package_dimensions($order);
            }

            $limit_dims = [
                (float)($data['dim-one-l'] ?? 0),
                (float)($data['dim-one-w'] ?? 0),
                (float)($data['dim-one-h'] ?? 0)
            ];

            rsort($dimensions);
            rsort($limit_dims);

            if (
                $dimensions[0] > $limit_dims[0] ||
                $dimensions[1] > $limit_dims[1] ||
                $dimensions[2] > $limit_dims[2]
            ) {
                $disable = true;
            }
        }*/

        return $disable;
    }

    public static function DisableByTotalPrice(string $service, string $country, $cart = false, $order = null)
    {
        $disable = false;

        if (tzas_is_native_method($service)) {
            $native_type = tzas_get_native_slug_from_service($service);
            $data = self::GetZPointsData(strtolower($country), $native_type);
        } else {
            $data = self::GetServiceBySlug($service);
        }

        $shpping_method_max = $shpping_method_max_pre = $data['totalprice'] ?? "";

        $shpping_method_max = self::currency_compatibility($shpping_method_max);

        $shpping_method_max = apply_filters('zasilkovna_shipping_max_total_price', $shpping_method_max, $service, $country, $cart, $order);

        if (!empty($shpping_method_max) && $shpping_method_max_pre != "") {

            if ($cart) {

                $subtotal = ToretZasilkovnaHelper::get_correct_cart_subtotal();

            } else {
                $subtotal = $order->get_total();
            }

            $subtotal = apply_filters('zasilkovna_shipping_max_total_price_subtotal', $subtotal, $service, $country, $cart, $order);

            if (!empty($subtotal)) {
                if ((float)$subtotal > (float)$shpping_method_max) {
                    $disable = true;
                }
            }
        }

        return $disable;
    }

    /**
     * Check if there is product with empty weight in cart
     */
    public function is_empty_weight_product_in_cart(): int
    {
        global $woocommerce;

        $included = 0;
        $items = $woocommerce->cart->get_cart();

        foreach ($items as $values) {
            $_product = $values['data']->get_id();
            $product = wc_get_product($_product);
            $weight = $product->get_weight();
            if (empty($weight) || $weight == '') {
                $included = 1;
                break;
            }
        }

        return $included;
    }

    public function get_language_by_country($country): string
    {
        $array = array(
            'CZ' => 'cs_CZ',
            'DE' => 'de_DE',
            'AT' => 'de_DE',
            'GB' => 'en_GB',
            'HU' => 'hu_HU',
            'SK' => 'sk_SK',
            'PL' => 'pl_PL',
            'RO' => 'ro_RO',
            'UA' => 'uk_UA',
            'ES' => 'es_ES',
            'FR' => 'fr_FR',
            'BE' => 'fr_BE',
            'PT' => 'pt_PT',
            'RU' => 'ru_RU',
            'SE' => 'sv_SE',
            'GR' => 'el_GR',
            'IT' => 'it_IT',
            'BG' => 'bg_BG',
            'SI' => 'sl_SI',
            'HR' => 'hr_HR',
            'LV' => 'lv_LV',
            'LT' => 'lt_LT',
            'ET' => 'et_ET',
            'DK' => 'da_DK',
            'FI' => 'fi_FI',

        );

        return $array[$country] ?? 'en_GB';
    }

    function check_email_address($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (strpos($email, '--') !== false) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }


    function clear_all_package_data($order_id)
    {
        packetka_cancel_package($order_id, false, false);
        packetka_cancel_package($order_id, true, false);

        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_id_zasilky_assistent');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcode_assistent');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_order_claim_status');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcodeText_assistent');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_is_multipackage_assistent');

        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_id_zasilky');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcode');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_status');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcodeText');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_is_multipackage');

        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_id_zasilky_assistent');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcode_assistent');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_order_claim_status');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcodeText_assistent');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_is_multipackage_assistent');

        Toret_HPOS_Compatibility::delete_order_meta($order_id, '_zasilkovna_odeslano');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_id_zasilky');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcode');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_weights');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcodeText');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_package_count');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_is_multipackage');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_failText');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_status');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_order_status');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_failText');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, '_zasilkovna_odeslano');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_id_zasilky');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcode');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_weights');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcodeText');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_package_count');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_is_multipackage');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_failText');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_status');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_order_status');
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_failText');

        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_id_zasilky_dopravce');
    }

    /**
     * Check if there is product with free shipping in cart
     */
    function is_disabled_zboxes()
    {
        global $woocommerce;

        $enabled = 'no';
        $items = $woocommerce->cart->get_cart();

        foreach ($items as $values) {

            $_product = $values['data']->get_id();
            $variationID = '';

            $product = wc_get_product($_product);
            if (!$product) {
                continue;
            }
            if (!$product->is_type('simple')) {
                $variationID = $_product;
                $_product = $product->get_parent_id();
            }

            if ($variationID != '') {
                $enabled_var = get_post_meta($variationID, 'tzas_disable_var_zboxes', true);

                if (!empty($enabled_var) && ($enabled_var == 'yes')) {
                    $enabled = 'yes';
                    break;
                }
            }

            $enabled_produkt = get_post_meta($_product, 'tzas_disable_zboxes', true);

            if (!empty($enabled_produkt) && ($enabled_produkt == 'yes')) {
                $enabled = 'yes';
                break;
            }

            $terms = get_the_terms($_product, 'product_cat');

            if (!is_wp_error($terms) && $terms)
                foreach ($terms as $term) {

                    $product_cat_id = $term->term_id;
                    $enabled_cat_produkt = get_term_meta($product_cat_id, 'tzas_disable_cat_zboxes', true);

                    if (!empty($enabled_cat_produkt) && ($enabled_cat_produkt == 'yes')) {
                        $enabled = 'yes';
                        break;
                    }
                }
        }

        return $enabled;
    }

    /**
     * Check if there is product with free shipping in cart
     */
    function is_disabled_zboxes_order($order)
    {
        $enabled = 'no';

        foreach ($order->get_items() as $item_id => $item) {

            $_product = $item->get_product_id();
            $variationID = '';

            $product = wc_get_product($_product);
            if (!$product) {
                continue;
            }
            if (!$product->is_type('simple')) {
                $variationID = $_product;
                $_product = $product->get_parent_id();
            }

            if ($variationID != '') {
                $enabled_var = get_post_meta($variationID, 'tzas_disable_var_zboxes', true);

                if (!empty($enabled_var) && ($enabled_var == 'yes')) {
                    $enabled = 'yes';
                    break;
                }
            }

            $enabled_produkt = get_post_meta($_product, 'tzas_disable_zboxes', true);

            if (!empty($enabled_produkt) && ($enabled_produkt == 'yes')) {
                $enabled = 'yes';
                break;
            }

            $terms = get_the_terms($_product, 'product_cat');

            if (!is_wp_error($terms) && $terms)
                foreach ($terms as $term) {

                    $product_cat_id = $term->term_id;
                    $enabled_cat_produkt = get_term_meta($product_cat_id, 'tzas_disable_cat_zboxes', true);

                    if (!empty($enabled_cat_produkt) && ($enabled_cat_produkt == 'yes')) {
                        $enabled = 'yes';
                        break;
                    }
                }
        }

        return $enabled;
    }

    function groupByStat($data)
    {
        $grouped = [];

        foreach ($data as $item) {
            if (isset($item['stat'])) {
                $grouped[$item['stat']][] = $item;
            }
        }

        return $grouped;
    }

    function get_all_shipping_methods($onlyActive = false)
    {
        $update_option = 'toret_zasilkovna_block_version_xxxiii';
        $shipping_methods = $this->gather_shipping_methods($onlyActive);
        $current_version = get_option($update_option, '6.9.3');
        if (version_compare($current_version, '7.0.0', '<')) {

            tzas_add_native_to_carriers();
            tzas_add_methods_to_shipping_zones($shipping_methods);

            update_option($update_option, TORETZASILKOVNAVERSION);

            $zasilkovna_option = get_option('zasilkovna_option', []);
            $zasilkovna_option['tzas_show_icon'] = get_option('tzas_show_icon');
            $zasilkovna_option['tzas_show_pickup_icon'] = get_option('tzas_show_pickup_icon', 'ok');
            $zasilkovna_option['tzas_icon_pickup_custom_css'] = get_option('tzas_icon_custom_css');
            $zasilkovna_option['tzas_icon_custom_css'] = get_option('tzas_icon_pickup_custom_css');
            update_option('zasilkovna_option', $zasilkovna_option);

            $zasilkovna_option = get_option('zasilkovna_option', []);
            $zasilkovna_prices = get_option('zasilkovna_prices', []);
            foreach (TORET_ZASILKOVNA_NATIVE_COUNTRIES as $country) {

                $slug = 'zasilkovna' . '' . '-' . $country;
                if (!empty($zasilkovna_option['fee_by_price']) && $zasilkovna_option['fee_by_price'] == 'ok') {
                    $zasilkovna_prices[$slug . '-fee-type'] = 'single';
                } else {
                    $zasilkovna_prices[$slug . '-fee-type'] = 'total';
                }
            }

            foreach ($shipping_methods as $key => $service_data) {
                //$stat = strtolower($service_data['country']);
                if ($service_data['type'] != 'native') {
                    $slug = $service_data['slug'];
                } else {
                    continue;
                }

                if (!empty($zasilkovna_option['fee_by_price']) && $zasilkovna_option['fee_by_price'] == 'ok') {
                    $zasilkovna_prices[$slug . '-fee-type'] = 'single';
                } else {
                    $zasilkovna_prices[$slug . '-fee-type'] = 'total';
                }

                update_option('zasilkovna_prices', $zasilkovna_prices);
            }
        }

        return $shipping_methods;
    }

    function gather_shipping_methods($onlyActive = false, $country = null)
    {
        $shipping_methods = [];

        $zasilkovna_services = get_option('zasilkovna_services');

        $NATIVE_TYPE = '';

        $include_native = true;
        if (!empty($country)) {
            $slug_active = 'vydejnimista' . $NATIVE_TYPE . '-active' . strtolower($country);
            if (empty($zasilkovna_services[$slug_active]) && $onlyActive) {
                $include_native = false;
            }
            if (!in_array(strtolower($country), TORET_ZASILKOVNA_NATIVE_COUNTRIES)) {
                $include_native = false;
            }
        }

        if ($include_native) {
            $shipping_methods[TORET_ZASILKOVNA_NATIVE_SHIPPINGS[$NATIVE_TYPE]] = array(
                'title' => __('Packeta', WOOZASILKOVNASLUG),
                'original_title' => __('Packeta', WOOZASILKOVNASLUG) . ' ' . __('(Z-Point, Z-Box)', WOOZASILKOVNASLUG),
                'active' => '1',
                'type' => 'native',
                'country' => '',
                'slug' => TORET_ZASILKOVNA_NATIVE_SHIPPINGS[$NATIVE_TYPE],
            );
        }

        $carriers = $this->komplet_data();

        foreach ($carriers as $key => $service) {
            $active = !empty($zasilkovna_services['service-active-' . $key]);
            if ($onlyActive && !$active) {
                continue;
            }
            if (!empty($country) && $service['stat'] != $country) {
                continue;
            }
            $label = (!empty($zasilkovna_services['service-label-' . $key]) ? $zasilkovna_services['service-label-' . $key] : $service['nazev']);
            $shipping_methods[$service['slug']] = array(
                'title' => $label,
                'original_title' => $service['nazev'],
                'active' => $active ? '1' : '0',
                'type' => 'carrier',
                'country' => $service['stat'],
                'slug' => $service['slug'],
            );
        }

        return $shipping_methods;
    }

    function get_active_carriers_in_country($active, $code, $string = false)
    {
        $carriers = $this->gather_shipping_methods($active, $code);

        if (!$string) {
            return $carriers;
        } else {
            if (empty($carriers)) {
                return '-';
            } else {
                $carriers = array_map(function ($carrier) {
                    return $carrier['title'];
                }, $carriers);
                return implode(', ', $carriers);
            }
        }
    }

    /**
     * Adjusts the given cost to make it compatible with various WooCommerce currency switchers and plugins.
     *
     * @param float $cost The original cost value before performing currency adjustments.
     * @return float Adjusted cost value based on the active currency settings and conversions.
     */
    static function currency_compatibility($cost)
    {
        return ExchangeRates::currency_compatibility($cost);
    }

    function get_multipackage_data($zasilkovna_option, $weigt_input, $dim_input = null, $sum_dim_input = null)
    {
        $weight = (float)$weigt_input;
        $dim_input = (float)$dim_input;
        $sum_dim_input = (float)$sum_dim_input;

        $sources = $zasilkovna_option['multipackage_source'] ?? ['weight'];
        if (!is_array($sources)) {
            $sources = [$sources];
        }

        $final_qty = 1;
        $winning_toBeDivided = 0;
        $winning_threshold = 0;

        foreach ($sources as $source) {
            $toBeDivided = 0;
            $threshold = 0;

            switch ($source) {
                case 'weight':
                    $toBeDivided = $weight;
                    $threshold = $zasilkovna_option['multipackage_treshold_weight'] ?? $zasilkovna_option['multipackage_treshold'] ?? 0;
                    break;
                case 'longest':
                    $toBeDivided = $dim_input;
                    $threshold = $zasilkovna_option['multipackage_treshold_longest'] ?? 0;
                    break;
                case 'sum':
                    $toBeDivided = $sum_dim_input;
                    $threshold = $zasilkovna_option['multipackage_treshold_sum'] ?? 0;
                    break;
                default:
                    continue 2;
            }

            $threshold = (float)$threshold;
            if ($threshold <= 0) {
                continue;
            }

            $qty_pre = 1;
            if ($toBeDivided > $threshold) {
                $qty_pre = ceil($toBeDivided / $threshold);
            }

            $effective_toBeDivided = $toBeDivided;
            if ($source === 'weight' && isset($zasilkovna_option['zas_add_wrap_weight']) && $zasilkovna_option['zas_add_wrap_weight'] > 0) {
                $wrap_weight_per_package = (float)$zasilkovna_option['zas_add_wrap_weight'];
                $effective_toBeDivided += $wrap_weight_per_package * $qty_pre;
            }

            $current_qty = 1;
            if ($effective_toBeDivided > $threshold) {
                $current_qty = ceil($effective_toBeDivided / $threshold);
            }
            if ($current_qty > $final_qty) {
                $final_qty = $current_qty;
                $winning_toBeDivided = $effective_toBeDivided;
                $winning_threshold = $threshold;
            }
        }

        $return = [];

        if ($final_qty > 1) {
            $remainder = fmod($winning_toBeDivided, $winning_threshold);
            if ($remainder == 0.0) {
                $remainder = $winning_threshold;
            }

            $return['qty'] = (int)$final_qty;
            $return['weight'] = $winning_toBeDivided;
            $return['baseweight'] = $winning_threshold;
            $return['reminder'] = $remainder;
            $return['codDisabled'] = true;
            $return['toBeDivided'] = $winning_toBeDivided;

        } else {
            $final_weight_single_package = $weight;
            if (isset($zasilkovna_option['zas_add_wrap_weight']) && $zasilkovna_option['zas_add_wrap_weight'] > 0) {
                $final_weight_single_package += (float)$zasilkovna_option['zas_add_wrap_weight'];
            }

            $return['qty'] = 1;
            $return['weight'] = $final_weight_single_package;
            $return['baseweight'] = $final_weight_single_package;
            $return['reminder'] = $final_weight_single_package;
            $return['codDisabled'] = false;
            $return['toBeDivided'] = $final_weight_single_package;
        }

        $return['enabled'] = ($zasilkovna_option['multipackage_enable'] ?? "") == 'ok';

        return $return;
    }


    function get_actions_visibility_data($order_id)
    {

        $rozmery = 0;
        $shippingID = '';
        $rozmery_data = '';
        $deklarace = 'ne';
        $apiAllowed = '1';
        $declaration_exception = false;

        $order = wc_get_order($order_id);

        $ToretZasilkovna = ToretZasilkovnaLib();

        $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy', true);
        $service = tzas_get_service_from_string($zasilkovna_shipping);

        if ($zasilkovna_shipping) {
            $komplet_data = $ToretZasilkovna->Helper->komplet_data();

            if (tzas_is_native_method($service)) {
                $shippingID = (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_carrierId') != 'undefined' ? Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_carrierId') : 0);
            }


            if ($shippingID != -1) {
                if ($shippingID == 0) {
                    foreach ($komplet_data as $data) {
                        if ($data['prac'] == $zasilkovna_shipping) {

                            if (isset($data['rozmery'])) {
                                $rozmery = $data['rozmery'];
                            }
                            if ($service['deklarace'] ?? 0 == 1) {
                                $deklarace = 'ano';
                                if ($ToretZasilkovna->Customs->is_declaration_enabled($data['slug'], $order->get_shipping_country())) {
                                    $deklarace = 'ne';
                                    $declaration_exception = true;
                                }
                            }
                            if (isset($data['api'])) {
                                $apiAllowed = $data['api'];
                            }
                        }
                    }
                } else {
                    $service = $ToretZasilkovna->Helper->GetServiceBySlug($service);
                    if (isset($service['rozmery'])) {
                        $rozmery = $service['rozmery'];
                    }
                    if ($service['deklarace'] ?? 0 == 1) {
                        $deklarace = 'ano';
                        if ($ToretZasilkovna->Customs->is_declaration_enabled($service['slug'], $order->get_shipping_country())) {
                            $deklarace = 'ne';
                            $declaration_exception = true;
                        }

                    }
                    if (isset($service['api'])) {
                        $apiAllowed = $service['api'];
                    }
                }

                if ($rozmery > 0) {
                    $rozmery_data = (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dimension', true) ? Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dimension', true) : '');
                }
            } else {
                $service = $ToretZasilkovna->Helper->GetServiceBySlug($service);
                if (isset($service['rozmery'])) {
                    $rozmery = $service['rozmery'];
                }
                if ($service['deklarace'] ?? 0 == 1) {
                    $deklarace = 'ano';
                    if ($ToretZasilkovna->Customs->is_declaration_enabled($service['slug'], $order->get_shipping_country())) {
                        $deklarace = 'ne';
                        $declaration_exception = true;
                    }

                }
                if (isset($service['api'])) {
                    $apiAllowed = $service['api'];
                }
            }
            if ($rozmery > 0) {
                $rozmery_data = (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dimension', true) ? Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dimension', true) : '');
            }
        }

        if (tzas_is_native_method($service)) {
            $apiAllowed = '1';
        }

        return [
            'rozmery' => $rozmery,
            'shippingID' => $shippingID,
            'rozmery_data' => $rozmery_data,
            'deklarace' => $deklarace,
            'apiAllowed' => $apiAllowed,
            'declaration_exception' => $declaration_exception ?? false
        ];
    }

    function get_action_current_location($order, $is_metabox = true)
    {
        $reserved = array(
            'zasilkovna_ticket_id',
            'zasilkovna_order_id',
            'package_count',
            'zasilkovna_ticket_id_assistent',
            'zasilkovna_id_objednavky_assistent',
            'packetka_cancel_finished',
            'zasilkovna_id_objednavky',
            'packeta_bulk_finished',
            'packetka_delete_finished',
            'packetka_cancel_finished',
            'is_claim',
        );

        $url_args = array();

        foreach ($_GET as $key => $item) {
            if (in_array($key, $reserved)) {
                continue;
            }
            $url_args[$key] = $item;
        }

        if ($is_metabox) {
            if (Toret_HPOS_Compatibility::is_wc_hpos_enabled()) {
                $location = add_query_arg($url_args, $order->get_edit_order_url());
            } else {
                $location = add_query_arg($url_args, admin_url() . 'post.php');
            }
        } else {
            if (Toret_HPOS_Compatibility::is_wc_hpos_enabled()) {
                $location = add_query_arg($url_args, wc_get_current_admin_url());
            } else {
                $location = add_query_arg($url_args, admin_url() . 'edit.php');
            }
        }
        return $location;
    }

    function draw_empty_package_actions($order_id, $ToretZasilkovna, $is_metabox)
    {
        if (!$is_metabox) {
            $button_class = 'torlib-column-action-button';
        } else {
            $button_class = 'torlib-metabox-action-button';
        }

        $id_zasilky = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky');
        $field = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode');
        $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy');

        $location = $this->get_action_current_location(wc_get_order($order_id), $is_metabox);

        if (empty($field) || apply_filters('zasilkovna_allow_multiple_submissions', false)) {

            $vaha = ToretZasilkovnaDimensionHelper::get_zasilkovna_weight($order_id, true);

            $visibility_data = $ToretZasilkovna->Helper->get_actions_visibility_data($order_id);
            $apiAllowed = $visibility_data['apiAllowed'];
            $rozmery_data = $visibility_data['rozmery_data'];
            $deklarace = $visibility_data['deklarace'];
            $rozmery = $visibility_data['rozmery'];
            $declaration_exception = $visibility_data['declaration_exception'];

            $declaration_ok = $ToretZasilkovna->Customs->is_declaration_ok($order_id);

            $dopravce = $ToretZasilkovna->Customs->get_order_dopravce($order_id);

            if ($deklarace && $is_metabox && TORET_ZASILKOVNA_ENABLE_CUSTOMS) {

                echo '<div class="zasilkovna-metabox-customs"><span><b>' . __('Customs declaration', WOOZASILKOVNASLUG) . '</b></span>';

                $invoice_number = $ToretZasilkovna->Customs->is_invoice_number_available($order_id);
                $invoice_date = $ToretZasilkovna->Customs->is_invoice_date_available($order_id);

                echo '<div class="options_group">' .
                    woocommerce_wp_text_input(array(
                        'id' => 'tzas-invoice-number',
                        'value' => $invoice_number,
                        'label' => __('Invoice number:', WOOZASILKOVNASLUG),
                    )) . '
                    </div>';

                echo '<div class="options_group">' .
                    woocommerce_wp_text_input(array(
                        'id' => 'tzas-invoice-date',
                        'value' => $invoice_date,
                        'label' => __('Invoice issue date (Y-m-d):', WOOZASILKOVNASLUG),
                    )) . '
                    </div>';

                if ((TORET_ZASILKOVNA_CUSTOMS_CARRIER_EAD[$dopravce] ?? "") == 'own') {
                    $load_invoice = '';
                    $types = array(
                        'invoice' => __('Invoice', WOOZASILKOVNASLUG),
                        'ead' => __('EAD', WOOZASILKOVNASLUG)
                    );
                    foreach ($types as $type => $title) {
                        $load_invoice .= '<div class="zasilkovna-file-wrap">';
                        $load_invoice .= '<label for="tzas_' . $type . '_file">' . $title . __(' file:', WOOZASILKOVNASLUG) . '</label>';
                        $load_invoice .= '<div class="zasilkovna-file-input-wrap">';
                        $load_invoice .= '<input readonly type="text" name="tzas_' . $type . '_file" id="tzas_' . $type . '_file" value="' . Toret_HPOS_Compatibility::get_order_meta($order_id, 'tzas_' . $type . '_file', true) . '">';
                        $load_invoice .= '<input type="hidden" name="tzas_' . $type . '_file_id" id="tzas_' . $type . '_file_id" value="' . Toret_HPOS_Compatibility::get_order_meta($order_id, 'tzas_' . $type . '_file_id', true) . '">';
                        $load_invoice .= '<button class="button choose_storage_file " data-stat="' . $type . '"><span class="dashicons dashicons-admin-page" title="' . __('Select file', 'zasilkovna') . '"></span></button>';
                        if (Toret_HPOS_Compatibility::get_order_meta($order_id, 'tzas_' . $type . '_file_id', true) != '') {
                            $load_invoice .= '<button class="button zasilkovna-upload-storage" data-fileid="' . Toret_HPOS_Compatibility::get_order_meta($order_id, 'tzas_' . $type . '_file_id', true) . '" data-type="' . 'invoice' . '" data-orderid="' . $order_id . '"><span class="dashicons dashicons-external" title="' . __('Upload to Packetka', 'zasilkovna') . '"></span></button>';
                        }
                        $load_invoice .= '</div>';
                        $load_invoice .= '</div>';
                    }

                    echo $load_invoice;
                }


                if (!$declaration_ok) {
                    echo $ToretZasilkovna->Customs->admin_popup_dialog($order_id, true);
                }
                echo '</div>';

            }

            $html_ok = '';

            $zasilkovna_option = get_option('zasilkovna_option', array());

            $order = wc_get_order($order_id);
            $max_dim = Dimensions::get_order_max_dimension($order, false, true);
            $max_dim_sum = Dimensions::get_order_max_sides_sum($order, false, true);
            $multipackage_data = $ToretZasilkovna->Helper->get_multipackage_data($zasilkovna_option, $vaha, $max_dim, $max_dim_sum);

            if ($multipackage_data['enabled']) {
                $qty = $multipackage_data['qty'];

                if ($declaration_ok && $apiAllowed == '1') {
                    if ($is_metabox) {
                        $html_ok = '<a href="' . $location . '&zasilkovna_id_objednavky=' . $order_id . '&package_count=' . $qty . '" class="button zasilkovna_id_objednavky ' . $button_class . '"><span class="dashicons dashicons-external" title="' . __('Send to Packeta', 'zasilkovna') . '"></span>' . __('Send to Packeta', 'zasilkovna') . '</a>';
                    } else {
                        $html_ok = '<a href="' . $location . '&zasilkovna_id_objednavky=' . $order_id . '&package_count=' . $qty . '" class="button zasilkovna_id_objednavky ' . $button_class . '"><span class="dashicons dashicons-external" title="' . __('Send to Packeta', 'zasilkovna') . '"></span></a>';
                    }
                }

            } else {

                if ($declaration_ok && $apiAllowed == '1') {
                    if ($is_metabox) {
                        $html_ok = '<a href="' . $location . '&zasilkovna_id_objednavky=' . $order_id . '&package_count=1" class="zasilkovna_id_objednavky button ' . $button_class . '"><span class="dashicons dashicons-external" title="' . __('Send to Packeta', 'zasilkovna') . '"></span>' . __('Send to Packeta', 'zasilkovna') . '</a>';
                    } else {
                        $html_ok = '<a href="' . $location . '&zasilkovna_id_objednavky=' . $order_id . '&package_count=1" class="button zasilkovna_id_objednavky ' . $button_class . '"><span class="dashicons dashicons-external" title="' . __('Send to Packeta', 'zasilkovna') . '"></span></a>';
                    }
                }
            }

            if ($is_metabox) {
                $html_ko = '<a class="button toret-add-info toret-add-info' . $order_id . ' ' . $button_class . '" data-id="' . $order_id . '"><span class="dashicons dashicons-warning" title="' . __('Add information', 'zasilkovna') . '"></span>' . __('Add information', 'zasilkovna') . '</a>
                                    <div class="toret-info-' . $order_id . ' toret-popup-order" style="display:none;">
                                        <div class="toret-popup-inner toret-popup-print-inner">
                                            <h1 class="toret-popup-title">' . __('Complete the information', 'zasilkovna') . '</h1>';
            } else {
                $html_ko = '<a class="button toret-add-info toret-add-info' . $order_id . ' ' . $button_class . '" data-id="' . $order_id . '"><span class="dashicons dashicons-warning" title="' . __('Add information', 'zasilkovna') . '"></span></a>
                                    <div class="toret-info-' . $order_id . ' toret-popup toret-popup-add-info" style="display:none;">
                                        <div class="toret-popup-inner toret-popup-print-inner">
                                            <h2 class="toret-popup-title">' . __('Complete the information', 'zasilkovna') . '</h2>';
            }


            if ($rozmery > 0) {
                if ($rozmery_data == '') {
                    $html_ko .= '<label for="toret-width" class="toret-popup-label">' . __('Width:', 'zasilkovna') . '</label><input type="number" min="10" step="1" value="" id="toret-width" class="zasilkovna-order-input toret-input-width toret-input-width' . $order_id . '" placeholder="mm"/>';
                    $html_ko .= '<label for="toret-height" class="toret-popup-label">' . __('Height:', 'zasilkovna') . '</label><input type="number" min="10" step="1" value="" id="toret-height" class="zasilkovna-order-input toret-input-height toret-input-height' . $order_id . '" placeholder="mm"/>';
                    $html_ko .= '<label for="toret-lenght" class="toret-popup-label">' . __('Length:', 'zasilkovna') . '</label><input type="number" min="10" step="1" value="" id="toret-lenght" class="zasilkovna-order-input toret-input-lenght toret-input-lenght' . $order_id . '" placeholder="mm"/>';
                }
            }

            if ($vaha <= 0) {
                if ($is_metabox) {
                    $html_ko .= '<label for="toret-weight" class="toret-popup-label">' . __('Weight:', 'zasilkovna') . '</label><input type="number" step="any" min="0.001" value="" id="toret-weight" class="zasilkovna-order-input toret-input-weight toret-input-weight' . $order_id . '" placeholder="' . get_option('woocommerce_weight_unit') . '"/>';
                } else {
                    $html_ko .= '<label class="toret-popup-label">' . __('Weight:', 'zasilkovna') . '</label><input name="weight" type="number" step="any" min="0.001" value="" class="zasilkovna-order-input toret-input-weight toret-input-weight' . $order_id . '" placeholder="' . get_option('woocommerce_weight_unit') . '"/>';
                }
            }

            $order = wc_get_order($order_id);
            $total_price = $order->get_total();

            if ($total_price == 0) {
                $custom_price_value = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_total', true);
                if ($custom_price_value != '') {
                    $total_price = $custom_price_value;
                }

                if ($is_metabox) {
                    $html_ko .= '<label for="toret-total" class="toret-popup-label">' . __('Order total:', 'zasilkovna') . '</label><input type="number" step="any" min="0.001" value="" id="toret-total" class="zasilkovna-order-input toret-input-weight toret-input-total' . $order_id . '" placeholder="' . get_woocommerce_currency_symbol() . '"/>';
                } else {
                    $html_ko .= '<label for="toret-total" class="toret-popup-label">' . __('Order total:', 'zasilkovna') . '</label><input name="weight" type="number" step="any" min="0.001" value="" id="toret-total" class="zasilkovna-order-input toret-input-weight toret-input-total' . $order_id . '" placeholder="' . get_woocommerce_currency_symbol() . '"/>';
                }
            }


            if ($is_metabox) {

                $html_ko .= '<div>';

            } else {
                $html_ko .= '<div class="toret-popup-buttons">';

            }
            $html_ko .= '<div class="toret-popup-print-buttons">';
            $html_ko .= '<button class="tzas-ulozit toret-popup-close" data-id="' . $order_id . '">' . __('Close', 'zasilkovna') . '</button>';
            $html_ko .= '<button class="tzas-ulozit toret-popup-save" data-id="' . $order_id . '">' . __('Save', 'zasilkovna') . '</button>';
            $html_ko .= '</div>';
            $html_ko .= '</div>';
            $html_ko .= '</div>';
            $html_ko .= '</div>';

            $html_deklarace = '<p>' . __('Customs declaration is required. It must be added manually.', 'zasilkovna') . '</p>';

            if ($deklarace == 'ano') {
                echo $html_deklarace;
            } else {

                if ($declaration_exception) {
                    echo $html_deklarace . '<br>';
                    if (!$declaration_ok) {
                        echo $ToretZasilkovna->Customs->admin_popup_dialog($order_id);
                    }
                }

                $disabled = $ToretZasilkovna->Helper->DisableByDim(str_replace('zasilkovna>', '', $zasilkovna_shipping), $order->get_shipping_country(), false, $order);

                if ($disabled && $id_zasilky == '') {
                    $oversized = '<span class="tzas-oversized-column dashicons dashicons-warning" title="' . __('This package is probably oversized.', 'zasilkovna') . '"></span>';
                    $html_ko .= $oversized;
                    $html_ok .= $oversized;
                }

                $rozmery = apply_filters('tzas_order_check_dimensions', $rozmery, $order_id, $zasilkovna_shipping);
                $vaha = apply_filters('tzas_order_check_weight', $vaha, $order_id, $zasilkovna_shipping);
                $total_price = apply_filters('tzas_order_check_total_price', $total_price, $order_id, $zasilkovna_shipping);

                if ($rozmery > 0) {
                    if ($rozmery_data == '') {
                        echo $html_ko;
                    } elseif ($vaha <= 0) {
                        echo $html_ko;
                    } else {
                        echo $html_ok;
                    }
                } else {
                    if ($total_price == 0) {
                        echo $html_ko;
                    } elseif ($vaha <= 0) {
                        echo $html_ko;
                    } else {
                        echo $html_ok;
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    static function getThresholdLabels(): array
    {
        return array(
            'weight' => __('Weight threshold for division in kg', WOOZASILKOVNASLUG),
            'longest' => __('Longest side threshold for division in m', WOOZASILKOVNASLUG),
            'sum' => __('Sum of all three sides threshold for division in m', WOOZASILKOVNASLUG)
        );
    }

}