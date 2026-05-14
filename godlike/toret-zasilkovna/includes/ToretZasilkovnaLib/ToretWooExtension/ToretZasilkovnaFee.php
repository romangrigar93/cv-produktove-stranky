<?php

use ToretZasilkovna\Toret\Library\Taxes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * @package   Toret Zasilkovna
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      https://toret.cz
 * @copyright 2021 toret.cz
 */
class ToretZasilkovnaFee
{
    private $shipping_total;
    private $zasilkovna_prices;
    private $country;
    private $doprava_name;
    private $ZasilkovnaHelper;
    private $shipping_method;


    /**
     * Zasilkovna Fee constructor.
     */
    public function __construct()
    {
        $ToretZasilkovna = ToretZasilkovnaLib();
        $this->ZasilkovnaHelper = $ToretZasilkovna->Helper;
        $this->shipping_total = WC()->shipping->shipping_total;
        $this->zasilkovna_prices = get_option('zasilkovna_prices', array());
        $this->country = toret_get_customer_country();
        $this->doprava_name = tzas_get_service_from_cart();
        $this->shipping_method = tzas_get_shipping_from_cart();
        $this->calculate_fee();
    }

    /**
     * Calculate fee
     */
    public function calculate_fee(): void
    {
        if (($this->ZasilkovnaHelper::get_current_gateway()) && ($this->ZasilkovnaHelper::get_current_gateway_settings())) {

            $payment_method = WC()->session->get('chosen_payment_method');
            if (in_array($payment_method, TORET_ZASILKOVNA_COD_METHODS)) {

                $zasilkovna_creditCardPayment = WC()->session->get('zasilkovna_creditCardPayment');

                $zasilkovna_option = get_option('zasilkovna_option');
                $cod_check = $zasilkovna_option['cod_point_check'] ?? '';
                if ($cod_check == 'ok') {
                    $service = tzas_get_service_from_cart();
                    if ($zasilkovna_creditCardPayment == 'false' && tzas_is_native_method($service)) {
                        return;
                    }
                }

                if (tzas_is_zasilkovna_shipping($this->shipping_method)) {

                    $feeData = $this->get_fee_value();

                    $fee = $feeData['fee'];
                    $fixed = $feeData['fixed'];

                    if ($fee != -1) {

                        $fee = $this->ZasilkovnaHelper::set_fee_by_dobirka_free_shipping($fee, $this->shipping_total);

                        if(!$fixed) {
                            $fee = $this->ZasilkovnaHelper::currency_compatibility($fee);
                        }

                        if ($fee != 0) {

                            $plugin_cod_tax_option = $plugin_options['cod_tax_class'] ?? 'inherit';
                            $vatIncluded = (!empty($zasilkovna_option['price_with_vat']) && $zasilkovna_option['price_with_vat'] == 'ok');
                            $FeeData = (new Taxes())->get_fee_tax_data_for_cart((float)$fee, true, $vatIncluded, $plugin_cod_tax_option);

                            $dobirka_label = apply_filters('zasilkovna_dobirka_label', __('Cash on delivery fee', WOOZASILKOVNASLUG));
                            $tax_class = apply_filters('zasilkovna_taxclass_dobirka', $FeeData['tax_class_applied']);
                            $fees_api = WC()->cart->fees_api();

                            if (is_array($tax_class)) {
                                $tax_class = $tax_class['tax_class'];
                            }

                            $fees_api->add_fee(
                                array(
                                    'id' => 'dobirka',
                                    'name' => $dobirka_label,
                                    'amount' => $FeeData['fee_excl_tax'],
                                    'taxable' => true,
                                    'tax_class' => (string)$tax_class,
                                )
                            );
                        }
                    }
                }
            }
            if (isset($this->shipping_method)) {
                if (tzas_is_zasilkovna_shipping($this->shipping_method)) {
                    $this->set_insurance();
                }
            }
        }
    }

