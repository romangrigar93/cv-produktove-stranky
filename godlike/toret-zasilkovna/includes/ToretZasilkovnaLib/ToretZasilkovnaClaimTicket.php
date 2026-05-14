<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class ToretZasilkovnaClaimTicket
{

    /**
     *send ticket
     */
    public static function send_ticket(int $order_id): void
    {

        $barcode = explode(';', Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_assistent', true));
        $weights = explode(';', Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_weights', true));

        $package_count = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_package_count', true);

        $ToretZasilkovna = ToretZasilkovnaLib();

        if (empty($barcode) || $barcode[0] == '') {

            Toret_HPOS_Compatibility::delete_order_meta($order_id, '_zasilkovna_odeslano_assistent');
            Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_id_zasilky_assistent');
            Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_weights_assistent');
            Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcode_assistent');
            Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_barcodeText_assistent');
            Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_package_count_assistent');
            Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_is_multipackage_assistent');
            Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_failText_assistent');

            for ($i = 1; $i < $package_count + 1; $i++) {

                /*zasilkovna_log( array(
                    'order_id' => $order_id,
                    'log'      => __( 'Order ID: ' . $order_id, 'zasilkovna' ),
                    'context'  => __( 'Create shipment', 'zasilkovna' )
                ) );*/

                $zasilkovna_option = get_option('zasilkovna_option', []);
                $zasilkovna_id = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_pobocky', true);

                if (!empty($zasilkovna_id)) {

                    /*zasilkovna_log( array(
                        'order_id' => $order_id,
                        'log'      => __( 'Store ID: ' . $zasilkovna_id, 'zasilkovna' ),
                        'context'  => __( 'Create shipment.', 'zasilkovna' ) . __( ' Package ', 'zasilkovna' ) . $i . '/' . $package_count
                    ) );*/

                    $order = wc_get_order($order_id);

                    if (self::check_ship_id($order) === true) {

                        if (!$ToretZasilkovna->Helper->check_email_address($order->get_billing_email())) {
                            zasilkovna_log(array(
                                'order_id' => $order_id,
                                'log' => __('Order ID: ' . $order_id, 'zasilkovna'),
                                'context' => __('Create shipment failed because the billing email address is not valid.', 'zasilkovna') . __(' Package ', 'zasilkovna') . $i . '/' . $package_count
                            ));
                            (new ToretZasilkovnaOutputs)->show_admin_notice_error(__('Create shipment failed because the billing email address is not valid.', 'zasilkovna'), $order_id, TORETZASILKOVNALOGSLUG, 'zasilkovna', true);
                            continue;
                        }

                        $zasilkovna_order = self::set_zasilkovna_order($order, $zasilkovna_option, $zasilkovna_id);
                        $apiPassword = $zasilkovna_option['api_password'];

                        /*zasilkovna_log( array(
                            'order_id' => $order_id,
                            'log'      => self::create_xml( $apiPassword, $zasilkovna_order, false, $i, $package_count, $weights ),
                            'context'  => 'Claim ' . __( 'Data sent', 'zasilkovna' )
                        ) );*/
                        $xml = self::create_xml($apiPassword, $zasilkovna_order, false, $i, $package_count, $weights);

                        $ch = curl_init('https://www.zasilkovna.cz/api/rest');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                        $result = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        /*zasilkovna_log( array(
                            'order_id' => $order_id,
                            'log'      => serialize( $result ),
                            'context'  => __( 'Packeta\'s answer.', 'zasilkovna' ) . ' Claim ' . __( ' Package ', 'zasilkovna' ) . $i . '/' . $package_count
                        ) );*/

                        $result = json_decode(json_encode(simplexml_load_string($result)), true);

                        $log_data = [
                            'url' => 'https://www.zasilkovna.cz/api/rest',
                            'method' => 'POST',
                            'httpcode' => $httpCode,
                            'body' => htmlspecialchars($xml),
                            'response' => (empty($response) ? '' : json_decode(json_encode(simplexml_load_string($result)), true))
                        ];

                        zasilkovna_log(array(
                            'order_id' => $order_id,
                            'log' => json_encode($log_data, JSON_UNESCAPED_UNICODE),
                            'context' => __('Packeta API call: ', 'zasilkovna') . ' ' . __('Package', 'zasilkovna') . ' ' . $i . '/' . $package_count,
                            'type' => 4,
                        ));

                        if ($result['status'] == 'ok') {
                            self::result_ok($order_id, $result, $i, $package_count, $weights);
                        } elseif ($result['status'] == 'fault') {
                            self::result_fault($order_id, $result, $order, $zasilkovna_option, $i, $package_count);
                        }

                        if ($package_count == 1) {
                            $zasilkovna_status_assistent = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_status_assistent');
                            $zasilkovna_status_assistent .= ($i == 1 ? '' : ';') . $result['status'];
                        } else {
                            $zasilkovna_status_assistent = $result['status'];
                        }

                        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_status_assistent', $zasilkovna_status_assistent);
                    }
                }
            }
        }
    }

    /**
     * Control if is shipping method on Zasilkovna methods
     */
    private static function check_ship_id($order): bool
    {
        $shipping = Toret_HPOS_Compatibility::get_order_meta($order->get_id(), 'zasilkovna_id_dopravy');
        $ship_ids = self::shipping_ids();
        if (in_array($shipping, $ship_ids)) {
            return true;
        }
        return false;
    }

    /**
     * Array of Zasilkovna ids
     */
    private static function shipping_ids()
    {
        $ToretZasilkovna = ToretZasilkovnaLib();

        return $ToretZasilkovna->Helper->set_order_shipping_ids();

    }

    /**
     * get order data
     */
    public static function set_zasilkovna_order($order, $zasilkovna_option, $zasilkovna_id): array
    {
        $country = self::set_order_country($order);
        $currency_price = self::convert_price($country, $order->get_total(), $order);

        $currency = $currency_price[1];

        $email = $order->get_billing_email();
        $phone = $order->get_billing_phone();

        $name = (empty($order->get_shipping_first_name()) ? $order->get_billing_first_name() : $order->get_shipping_first_name());
        $surname = (empty($order->get_shipping_last_name()) ? $order->get_billing_last_name() : $order->get_shipping_last_name());
        $street = $order->get_shipping_address_1();
        $house = $order->get_shipping_address_2();
        $town = $order->get_shipping_city();
        $zip = $order->get_shipping_postcode();

        $price = $currency_price[0];
        $price_value = apply_filters('zasilkovna_ticket_value', $price, $order, $zasilkovna_option, $zasilkovna_id);
        $eshop = $zasilkovna_option['nazev_eshopu'];

        $order_number = $order->get_order_number();
        $zasilkovna_order_number = apply_filters('zasilkovna_order_number', $order_number, $order);

        $zasilkovna_order = array();
        $zasilkovna_order['number'] = (string)$zasilkovna_order_number;
        $zasilkovna_order['name'] = (string)$name;
        $zasilkovna_order['surname'] = (string)$surname;
        $zasilkovna_order['email'] = (string)$email;
        $zasilkovna_order['phone'] = (string)$phone;
        $zasilkovna_order['street'] = (string)$street;
        $zasilkovna_order['houseNumber'] = (string)$house;
        $zasilkovna_order['city'] = (string)$town;
        $zasilkovna_order['zip'] = (string)$zip;

        $zasilkovna_order['currency'] = (string)$currency;

        $s_method = $order->get_payment_method();

        $zasilkovna_order['cod'] = zasilkovna_get_cod_value($s_method, $price, $country, $order, $currency);
        $zasilkovna_order['addressId'] = (int)$zasilkovna_id;
        $zasilkovna_order['value'] = (float)$price_value;

        $zasilkovna_order['eshop'] = (string)$eshop;

        return apply_filters('zasilkovna_order_data', $zasilkovna_order, $order, true);
    }

    /**
     * get order country
     */
    public static function set_order_country($order): string
    {
        $country = $order->get_shipping_country();
        if (empty($country))
            $country = $order->get_billing_country();
        if (empty($country))
            $country = toret_get_customer_country();
        return $country;
    }

    /**
     * create XML
     */
    private static function create_xml(string $apiPassword, array $zasilkovna_order, string $label, $current_package, $package_count, $weights): string
    {
        $value = (float)$zasilkovna_order['value'] / (float)$package_count;
        $weight = $weights[$current_package - 1];

        $xml = '
                        <createPacketClaim>
                            <apiPassword>' . $apiPassword . '</apiPassword>
                            <ClaimAttributes>
                                <number>' . $zasilkovna_order['number'] . '</number>
                                <name>' . htmlspecialchars($zasilkovna_order['name']) . '</name>
                                <surname>' . htmlspecialchars($zasilkovna_order['surname']) . '</surname>
                                <email>' . $zasilkovna_order['email'] . '</email>
                                <phone>' . $zasilkovna_order['phone'] . '</phone>
                                <currency>' . $zasilkovna_order['currency'] . '</currency>
                                <value>' . $value . '</value>
                                <eshop>' . htmlspecialchars($zasilkovna_order['eshop']) . '</eshop>
                                <sendLabelToEmail>' . $label . '</sendLabelToEmail>
                            </ClaimAttributes>
                        </createPacketClaim>
                    ';

        return apply_filters('zasilkovna_claim_ticket_xml', $xml, $apiPassword, $zasilkovna_order, $label);
    }

    /**
     * save OK result
     */
    private static function result_ok(int $order_id, array $result, $package_order = 1, $package_count = 1, $weights = array()): void
    {

        $_zasilkovna_odeslano_assistent = Toret_HPOS_Compatibility::get_order_meta($order_id, '_zasilkovna_odeslano_assistent', true);
        $_zasilkovna_odeslano_assistent .= ($package_order == 1 ? '' : ';') . 'ok';
        Toret_HPOS_Compatibility::update_order_meta($order_id, '_zasilkovna_odeslano_assistent', $_zasilkovna_odeslano_assistent);

        $zasilkovna_id_zasilky_assistent = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_assistent', true);
        $zasilkovna_id_zasilky_assistent .= ($package_order == 1 ? '' : ';') . $result['result']['id'];
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_zasilky_assistent', $zasilkovna_id_zasilky_assistent);

        $zasilkovna_barcode_assistent = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode_assistent', true);
        $zasilkovna_barcode_assistent .= ($package_order == 1 ? '' : ';') . $result['result']['barcode'];
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_barcode_assistent', $zasilkovna_barcode_assistent);

        $zasilkovna_barcodeText_assistent = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcodeText_assistent', true);
        $zasilkovna_barcodeText_assistent .= ($package_order == 1 ? '' : ';') . $result['result']['barcodeText'];
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_barcodeText_assistent', $zasilkovna_barcodeText_assistent);

        $zasilkovna_weights_assistent = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_weights_assistent', true);
        $zasilkovna_weights_assistent .= ($package_order == 1 ? '' : ';') . $weights[$package_order - 1] ?? '0';
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_weights_assistent', $zasilkovna_weights_assistent);

        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_package_count_assistent', $package_count);
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_is_multipackage_assistent', ($package_count > 1 ? '1' : '0'));

        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_failText_assistent');
    }

    /**
     * save fault result
     */
    private static function result_fault(int $order_id, array $result, object $order, array $zasilkovna_option, $package_order = 1, $package_count = 1): void
    {
        if (!empty($result['detail'])) {

            if (!class_exists('WC_Email')) {
                WC()->mailer();
            }

            if (!empty($result['detail']['attributes']['fault']['fault'])) {

                $zasilkovna_failText = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_failText_assistant', true);
                $zasilkovna_failText .= ($package_order == 1 ? '' : ';') . $result['detail']['attributes']['fault']['fault'];
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_failText_assistant', $zasilkovna_failText);

                $note = __('Shipment has not been saved with error:', 'zasilkovna') . ' ' . $result['detail']['attributes']['fault']['fault'];

                $order->add_order_note($note);

                if (!empty($zasilkovna_option['error_email'])) {

                    require_once(WOOZASILKOVNADIR . 'includes/ToretZasilkovnaLib/ToretWooExtension/ToretZasilkovnaWcAdminErrorInfo.php');
                    $send = new ToretZasilkovnaWcAdminErrorInfo();
                    $send->trigger($order_id, $note);

                }

            } elseif (!empty($result['detail']['attributes']['fault'][0]['fault'])) {

                $zasilkovna_failText = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_failText_assistant', true);
                $zasilkovna_failText .= ($package_order == 1 ? '' : ';') . $result['detail']['attributes']['fault'][0]['fault'];
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_failText_assistant', $zasilkovna_failText);

                $note = __('Shipment has not been saved with error:', 'zasilkovna') . ' ' . $result['detail']['attributes']['fault'][0]['fault'];

                $order->add_order_note($note);

                if (!empty($zasilkovna_option['error_email'])) {

                    require_once(WOOZASILKOVNADIR . 'includes/ToretZasilkovnaLib/ToretWooExtension/ToretZasilkovnaWcAdminErrorInfo.php');
                    $send = new ToretZasilkovnaWcAdminErrorInfo();
                    $send->trigger($order_id, $note);

                }
            }
        }
    }

    /**
     * Convert
     */
    public static function convert_price($country, $price, $order)
    {
        $ToretZasilkovna = ToretZasilkovnaLib();

        $currency = $order->get_currency();
        $zasilkovna_prices = get_option('zasilkovna_prices', array());
        $country_currency = get_option('zasilkovna_country_currency');

        if (!empty($country_currency['country_currency_deactivate']) && $country_currency['country_currency_deactivate'] == 'ok') {
            $return = array($price, $currency);
            return apply_filters('toret-zasilkovna-price-convert', $return, $price, $order);
        }

        if (empty($zasilkovna_prices['kurz-euro'])) {
            $kurz_eur = 0.041;
        } else {
            $kurz_eur = $zasilkovna_prices['kurz-euro'];
        }
        if (empty($zasilkovna_prices['kurz-forint'])) {
            $kurz_forint = 17.49;
        } else {
            $kurz_forint = $zasilkovna_prices['kurz-forint'];
        }
        if (empty($zasilkovna_prices['kurz-zloty'])) {
            $kurz_zloty = 0.20;
        } else {
            $kurz_zloty = $zasilkovna_prices['kurz-zloty'];
        }
        if (empty($zasilkovna_prices['kurz-lei'])) {
            $kurz_lei = 0.20;
        } else {
            $kurz_lei = $zasilkovna_prices['kurz-lei'];
        }
        if (empty($zasilkovna_prices['kurz-usd'])) {
            $kurz_usd = 0.040;
        } else {
            $kurz_usd = $zasilkovna_prices['kurz-usd'];
        }

        $czk_only = array('CZ', 'RO', 'BG', 'DK');
        $set_optional_countries = array('SK', 'PL', 'HU', 'DE', 'PL', 'AT');

        $exchanges_rates = array(
            'CZK' => 1,
            'EUR' => (float)str_replace(',', '.', $kurz_eur),
            'PLN' => (float)str_replace(',', '.', $kurz_zloty),
            'RON' => (float)str_replace(',', '.', $kurz_lei),
            'HUF' => (float)str_replace(',', '.', $kurz_forint),
            'USD' => (float)str_replace(',', '.', $kurz_usd)
        );

        foreach ($exchanges_rates as $index => $exchanges_rate) {
            $exchanges_rates[$index] = ($exchanges_rate != 0 ? $exchanges_rate : 1);
        }

        $allrates = array(
            'CZ' => "CZK",
            'SK' => 'EUR',
            'PL' => 'PLN',
            'RO' => 'RON',
            'HU' => 'HUF',
            'US' => 'USD'
        );

        $eu_countries = array(
            'AT',
            'BE',
            'CY',
            'EE',
            'FI',
            'FR',
            'DE',
            'GR',
            'IE',
            'IT',
            'LV',
            'LT',
            'LU',
            'MT',
            'NL',
            'PT',
            'SI',
            'ES'
        );

        if (in_array($currency, $allrates)) {

            $option = 'country_currency_' . strtolower($country);

            if (in_array($country, $set_optional_countries)) {
                if (isset($country_currency[$option]) && !empty($country_currency[$option])) {
                    $allowed_currency = $country_currency[$option];
                } else {
                    $allowed_currency = 'CZK';
                }
            } else if (in_array($country, $eu_countries)) {
                $allowed_currency = 'EUR';
            } elseif (in_array($country, $czk_only)) {
                $allowed_currency = 'CZK';
            } else {
                $allowed_currency = 'CZK';
            }

            if ($country == 'CZ' || $allowed_currency == 'CZK' || in_array($country, $czk_only)) {
                $price = round($price * (1 / $exchanges_rates[$currency]), 2);
                $return = array($price, 'CZK', 'first', $allowed_currency);
            } elseif (in_array($country, $set_optional_countries)/*  && $allowed_currency != $allrates[$country]*/) {
                $in_czk = $price * (1 / $exchanges_rates[$currency]);
                $price = round($in_czk * $exchanges_rates[$allowed_currency], 2);
                $return = array($price, $allowed_currency, 'second', $allowed_currency);
            } elseif (in_array($country, $eu_countries)) {
                $in_czk = $price * (1 / $exchanges_rates[$currency]);
                $price = round($in_czk * $exchanges_rates['EUR'], 2);
                $return = array($price, 'EUR', 'third', $allowed_currency);
            } elseif ($currency == $allrates[$country]) {
                $return = array($price, $currency, 'fourth', $allowed_currency);
            } elseif ($currency == 'USD') {
                $in_czk = $price * (1 / $exchanges_rates[$currency]);
                $price = round($in_czk * $exchanges_rates[$allowed_currency], 2);
                $return = array($price, $allowed_currency, 'second', $allowed_currency);

            } else {
                $return = array($price, $currency, 'sixth', $allowed_currency);
            }

        } else {
            $return = array($price, 'CZK', 'nothing', 'nothing');
        }

        return apply_filters('toret-zasilkovna-price-convert', $return, $price, $order);
    }
}