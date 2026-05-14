<?php

namespace ToretZasilkovna\Toret\Library;


use Exception;
use WC_Countries;
use WC_Data_Store;
use WC_Shipping_Zone;
use WC_Shipping_Zone_Data_Store;

class Shipping
{
    /**
     * @return string
     */
    static function getCartShippingMethod(): string
    {
        $shipping_method = '';
        if (isset(WC()->session)) {
            if (isset(WC()->session->chosen_shipping_methods[0])) {
                $shipping_method = WC()->session->chosen_shipping_methods[0];
            }
        }

        return $shipping_method;
    }

    /**
     * @param $order
     * @return false|mixed|string
     */
    static function getOrderShippingMethodId($order)
    {
        $methods = $order->get_shipping_methods();
        $shipping_method = @array_shift($methods);
        if (empty($shipping_method)) {
            return false;
        }
        $shipping_method_id = $shipping_method['method_id'];

        if (empty($shipping_method_id)) {
            return false;
        }

        return $shipping_method_id;
    }

    /**
     * @return array
     */
    static function getAllShippingZones(): array
    {
        try {
            $data_store = WC_Data_Store::load('shipping-zone');

            /**
             * @var WC_Shipping_Zone_Data_Store $zones
             */
            $raw_zones = $data_store->get_zones();
            foreach ($raw_zones as $raw_zone) {
                $zones[] = new WC_Shipping_Zone($raw_zone);
            }
            $zones[] = new WC_Shipping_Zone(0);

        } catch (Exception $e) {
            return [];
        }

        return $zones;
    }

    /**
     * @param $continent_code
     * @return array
     */
    static function getCountriesByContinent($continent_code): array
    {
        $wc_countries = new WC_Countries();
        $countries = $wc_countries->get_countries();
        $continents = $wc_countries->get_continents();

        if (!isset($continents[$continent_code])) {
            return [];
        }

        $country_list = [];

        foreach ($continents[$continent_code]['countries'] as $country_code) {
            if (isset($countries[$country_code])) {
                $country_list[$country_code] = $countries[$country_code];
            }
        }

        return $country_list;
    }

    /**
     * @return array
     */
    static function getEuVatCountries(): array
    {
        $namedCountries = [];

        $euVatCountries = WC()->countries->get_european_union_countries('eu_vat');

        $countries = WC()->countries->get_countries();
        foreach ($euVatCountries as $euVatCountry) {
            $namedCountries[$euVatCountry] = $countries[$euVatCountry];
        }

        return $namedCountries;
    }
}