    /**
     * check price vat settings
     */
    private function zasilkovna_fee_check_vat(float $fee, $cod = false): array
    {
        $return = array(
            'fee' => $fee,
            'tax_class' => ''
        );

        $plugin_options = get_option('zasilkovna_option');
        $plugin_vat_option = $plugin_options['price_with_vat'] ?? "";
        $plugin_cod_tax_option = $plugin_options['cod_tax_class'] ?? 'inherit';

        if (get_option('woocommerce_calc_taxes') === 'yes') {

            if ($cod) {
                $tax_class = $plugin_cod_tax_option;
            } else {
                $tax_class = 'NOTUSED';
            }

            if ($tax_class == 'inherit') {
                $tax_class = 'NOTUSED';
            }

            $tax_class = apply_filters('zasilkovna_taxclass_dobirka', $tax_class);

            $_tax = new WC_Tax();
            $items = WC()->cart->get_cart();
            $ProductTaxPercent = 0;
            $ProductTaxPercentArray = array();

            $return['tax_class'] = $tax_class;

            if ($tax_class == 'inherit' || $tax_class == 'NOTUSED') {

                foreach ($items as $item) {
                    $_product = wc_get_product($item['variation_id'] ?: $item['product_id']);
                    $array = $_tax->get_rates($_product->get_tax_class());
                    $ProductTax = reset($array);
                    $ProductTaxPercentArray[$_product->get_tax_class()] = $ProductTax['rate'] ?? 0;
                }

                $ProductTaxPercent = max($ProductTaxPercentArray);

                foreach ($ProductTaxPercentArray as $key => $ptpa) {
                    if ($ptpa == $ProductTaxPercent) {
                        $return['tax_class'] = $key;
                    }
                }

            } else {

                $return['tax_class'] = $tax_class;
                if (is_array($tax_class)) {
                    $tax_class = $tax_class['tax_class'];
                }
                $TaxRate = $_tax->get_rates_for_tax_class($tax_class);
                $ProductTaxPercentPart = reset($TaxRate);
                if ($ProductTaxPercentPart) {
                    $ProductTaxPercent = (float)$ProductTaxPercentPart->tax_rate;
                }

            }

            if ($plugin_vat_option == 'ok') {
                $fee = ($fee / (100 + $ProductTaxPercent) * 100);
                $return['fee'] = $fee;
            }

        }

        return $return;
    }

    /**
     * Calculate fee
     */
    public function get_fee_value()
    {
        global $woocommerce;

        $fee = '';
        $fixed = false;

        $zasilkovna_option = get_option('zasilkovna_option');
        $zasilkovna_prices = get_option('zasilkovna_prices', array());

        $amount = $woocommerce->cart->subtotal;

        $service = $this->doprava_name;

        if (tzas_is_native_method($service)) {
            $native_type = tzas_get_native_slug_from_service($service);
            $fee_price_type = tzas_get_cod_fee_type($zasilkovna_option, $zasilkovna_prices, 'zasilkovna' . $native_type . '-' . strtolower($this->country));
        } else {
            $fee_price_type = tzas_get_cod_fee_type($zasilkovna_option, $zasilkovna_prices, $service);
        }

        if ($fee_price_type == 'total') {

            if (tzas_is_native_method($service)) {
                $native_type = tzas_get_native_slug_from_service($service);
                $fee = $this->get_flat_rate_in_currency();
                if (empty($fee)) {
                    $fee = (!empty($zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($this->country) . '-dobirka']) ? $zasilkovna_prices['zasilkovna' . $native_type . '-' . strtolower($this->country) . '-dobirka'] : -1);
                } else {
                    $fixed = true;
                }

                if (!empty($zasilkovna_prices['zasilkovna' . $native_type . '-feeo-' . strtolower($this->country)]) &&
                    !empty($zasilkovna_prices['zasilkovna' . $native_type . '-feed-' . strtolower($this->country)]) &&
                    !empty($zasilkovna_prices['zasilkovna' . $native_type . '-cenafee-' . strtolower($this->country)])) {
                    foreach ($zasilkovna_prices['zasilkovna' . $native_type . '-feeo-' . strtolower($this->country)] as $key => $hmo) {
                        if (($hmo != '') || ($zasilkovna_prices['zasilkovna' . $native_type . '-feed-' . strtolower($this->country)][$key] != '') || ($zasilkovna_prices['zasilkovna' . $native_type . '-cenafee-' . strtolower($this->country)][$key] != '')) {
                            if ($amount >= $hmo && $amount <= $zasilkovna_prices['zasilkovna' . $native_type . '-feed-' . strtolower($this->country)][$key]) {
                                $fee = $zasilkovna_prices['zasilkovna' . $native_type . '-cenafee-' . strtolower($this->country)][$key];
                                $fixed = false;
                            }
                        }
                    }
                }
            } else {

                $fee = $this->get_flat_rate_in_currency();
                if (empty($fee)) {
                    $fee = (!empty($zasilkovna_prices[$service . '-dobirka']) ? $zasilkovna_prices[$service . '-dobirka'] : -1);
                } else {
                    $fixed = true;
                }


                if (!empty($zasilkovna_prices[$service . '-feeo-' . strtolower($this->country)]) &&
                    !empty($zasilkovna_prices[$service . '-feed-' . strtolower($this->country)]) &&
                    !empty($zasilkovna_prices[$service . '-cenafee-' . strtolower($this->country)])) {

                    foreach ($zasilkovna_prices[$service . '-feeo-' . strtolower($this->country)] as $key => $hmo) {
                        if (($hmo != '') || ($zasilkovna_prices[$service . '-feed-' . strtolower($this->country)][$key] != '') || ($zasilkovna_prices[$service . '-cenafee-' . strtolower($this->country)][$key] != '')) {
                            if ($amount >= $hmo && $amount <= $zasilkovna_prices[$service . '-feed-' . strtolower($this->country)][$key]) {
                                $fee = $zasilkovna_prices[$service . '-cenafee-' . strtolower($this->country)][$key];
                                $fixed = false;
                            }
                        }
                    }
                }
            }
        } else {
            if (tzas_is_native_method($service)) {
                $native_type = tzas_get_native_slug_from_service($service);
                $ToretZasilkovna = ToretZasilkovnaLib();
                foreach ($ToretZasilkovna->Helper->komplet_staty_kont() as $stat => $stateNazev) {
                    if (in_array(strtoupper($stat), $zasilkovna_option['povolene_staty'])) {
                        $aviable = $ToretZasilkovna->Helper->IsPacketaAviableAdmin($stat);
                        if ($aviable) {
                            if (toret_get_customer_country() == $stat) {
                                $fee = $this->get_flat_rate_in_currency();
                                if (empty($fee)) {
                                    $fee = $this->is_empty_price('zasilkovna' . $native_type . '-' . strtolower($stat) . '-dobirka');
                                } else {
                                    $fixed = true;
                                }
                            }
                        }
                    }
                }
            } else {
                $fee = $this->get_flat_rate_in_currency();
                if (empty($fee)) {
                    $fee = $this->is_empty_price($service . '-dobirka');
                } else {
                    $fixed = true;
                }
            }
        }

