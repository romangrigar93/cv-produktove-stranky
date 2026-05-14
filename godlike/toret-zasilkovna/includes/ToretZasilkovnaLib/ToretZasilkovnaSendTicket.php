<?php

use ToretZasilkovna\Toret\Library\Dimensions;
use ToretZasilkovna\Toret\Library\ExchangeRates;
use ToretZasilkovna\Toret\Library\Shipping;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class ToretZasilkovnaSendTicket
{
    /**
     * Send ticket to Zásilkovna
     */
    public static function send_ticket($order_id, $package_count = 1, $show_notice = false, $bulk = false)
    {
        $barcode = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode');

        $return = [];
        $return['status'] = 'error';
        $return['errormsg'] = 'Unknown error.';
        $bulk_result = [];

        if (empty($barcode) || apply_filters('zasilkovna_allow_multiple_submissions', false)) {

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

            $zasilkovna_option = get_option('zasilkovna_option', []);

            $ToretZasilkovna = ToretZasilkovnaLib();
            $weight = ToretZasilkovnaDimensionHelper::get_zasilkovna_weight($order_id);

            $weights = array();

            if ($package_count == 1) {

                $weights[] = self::get_final_weight($weight);

            } else {


                $order = wc_get_order($order_id);
                $max_dim = Dimensions::get_order_max_dimension($order, false, true);
                $max_dim_sum = Dimensions::get_order_max_sides_sum($order, false, true);
                $multipackage_data = $ToretZasilkovna->Helper->get_multipackage_data($zasilkovna_option, $weight, $max_dim, $max_dim_sum);

                $package_count = $multipackage_data['qty'];
                $base_weight = $multipackage_data['baseweight'];
                $remainder = $multipackage_data['reminder'];

                for ($i = 1; $i < $package_count + 1; $i++) {
                    if ($i < $package_count) {
                        $w = $base_weight;
                    } else {
                        $w = $remainder;
                    }
                    if ($w < 0.001) {
                        $w = 0.001;
                    }
                    $weights[] = $w;
                }
            }

            $sent_package_order_ids = [];

            $zasilkovna_status = '';
            for ($i = 1; $i < $package_count + 1; $i++) {
                $order = wc_get_order($order_id);

                if ($package_count == 1) {

                    $shipping_method = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy', true);
                    $service = tzas_get_service_from_string($shipping_method);

                    $disabled = $ToretZasilkovna->Helper->DisableByDim($service, $order->get_shipping_country(), false, $order);

                    if ($disabled) {
                        $return['status'] = 'error';
                        $return['errormsg'] = 'The package dimensions have exceeded the maximum value';

                        zasilkovna_log(array(
                            'order_id' => $order_id,
                            'log' => __('Order ID: ' . $order_id, 'zasilkovna'),
                            'context' => __('Create shipment failed because the package dimensions have exceeded the maximum value.', 'zasilkovna') . __(' Package ', 'zasilkovna') . $i . '/' . $package_count
                        ));

                        if ($show_notice) {
                            (new ToretZasilkovnaOutputs)->show_admin_notice_error(__('Create shipment failed because the package dimensions have exceeded the maximum value.', 'zasilkovna'), $order_id, TORETZASILKOVNALOGSLUG, 'zasilkovna', true);
                        }
                        $bulk_result[] = 'error';
                        continue;
                    }
                }

                if (!$ToretZasilkovna->Helper->check_email_address($order->get_billing_email())) {
                    zasilkovna_log(array(
                        'order_id' => $order_id,
                        'log' => __('Order ID: ' . $order_id, 'zasilkovna'),
                        'context' => __('Create shipment failed because the billing email address is not valid.', 'zasilkovna') . __(' Package ', 'zasilkovna') . $i . '/' . $package_count
                    ));
                    if ($show_notice) {
                        (new ToretZasilkovnaOutputs)->show_admin_notice_error(__('Create shipment failed because the billing email address is not valid.', 'zasilkovna'), $order_id, TORETZASILKOVNALOGSLUG, 'zasilkovna', true);
                    }
                    $bulk_result[] = 'error';
                    continue;
                }

                $zasilkovna_id = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_pobocky', true);

                if (!empty($zasilkovna_id)) {

                    if (self::check_ship_id($order) === true) {

                        try {
                            $zasilkovna_order = self::set_zasilkovna_order($order, $zasilkovna_option, $zasilkovna_id);
                        } catch (Exception $e) {

                            $return['status'] = 'error';
                            $return['errormsg'] = $e->getMessage();

                            zasilkovna_log(array(
                                'order_id' => $order_id,
                                'log'      => __('Order ID: ' . $order_id, 'zasilkovna'),
                                'context' => __("The shipment could not be created because the exchange rate has not been set in the plugin settings in the ", 'zasilkovna') .
                                    '<a href="' . admin_url('admin.php?page=zasilkovna&form=exchange_rates-settings') . '" target="_blank">' . __("'Exchange rates' tab", 'zasilkovna') . '</a>.' .
                                    __(' Package ', 'zasilkovna') . $i . '/' . $package_count,
                                'type'     => 1,
                            ));

                            if ($show_notice) {
                                (new ToretZasilkovnaOutputs)->show_admin_notice_error(
                                    $e->getMessage(),
                                    $order_id,
                                    TORETZASILKOVNALOGSLUG,
                                    'zasilkovna',
                                    true
                                );
                            }

                            return $return;
                        }

                        $apiPassword = $zasilkovna_option['api_password'];

                        $control = Toret_HPOS_Compatibility::get_order_meta($order_id, '_zasilkovna_odeslano', true);

                        if ((empty($control) && $control != 'ok') || ($i < $package_count + 1) || apply_filters('zasilkovna_allow_multiple_submissions', false)) {

                            $xml = self::create_xml($order_id, $apiPassword, $zasilkovna_order, $i, $package_count, $weights);

                            $args = array(
                                'body' => $xml,
                                'timeout' => 60
                            );

                            $response = wp_remote_post('https://www.zasilkovna.cz/api/rest', $args);

                            if (!is_wp_error($response)) {
                                $result = json_decode(json_encode(simplexml_load_string(wp_remote_retrieve_body($response))), true);
                            } else {
                                $result = null;
                            }


                            $log_data = [
                                'url' => 'https://www.zasilkovna.cz/api/rest',
                                'method' => 'POST',
                                'httpcode' => (empty($response) ? '' : wp_remote_retrieve_response_code($response)),
                                'body' => htmlspecialchars($xml),
                                'response' => (empty($response) ? '' : json_decode(json_encode(simplexml_load_string(wp_remote_retrieve_body($response))), true)),
                                'responseRaw' => json_encode($response)
                            ];

                            zasilkovna_log(array(
                                'order_id' => $order_id,
                                'log' => json_encode($log_data, JSON_UNESCAPED_UNICODE),
                                'context' => __('Packeta API call: ', 'zasilkovna') . ' ' . __('Package', 'zasilkovna') . ' ' . $i . '/' . $package_count,
                                'type' => 4,
                            ));

                            if (empty($result)) {
                                $return['status'] = 'error';
                                $return['errormsg'] = 'unknown error';
                                return $return;
                            }

                            if ($result['status'] == 'ok') {
                                $id = self::result_ok($order_id, $result, $apiPassword, $i, $package_count, $weights, $show_notice);
                                $return['status'] = 'ok';
                                $return['errormsg'] = '';
                                $bulk_result[] = 'ok';
                                $sent_package_order_ids[] = $order_id;
                            } elseif ($result['status'] == 'fault') {
                                $fault_data = self::result_fault($order_id, $result, $zasilkovna_option, $order, $i, $package_count, $show_notice);
                                $id = $fault_data[0];
                                $return['status'] = 'error';
                                $return['errormsg'] = $fault_data[1];
                                $bulk_result[] = 'error';
                            }

                            if ($package_count > 1) {
                                $zasilkovna_status .= ($i == 1 ? '' : ';') . $result['status'];
                            } else {
                                $zasilkovna_status = $result['status'];
                            }

                            Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_status', $zasilkovna_status);

                            if ($result['status'] == 'ok') {

                                $ToretZasilkovna = ToretZasilkovnaLib();
                                $ToretZasilkovna->Helper->GetPacketStatus($order_id, 'zasilkovna_order_status', 'zasilkovna_barcode', true, array($id), $i);

                                if (!empty($zasilkovna_option['asistent']) && $zasilkovna_option['asistent'] == 'ok') {
                                    if (!empty($zasilkovna_option['asisten_track']) && $zasilkovna_option['asisten_track'] == 'ok') {
                                        $ToretZasilkovna->Helper->GetPacketStatus($order_id, 'zasilkovna_order_claim_status', 'zasilkovna_barcode_assistent', true, array($id), $i);
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($sent_package_order_ids as $sent_package_order_id) {
                    if (!empty($zasilkovna_option['asisten_direct']) && $zasilkovna_option['asistent'] == 'ok') {
                        if ($zasilkovna_option['asisten_direct'] == 'ok') {
                            $ToretZasilkovna->Claim->send_ticket($sent_package_order_id);
                        }
                    }
                }
            }
        }

        if (!$bulk) {
            return $return;
        } else {
            return $bulk_result;
        }
    }

    /**
     * Check shipping ID
     */
    private static function check_ship_id(object $order): bool
    {
        $shipping = Toret_HPOS_Compatibility::get_order_meta($order->get_id(), 'zasilkovna_id_dopravy', true);
        $ship_ids = self::shipping_ids();
        if (in_array($shipping, $ship_ids)) {
            return true;
        }
        return false;
    }

    /**
     * Array of Zasilkovna ids
     *
     */
    private static function shipping_ids(): array
    {
        $ToretZasilkovna = ToretZasilkovnaLib();
        return $ToretZasilkovna->Helper->set_order_shipping_ids();
    }

    /**
     * get order data
     */
    public static function set_zasilkovna_order(object $order, array $zasilkovna_option, string $zasilkovna_id): array
    {
        $country = self::set_order_country($order);
        $currency_price = self::convert_price($country, $order->get_total(), $order);

        if (isset($currency_price['error']) && $currency_price['error'] === true) {
            throw new Exception($currency_price['message']);
        }

        $ToretZasilkovna = new ToretZasilkovnaLib();

        $currency = $currency_price[1];
        $email = $order->get_billing_email();
        $phone = $order->get_billing_phone();

        $name = (empty($order->get_shipping_first_name()) ? $order->get_billing_first_name() : $order->get_shipping_first_name());
        $surname = (empty($order->get_shipping_last_name()) ? $order->get_billing_last_name() : $order->get_shipping_last_name());
        $street = $order->get_shipping_address_1();
        $house = $order->get_shipping_address_2();
        $town = $order->get_shipping_city();
        $zip = $order->get_shipping_postcode();
        $company = $order->get_shipping_company();

        $price = $currency_price[0];

        $custom_price_value = Toret_HPOS_Compatibility::get_order_meta($order->get_id(), 'zasilkovna_custom_total', true);
        if ($custom_price_value != '') {
            $price_value = $custom_price_value;
        } else {
            $price_value = $price;
        }

        $price_value = apply_filters('zasilkovna_ticket_value', $price_value, $order, $zasilkovna_option, $zasilkovna_id);

        $eshop = $zasilkovna_option['nazev_eshopu'];

        /** @var WC_Order $order */
        $order_number = $order->get_order_number();
        $zasilkovna_order_number = apply_filters('zasilkovna_order_number', $order_number, $order);

        $note = tzas_get_note_with_shortcodes($order, $zasilkovna_option['tzas_packet_note'] ?? '');
        $number_after = tzas_get_note_with_shortcodes($order, $zasilkovna_option['ref_text_after'] ?? '');
        $number_before = tzas_get_note_with_shortcodes($order, $zasilkovna_option['ref_text_before'] ?? '');
        $number = $number_before . $zasilkovna_order_number . $number_after;
        if (strlen($number) > 35) {
            $number = $zasilkovna_order_number;
        }

        $zasilkovna_order = array();
        $zasilkovna_order['number'] = $number;
        $zasilkovna_order['name'] = (string)$name;
        $zasilkovna_order['surname'] = (string)$surname;
        $zasilkovna_order['company'] = (string)$company;
        $zasilkovna_order['email'] = (string)$email;
        $zasilkovna_order['phone'] = (string)$phone;
        $zasilkovna_order['street'] = (string)$street;
        $zasilkovna_order['houseNumber'] = (string)$house;
        $zasilkovna_order['city'] = (string)$town;
        $zasilkovna_order['zip'] = (string)$zip;
        $zasilkovna_order['country'] = (string)$country;
        $zasilkovna_order['currency'] = (string)$currency;
        $zasilkovna_order['note'] = (string)$note;

        $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($order->get_id(), 'zasilkovna_id_dopravy');
        $dopravce = tzas_get_service_from_string($zasilkovna_shipping);

        if ($ToretZasilkovna->Customs->is_declaration_enabled($dopravce, $zasilkovna_order['country'])) {
            $orig_titles = $zasilkovna_option['customs_en_as_orig'] ?? '';

            $product_ids = [];
            $variation_ids = [];
            foreach ($order->get_items() as $item) {
                if ($item->get_variation_id() > 0) {
                    $id = $item->get_variation_id();
                    $product = wc_get_product($id);
                    $variation_ids[] = array(
                        'id' => $id,
                        'q' => $item->get_quantity(),
                        'total' => $item->get_total(),
                        'weight' => $product->get_weight(),
                        'name' => $item->get_total(),
                        'nameen' => ($orig_titles == 'ok') ? $item->get_name() : get_post_meta($id, '_zasilkovna_en_product_title', true),
                        'customcode' => get_post_meta($id, '_zasilkovna_custom_code', true),
                    );
                } else {
                    $id = $item->get_product_id();
                    $product = wc_get_product($id);
                    $product_ids[] = array(
                        'id' => $id,
                        'q' => $item->get_quantity(),
                        'total' => $item->get_total(),
                        'name' => $item->get_name(),
                        'weight' => $product->get_weight(),
                        'nameen' => ($orig_titles == 'ok') ? $item->get_name() : get_post_meta($id, '_zasilkovna_en_product_title', true),
                        'customcode' => get_post_meta($id, '_zasilkovna_custom_code', true),
                    );
                }
            }
            $zasilkovna_order['products'] = $product_ids;
            $zasilkovna_order['variations'] = $variation_ids;
        }

        $s_method = $order->get_payment_method();
        $zasilkovna_order['cod'] = zasilkovna_get_cod_value($s_method, $price, $country, $order);
        $zasilkovna_order['addressId'] = (int)$zasilkovna_id;
        $zasilkovna_order['value'] = (float)$price_value;

        $zasilkovna_order['eshop'] = $eshop;

        return apply_filters('zasilkovna_order_data', $zasilkovna_order, $order, false);
    }

    /**
     * get order country
     */
    public static function set_order_country($order): string
    {
        $country = $order->get_shipping_country();
        if (empty($country))
            $country = $order->get_billing_country();

        if (empty($country)) {
            $country = toret_get_customer_country();
        }

        return $country;
    }

    /**
     * create XML
     */
    private static function create_xml(int $order_id, string $apiPassword, array $zasilkovna_order, $i, $package_count, $weights): string
    {
        $ToretZasilkovna = ToretZasilkovnaLib();
        $komplet_data = $ToretZasilkovna->Helper->komplet_data();
        $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy');
        $zasilkovnaCarrierId = (int)(Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_carrierId') != 'undefined' ? Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_carrierId') : 0);
        $addressId = self::check_addressid($zasilkovna_shipping, $komplet_data, $zasilkovna_order, $zasilkovnaCarrierId);
        $zasilkovna_id = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_pobocky');

        $dopravce = tzas_get_service_from_string($zasilkovna_shipping);

        $zasilkovna_option = get_option('zasilkovna_option', []);

        $value = (float)$zasilkovna_order['value'] / (float)$package_count;

        if (isset($zasilkovna_option['pricelimit_reduce'])) {
            if ($zasilkovna_option['pricelimit_reduce'] == 'ok') {
                $limit = (new ToretZasilkovnaLimits)->get_country_cod_insurance_limit($zasilkovna_order['country'], $zasilkovna_order['currency'], 'value');
                if ($value > $limit) {
                    $value = $limit;
                }
            }
        }

        $cod = $zasilkovna_order['cod'];
        if ($i != 1) {
            $cod = 0;
            if ($zasilkovna_order['currency'] == 'HUF') {
                $cod = round($value / 5) * 5;
                $cod = (round($cod) % $cod === 0) ? round($cod) : round(($cod + 5 / 2) / 5) * 5;
            }
        }

        //Max 32 znaků
        $xml = '<createPacket>
                    <apiPassword>' . $apiPassword . '</apiPassword>
                    <packetAttributes>
                        <number>' . $zasilkovna_order['number'] . '</number>
                        <name>' . htmlspecialchars($zasilkovna_order['name']) . '</name>
                        <surname>' . htmlspecialchars($zasilkovna_order['surname']) . '</surname>
                        <email>' . $zasilkovna_order['email'] . '</email>
                        <phone>' . $zasilkovna_order['phone'] . '</phone>
                        <street>' . htmlspecialchars($zasilkovna_order['street']) . '</street>
                        <houseNumber>' . htmlspecialchars($zasilkovna_order['houseNumber']) . '</houseNumber>
                        <city>' . $zasilkovna_order['city'] . '</city>
                        <zip>' . $zasilkovna_order['zip'] . '</zip>
                        <country>' . $zasilkovna_order['country'] . '</country>
                        <currency>' . $zasilkovna_order['currency'] . '</currency>
                        <cod>' . $cod . '</cod>
                        <addressId>' . $addressId . '</addressId>
                        <value>' . $value . '</value>
                        <eshop>' . htmlspecialchars($zasilkovna_order['eshop']) . '</eshop>
                        <note>' . $zasilkovna_order['note'] . '</note>
                        <adultContent>' . self::check_adult($order_id) . '</adultContent>';

        /*if ($dopravce == 'cz-zasilkovna-do-auta') {
            $xml .= '<carDeliveryId>' . (string)'123456' . '</carDeliveryId>';
        }*/

        if ($ToretZasilkovna->Customs->is_declaration_enabled($dopravce, $zasilkovna_order['country'])) {
            $xml .= '<attributes>';
            $xml .= '<attribute><key>ead</key><value>carrier</value></attribute>';
            $xml .= '<attribute><key>deliveryCost</key><value>' . $zasilkovna_order['value'] . '</value></attribute>';
            $xml .= '<attribute><key>invoiceNumber</key><value>' . $ToretZasilkovna->Customs->is_invoice_number_available($order_id) . '</value></attribute>';
            $xml .= '<attribute><key>invoiceIssueDate</key><value>' . $ToretZasilkovna->Customs->is_invoice_date_available($order_id) . '</value></attribute>';
            $xml .= '</attributes>';

            $xml .= '<items>';

            foreach ($zasilkovna_order['products'] as $order_item) {
                $xml .= '<item>';
                $xml .= '<attributes>';
                $xml .= '<attribute><key>customsCode</key><value>' . $order_item['customcode'] . '</value></attribute>';
                $xml .= '<attribute><key>countryOfOrigin</key><value>' . wc_get_base_location()['country'] . '</value></attribute>';
                $xml .= '<attribute><key>value</key><value>' . $order_item['id'] . '</value></attribute>';
                $xml .= '<attribute><key>productNameEn</key><value>' . $order_item['nameen'] . '</value></attribute>';
                $xml .= '<attribute><key>productName</key><value>' . $order_item['name'] . '</value></attribute>';
                $xml .= '<attribute><key>unitsCount</key><value>' . $order_item['q'] . '</value></attribute>';
                $xml .= '<attribute><key>weight</key><value>' . $order_item['weight'] . '</value></attribute>';
                $xml .= '</attributes>';
                $xml .= '</item>';
            }

            $xml .= '</items>';
        }

        if (isset($weights[$i - 1])) {
            $xml .= '<weight>' . $weights[$i - 1] . '</weight>';
        }

        if ($zasilkovnaCarrierId != 0) {
            if ($zasilkovna_id == $zasilkovnaCarrierId)
                $xml .= '<carrierPickupPoint>' . Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_carrierPickupPointId', true) . '</carrierPickupPoint>';
            else
                $xml .= '<carrierPickupPoint>' . $zasilkovna_id . '</carrierPickupPoint>';
        }

        if (isset($zasilkovna_order['company'])) {
            $xml .= '<company>' . htmlspecialchars($zasilkovna_order['company']) . '</company>';
        }

        if (!empty(self::check_size($order_id))) {
            $rozmeryData = self::check_size($order_id);
            $xml .= '<size>
                        <width>' . $rozmeryData['sirka'] . '</width>   
                        <height>' . $rozmeryData['vyska'] . '</height>   
                        <length>' . $rozmeryData['delka'] . '</length>   
                     </size>';
        }
        $xml .= '</packetAttributes>
                 </createPacket>';

        return apply_filters('zasilkovna_create_ticket_xml', $xml, $order_id, $apiPassword, $zasilkovna_order);
    }

    /**
     * check address ID
     */
    private static function check_addressid(string $zasilkovna_shipping, array $komplet_data, array $zasilkovna_order, string $zasilkovnaCarrierId): int
    {
        if ($zasilkovnaCarrierId == 0) {
            if (self::check_branches($zasilkovna_shipping, $komplet_data) == 1 && !tzas_is_native_pickup_method(tzas_get_service_from_string($zasilkovna_shipping))) {
                $addressId = self::check_addresskey($zasilkovna_shipping, $komplet_data);
            } else {
                $addressId = $zasilkovna_order['addressId'];
            }
        } else {
            $addressId = $zasilkovnaCarrierId;
        }

        return $addressId;
    }

    /**
     * Control if is shipping method on Zasilkovna methods
     */
    private static function check_branches(string $zasilkovna_shipping, array $komplet_data): int
    {
        $pobocky = 0;
        foreach ($komplet_data as $data) {
            if ($data['prac'] == $zasilkovna_shipping && $data['pobocky'] == 1) {
                $pobocky = $data['pobocky'];
                break;
            }
        }

        return $pobocky;
    }

    /**
     * Control if is shipping method on Zasilkovna methods
     */
    private static function check_addresskey(string $zasilkovna_shipping, array $komplet_data): int
    {
        $addresskey = 0;
        foreach ($komplet_data as $key => $data) {
            if ($data['prac'] == $zasilkovna_shipping && $data['pobocky'] == 1) {
                $addresskey = $key;
            }
        }

        return $addresskey;
    }

    /**
     * check adult content
     */
    private static function check_adult(int $order_id): int
    {
        $overeni = 0;
        $order_overeni_veku = wc_get_order($order_id);
        $items_overeni_veku = $order_overeni_veku->get_items();
        foreach ($items_overeni_veku as $item_ov) {
            $product_id = $item_ov->get_product_id();

            $overeni_produkt = get_post_meta($product_id, '_zasilkovna_vek', true);

            if (!empty($overeni_produkt) && ($overeni_produkt == 'yes')) {
                $overeni = 1;
            }
        }

        return $overeni;
    }

    /**
     * check weight
     */
    private static function get_final_weight($weight): float
    {
        $zasilkovna_option = get_option('zasilkovna_option', []);
        if (isset($zasilkovna_option['zas_add_wrap_weight']) && $zasilkovna_option['zas_add_wrap_weight'] > 0) {
            $weight += $zasilkovna_option['zas_add_wrap_weight'];
        }

        return apply_filters('zasilkovna_packeta_weight_check', $weight);
    }

    /**
     * check size
     */
    private static function check_size(int $order_id): array
    {
        $rozmeryData = (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dimension', true) ? Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_custom_dimension', true) : '');
        $rozmery = array();
        if ($rozmeryData != '') {
            $rozmeryParts = explode('|', $rozmeryData);
            $rozmery = array(
                'sirka' => $rozmeryParts[0],
                'vyska' => $rozmeryParts[1],
                'delka' => $rozmeryParts[2]
            );
        }

        return $rozmery;
    }

    /**
     * save OK result
     */
    private static function result_ok(int $order_id, array $result, string $apiPassword, $package_order = 1, $package_count = 1, array $weights = array(), $show_notice = false)
    {
        $zasilkovna_id_zasilky = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky', true);
        $zasilkovna_id_zasilky .= ($package_order == 1 ? '' : ';') . $result['result']['id'];

        $zasilkovna_barcode = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode', true);
        $zasilkovna_barcode .= ($package_order == 1 ? '' : ';') . $result['result']['barcode'];

        $zasilkovna_barcodeText = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcodeText', true);
        $zasilkovna_barcodeText .= ($package_order == 1 ? '' : ';') . $result['result']['barcodeText'];

        $_zasilkovna_odeslano = Toret_HPOS_Compatibility::get_order_meta($order_id, '_zasilkovna_odeslano', true);
        $_zasilkovna_odeslano .= ($package_order == 1 ? '' : ';') . 'ok';

        $zasilkovna_weights = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_weights', true);
        $zasilkovna_weights .= ($package_order == 1 ? '' : ';') . ($weights[$package_order - 1] ?? '0.0001');

        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_weights', $zasilkovna_weights);
        Toret_HPOS_Compatibility::update_order_meta($order_id, '_zasilkovna_odeslano', $_zasilkovna_odeslano);
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_zasilky', $zasilkovna_id_zasilky);
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_barcode', $zasilkovna_barcode);
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_barcodeText', $zasilkovna_barcodeText);
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_package_count', $package_count);
        Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_is_multipackage', ($package_count > 1 ? '1' : '0'));
        Toret_HPOS_Compatibility::delete_order_meta($order_id, 'zasilkovna_failText');

        if ($show_notice) {
            (new ToretZasilkovnaOutputs)->show_admin_notice_success(__('Package(s) sent to Zasilkovna.', 'zasilkovna'), $order_id, TORETZASILKOVNALOGSLUG, 'zasilkovna');
        }

        if (strpos(Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy', true), 'z-points') === false) {
            $count = get_option('toret_service_count', 0);
            $count++;
            update_option('toret_service_count', $count);
            $gw = new SoapClient("https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl");
            try {
                $id_baliku_dopravce = $gw->packetCourierNumber($apiPassword, $result['result']['barcode']);
                $id_baliku_dopravce_meta = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_dopravce', true);
                $id_baliku_dopravce_meta .= ($package_order == 1 ? '' : ';') . $id_baliku_dopravce;
                if (explode(';', $id_baliku_dopravce_meta) > 1 && $package_order == 1) {
                    $id_baliku_dopravce_meta = $id_baliku_dopravce;
                }
                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_id_zasilky_dopravce', $id_baliku_dopravce_meta);
            } catch (SoapFault $e) {
                if (isset($id_baliku_dopravce)) {
                    zasilkovna_log(array(
                        'order_id' => $order_id,
                        'log' => serialize($id_baliku_dopravce),
                        'context' => __('Packeta’s answer - searching for carrier id.', 'zasilkovna') . __(' Package ', 'zasilkovna') . $package_order . '/' . $package_count
                    ));
                }
            }
        } else {
            $count = get_option('toret_zpoint_count', 0);
            $count++;
            update_option('toret_zpoint_count', $count);
        }

        return $result['result']['id'];
    }

    /**
     * save fault result
     */
    private static function result_fault(int $order_id, array $result, array $zasilkovna_option, object $order, $package_order = 1, $package_count = 1, $show_notice = false)
    {
        $zasilkovna_failText = '';

        if (!empty($result['detail'])) {

            if (!class_exists('WC_Email')) {
                WC()->mailer();
            }

            if (!empty($result['detail']['attributes']['fault']['fault'])) {

                $zasilkovna_failText = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_failText', true);
                $zasilkovna_failText .= ($package_order == 1 ? '' : ';') . $result['detail']['attributes']['fault']['fault'];

                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_failText', $zasilkovna_failText);

                $note = __('Shipment has not been saved with error:', 'zasilkovna') . ' ' . $result['detail']['attributes']['fault']['fault'];

                $order->add_order_note($note);

                if ($show_notice) {
                    (new ToretZasilkovnaOutputs)->show_admin_notice_error($note, $order_id, TORETZASILKOVNALOGSLUG, 'zasilkovna', true);
                }

                if (!empty($zasilkovna_option['error_email']) && $zasilkovna_option['error_email'] == 'email') {
                    require_once(WOOZASILKOVNADIR . 'includes/ToretZasilkovnaLib/ToretWooExtension/ToretZasilkovnaWcAdminErrorInfo.php');
                    $send = new ToretZasilkovnaWcAdminErrorInfo();
                    $send->trigger($order_id, $note);
                }

            } elseif (!empty($result['detail']['attributes']['fault'][0]['fault'])) {

                $zasilkovna_failText = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_failText', true);
                $zasilkovna_failText .= ($package_order == 1 ? '' : ';') . $result['detail']['attributes']['fault'][0]['fault'];

                Toret_HPOS_Compatibility::update_order_meta($order_id, 'zasilkovna_failText', $zasilkovna_failText);

                $note = __('Shipment has not been saved with error:', 'zasilkovna') . ' ' . $result['detail']['attributes']['fault'][0]['fault'];

                if ($show_notice) {
                    (new ToretZasilkovnaOutputs)->show_admin_notice_error($note, $order_id, TORETZASILKOVNALOGSLUG, 'zasilkovna', true);
                }

                $order->add_order_note($note);

                if (!empty($zasilkovna_option['error_email'])) {
                    require_once(WOOZASILKOVNADIR . 'includes/ToretZasilkovnaLib/ToretWooExtension/ToretZasilkovnaWcAdminErrorInfo.php');
                    $send = new ToretZasilkovnaWcAdminErrorInfo();
                    $send->trigger($order_id, $note);
                }
            }
        }
        return array('', $zasilkovna_failText);
    }

    public static function convert_price($country, $price, $order, $currency = 'CZK')
    {
        if (!empty($order)) {
            $currency = $order->get_currency();
        }

        $base_currency = get_woocommerce_currency();
        $zasilkovna_prices = get_option('zasilkovna_prices', array());
        $country_currency = get_option('zasilkovna_country_currency', array());

        if (($country_currency['country_currency_deactivate'] ?? '') === 'ok') {
            return apply_filters('toret-zasilkovna-price-convert', array($price, $currency), $price, $order);
        }

        $rates = array();
        $rates[$base_currency] = 1.0;

        $currency_mapping = [
            'kurz-czk' => 'CZK',
            'kurz-euro' => 'EUR',
            'kurz-forint' => 'HUF',
            'kurz-zloty' => 'PLN',
            'kurz-lei' => 'RON',
            'kurz-usd' => 'USD',
        ];

        foreach ($currency_mapping as $key => $curr) {

            if ($curr === $base_currency) {
                continue;
            }

            $raw_rate = $zasilkovna_prices[$key] ?? '';

            if ($raw_rate === '' || (float)str_replace(',', '.', $raw_rate) <= 0) {
                if ($curr === $currency) {
                    return array(
                        'error' => true,
                        'message' => 'Missing exchange rate for currency: ' . $curr
                    );
                }
                continue;
            }

            $rates[$curr] = (float)str_replace(',', '.', $raw_rate);
        }

        $countries_with_options = array(
            'BG' => 'CZK',
            'CZ' => 'CZK',
            'HU' => 'CZK',
            'DE' => 'CZK',
            'PL' => 'CZK',
            'AT' => 'CZK',
            'RO' => 'CZK',
            'SK' => 'CZK',
        );

        $option_key = 'country_currency_' . strtolower($country);
        if (isset($countries_with_options[$country])) {
            $target_currency = $country_currency[$option_key] ?? $countries_with_options[$country];
        } else {
            $all_eu_countries = Shipping::getEuVatCountries();
            $other_eu_countries = array_diff($all_eu_countries, array_keys($countries_with_options));
            $other_eu_countries = array_keys($other_eu_countries);

            if (in_array($country, $other_eu_countries)) {
                $target_currency = isset($rates['EUR']) ? 'EUR' : 'CZK';
            } else {
                $target_currency = 'CZK';
            }
        }

        $order_rate = $rates[$currency] ?? null;
        $target_rate = $rates[$target_currency] ?? null;

        if (!$target_rate) {
            $target_currency = 'CZK';
            $target_rate = $rates['CZK'] ?? 1.0;
        }

        if (!$order_rate) {
            return apply_filters('toret-zasilkovna-price-convert', array($price, 'CZK'), $price, $order);
        }

        $price_in_base = $price / $order_rate;

        $final_price = round($price_in_base * $target_rate, 2);

        $return = array(
            $final_price,
            $target_currency,
            'mode_sek_conversion',
            $target_currency,
            $currency
        );

        return apply_filters('toret-zasilkovna-price-convert', $return, $price, $order);
    }

    /**
     * Bulk print from app
     */
    public function bulk_print_from_app($order_ids_string = '', $format = "A7 on A7"): string
    {
        //$gw = new SoapClient("https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl");

        $zasilkovna_option = get_option('zasilkovna_option', []);
        //$apiPassword = $zasilkovna_option['api_password'];

        $order_ids = explode(';', $order_ids_string);

        $ids_zasilky = [];
        $ids_dopravy = [];
        //$ids_baliku_dopravce = [];
        $barcodes = [];
        $ids_baliku_dopravce = [];
        foreach ($order_ids as $order_id) {
            if (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky', true) != '') {
                $ids_zasilky[] = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky', true);
            }
            /*if (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy', true) != '') {
                $ids_dopravy[] = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy', true);
            }*/
            if (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_dopravce', true) != '') {
                $ids_baliku_dopravce[] = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_dopravce', true);
            }
            if (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode', true) != '') {
                $barcodes[] = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode', true);
            }
            if (Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_dopravce', true) != '') {
                $ids_baliku_dopravce[] = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_dopravce', true);
            }
        }

        $packetIds = [];
        if (count($ids_baliku_dopravce) > 0) {
            foreach ($ids_zasilky as $index => $id) {
                $packetIds[] = implode('|', array(
                    'packetId' => $id,
                    'courierNumber' => $ids_baliku_dopravce[$index]
                ));
            }
        }

        return "1;" . implode(',', $ids_zasilky) . ';' . implode(',', $barcodes) . ';' . implode(',', $packetIds);
    }

    /**
     * Bulk send from app
     */
    public function bulk_send_from_app($post_ids)
    {
        $post_ids = explode(';', $post_ids);
        $ToretZasilkovna = ToretZasilkovnaLib();
        $return = [];

        foreach ($post_ids as $postID) {
            if (Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_id_zasilky', true) == '') {

                $vaha = ToretZasilkovnaDimensionHelper::get_zasilkovna_weight($postID);

                $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_id_dopravy', true);
                $zasilkovna_service = tzas_get_service_from_string($zasilkovna_shipping);

                $rozmery = 0;
                $shippingID = $rozmery_data = '';
                if ($zasilkovna_shipping) {
                    $komplet_data = $ToretZasilkovna->Helper->komplet_data();
                    if (tzas_is_native_method($zasilkovna_service)) {
                        $shippingID = (Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_carrierId', true) != 'undefined' ? Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_carrierId', true) : 0);
                    }
                    if ($shippingID != '') {
                        if ($shippingID == 0) {
                            foreach ($komplet_data as $data) {
                                if ($data['prac'] == $zasilkovna_shipping) {
                                    $rozmery = $data['rozmery'];
                                }
                            }
                        } else {
                            $service = $ToretZasilkovna->Helper->GetServiceByID($shippingID);
                            $rozmery = $service['rozmery'];
                        }
                        if ($rozmery > 0) {
                            $rozmery_data = (Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_custom_dimension') ? Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_custom_dimension', true) : '');
                        }
                    }
                    if ($rozmery > 0) {
                        $rozmery_data = (Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_custom_dimension') ? Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_custom_dimension', true) : '');
                    }
                }

                $qty = 1;

                $zasilkovna_option = get_option('zasilkovna_option', array());

                $order = wc_get_order($postID);
                $max_dim = Dimensions::get_order_max_dimension($order, false, true);
                $max_dim_sum = Dimensions::get_order_max_sides_sum($order, false, true);
                $multipackage_data = $ToretZasilkovna->Helper->get_multipackage_data($zasilkovna_option, $vaha, $max_dim, $max_dim_sum);
                if ($multipackage_data['enabled']) {
                    $qty = $multipackage_data['qty'];
                }

                if ($rozmery > 0) {
                    if (($rozmery_data != '') && ($vaha > 0)) {
                        $response = $ToretZasilkovna->Send->send_ticket($postID, $qty);
                        $return[$postID] = $response;
                    }
                } else {
                    if ($vaha > 0) {
                        $response = $ToretZasilkovna->Send->send_ticket($postID, $qty);
                        $return[$postID] = $response;
                    }
                }
            } else {
                $return[$postID] = array('status' => 'error', 'errormsg' => 'Package already sent.');
            }
        }
        return $return;
    }
}