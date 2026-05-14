<?php

namespace ToretZasilkovna\Toret\Library;

class ExchangeRates
{
    public static function currency_compatibility($cost)
    {
        if (function_exists('x_currency_exchange')) {

            $filtered_cost = x_currency_exchange($cost);
            if (!empty($filtered_cost)) {
                $cost = $filtered_cost;
            }

        }else if (function_exists('wmc_get_price')) {

            $filtered_cost = wmc_get_price($cost);
            if (!empty($filtered_cost)) {
                $cost = $filtered_cost;
            }

        } else if (class_exists('WCML_Exchange_Rates')) {

            $filtered_fee = apply_filters('wcml_raw_price_amount', $cost);
            if (!empty($filtered_fee)) {
                $cost = $filtered_fee;
            }

        }  else if (in_array('currency-switcher-for-woocommerce/wc-currency-switcher.php', apply_filters('active_plugins', get_option('active_plugins')))) {

            if (class_exists('WCCS')) {
                $coversion_rate = (new WCCS())->wccs_get_currency_rate();
                $decimals = (new WCCS())->wccs_get_currency_decimals();

                if ($coversion_rate) {
                    $cost = round($cost * $coversion_rate, $decimals);
                }
            }

        } else if (function_exists('wcj_get_currency_exchange_rate') && function_exists('wcj_get_option') && function_exists('wcj_get_current_currency_code')) {

            $default_currency_number = wcj_get_option('wcj_multicurrency_default_currency', 1);
            $base_currency = wcj_get_option('wcj_multicurrency_currency_' . $default_currency_number, apply_filters('woocommerce_currency', get_option('woocommerce_currency')));
            $current_currency = wcj_get_current_currency_code('multicurrency');

            if ($base_currency !== $current_currency) {
                $currency_exchange_rate = wcj_get_currency_exchange_rate('multicurrency', $current_currency);
                $cost *= $currency_exchange_rate;
            }

        } else {

            $filtered_fee = apply_filters('woocs_exchange_value', $cost);
            if (!empty($filtered_fee)) {
                $cost = $filtered_fee;
            }

            if (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) {
                $from_currency = get_option('woocommerce_currency');
                $to_currency = get_woocommerce_currency();
                $cost = apply_filters('wc_aelia_cs_convert', $cost, $from_currency, $to_currency);
            }
        }

        return $cost;
    }
}