        if ($fee == -1) {
            $fee = -1;
            $fixed = false;
        } else {
            if ($fee == '') {
                $fee = -1;
                $fixed = false;
            }
        }

        return [
            'fee' => $fee,
            'fixed' => $fixed
        ];
    }

    function get_flat_rate_in_currency()
    {
        $currencies = ['CZK', 'EUR', 'USD', 'GBP', 'PLN', 'HUF', 'RON'];
        $currency = $this->get_current_currency();

        $zasilkovna_prices = get_option('zasilkovna_prices', []);

        $service = tzas_get_service_from_cart();

        if (tzas_is_native_method($service)) {
            $slug = 'zasilkovna' . '-' . strtolower(toret_get_customer_country());
        } else {
            $slug = $service;
        }

        if (($zasilkovna_prices[$slug . '-flr-cod-enabled'] ?? '') == 'ok' && in_array($currency, $currencies)) {
            $option = $slug . '-flr-cod-' . $currency;
            return $zasilkovna_prices[$option] ?? null;
        }

        return null;
    }

    private function get_current_currency()
    {
        global $WOOCS;
        if (!empty($WOOCS))
            return $WOOCS->current_currency;
        else {
            return get_woocommerce_currency();
        }

    }

    /**
     * Ïs empty
     */
    public function is_empty_price(string $option_name): float
    {
        if (!empty($this->zasilkovna_prices[$option_name])) {
            $fee = $this->zasilkovna_prices[$option_name];
        } else {
            $fee = -1;
        }

        return $fee;
    }

    /**
     * Set insurance
     */
    public function set_insurance(): void
    {
        $pojisteni_label = apply_filters('zasilkovna_pojisteni_label', __('Consignment insurance', WOOZASILKOVNASLUG));
        if (!empty($this->zasilkovna_prices[$this->doprava_name[1] . '-pojisteni'])) {
            $pojisteni = $this->zasilkovna_prices[$this->doprava_name[1] . '-pojisteni'];
            $pojisteni = $this->ZasilkovnaHelper::currency_compatibility($pojisteni);
            WC()->cart->add_fee($pojisteni_label, $pojisteni, true);
        }

    }
}