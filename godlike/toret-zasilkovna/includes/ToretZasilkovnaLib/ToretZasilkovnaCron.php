<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('ToretZasilkovnaCron')) {
    class ToretZasilkovnaCron
    {
        public function update_services($manual = false)
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'zasilkovna_dopravci';

            $table_staty_name = $wpdb->prefix . 'zasilkovna_staty';

            $zasilkovna_option   = get_option('zasilkovna_option');

            if (!empty($zasilkovna_option['api_key'])) {
                $ToretZasilkovna = ToretZasilkovnaLib();

                $url = 'https://pickup-point.api.packeta.com/v5/' . $zasilkovna_option['api_key'] . '/carrier/json';

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Cookie: _nss=1'
                    ),
                ));
                $response = curl_exec($curl);
                curl_close($curl);

                if($response === false){
                    return;
                }

                $carriers = json_decode($response);

                if (empty($carriers) || json_last_error() !== JSON_ERROR_NONE) {
                    if ($manual) {
                        echo __('Invalid API response.', 'zasilkovna') . '<br/>';
                    }
                    return;
                }

                if ($carriers !== false) {

                    $dopravci = $wpdb->get_results("SELECT * FROM $table_name ORDER BY stat, nazev");

                    foreach ($dopravci as $dop) {
                        $wpdb->update(
                            $table_name,
                            array(
                                'active' => 0,
                                'removed' => 1,
                            ),
                            array('ID' => $dop->ID),
                            array(
                                '%d',
                                '%d'
                            ),
                            array('%d')
                        );
                    }

                    if(is_array($carriers)){
                        $carriers =  (object)$carriers;
                    }

                    foreach ($carriers as $dopravce) {
                        $dopravce_id = (int)$dopravce->id;
                        $nazev = (string)$dopravce->name;
                        $stat = strtoupper((string)$dopravce->country);
                        $statnazev = WC()->countries->countries[$stat];
                        $pobocky = (int)($dopravce->pickupPoints == 'true' ? 1 : 0);
                        $api = (int)($dopravce->apiAllowed == 'true' ? 1 : 0);
                        $vaha = (int)$dopravce->maxWeight;
                        $slug = sanitize_title((string)$dopravce->name);
                        $prac = 'zasilkovna>' . sanitize_title((string)$dopravce->name);

                        $dobirka = (int)($dopravce->disallowsCod == 'true' ? 1 : 0);
                        $deklarace = (int)($dopravce->customsDeclarations == 'true' ? 1 : 0);
                        $rozmery = (int)($dopravce->requiresSize == 'true' ? 1 : 0);
                        $active = 1;

                        $dopr = $wpdb->get_row("SELECT * FROM " . $table_name . " WHERE dopravce_id = $dopravce_id");

                        if (null !== $dopr) {
                            $wpdb->update(
                                $table_name,
                                array(
                                    'dopravce_id' => $dopravce_id,
                                    'nazev' => $nazev,
                                    'stat' => $stat,
                                    'statnazev' => $statnazev,
                                    'pobocky' => $pobocky,
                                    'api' => $api,
                                    'dobirka' => $dobirka,
                                    'deklarace' => $deklarace,
                                    'rozmery' => $rozmery,
                                    'vaha' => $vaha,
                                    'slug' => $slug,
                                    'prac' => $prac,
                                    'active' => $active,
                                    'removed' => 0,
                                    'type' => 'carrier'
                                ),
                                array('dopravce_id' => $dopravce_id),
                                array(
                                    '%d',
                                    '%s',
                                    '%s',
                                    '%s',
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%s',
                                    '%s',
                                    '%d',
                                    '%d',
                                    '%s',
                                ),
                                array('%d')
                            );
                        } else {

                            $wpdb->insert(
                                $table_name,
                                array(
                                    'dopravce_id' => $dopravce_id,
                                    'nazev' => $nazev,
                                    'stat' => $stat,
                                    'statnazev' => $statnazev,
                                    'pobocky' => $pobocky,
                                    'api' => $api,
                                    'dobirka' => $dobirka,
                                    'deklarace' => $deklarace,
                                    'rozmery' => $rozmery,
                                    'vaha' => $vaha,
                                    'slug' => $slug,
                                    'prac' => $prac,
                                    'active' => $active,
                                    'removed' => 0,
                                    'type' => 'carrier'
                                ),
                                array(
                                    '%d',
                                    '%s',
                                    '%s',
                                    '%s',
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%d',
                                    '%s',
                                    '%s',
                                    '%d',
                                    '%d',
                                    '%s',
                                )
                            );
                        }

                        $tabStaty = $wpdb->get_row("SELECT * FROM " . $table_staty_name . " WHERE stat = '$stat'");

                        if (null !== $tabStaty) {
                            $wpdb->update(
                                $table_staty_name,
                                array(
                                    'stat' => $stat,
                                    'statnazev' => $statnazev,
                                ),
                                array('ID' => $tabStaty->ID),
                                array(
                                    '%s',
                                    '%s'
                                ),
                                array('%d')
                            );
                        } else {
                            $insert = $wpdb->insert(
                                $table_staty_name,
                                array(
                                    'stat' => $stat,
                                    'statnazev' => $statnazev,
                                ),
                                array(
                                    '%s',
                                    '%s'
                                )
                            );
                        }

                    }

                    tzas_add_native_to_carriers();

                    update_option('zasilkovna_dopravci_cron', 1);

                    $ToretZasilkovna->Helper->reset_komplet_data();

                    if ($manual) {
                        echo __('Carriers fetched successfully.', 'zasilkovna') . '<br/>';
                    }
                }else{
                    if ($manual) {
                        echo __('Error while fetching carriers.', 'zasilkovna') . '<br/>';
                    }
                }
            }else{
                if ($manual) {
                    echo __('API key is not set.', 'zasilkovna') . '<br/>';
                }
            }
        }

        public function update_statuses($manual = false)
        {
            $zasilkovna_option = get_option('zasilkovna_option');

            if (empty($zasilkovna_option['api_key'])) {
                return;
            }

            if (!function_exists('ToretZasilkovnaLib')) {
                require_once('includes/ToretZasilkovnaLib/ToretZasilkovnaLib.php');
            }

            $ToretZasilkovna = ToretZasilkovnaLib();
            $days = !empty($zasilkovna_option['status_days']) ? (int) $zasilkovna_option['status_days'] : 14;
            $dateMax = apply_filters('zasilkovna_status_cron_date_limit', (time() - (HOUR_IN_SECONDS * 24 * $days)));

            $option_blocked_order_statuses_slugs = $zasilkovna_option['zakazane_stavy'] ?? array();
            $blocked_wc_order_statuses = [];
            if(!is_array($option_blocked_order_statuses_slugs)){
                $option_blocked_order_statuses_slugs = [];
            }
            foreach ($option_blocked_order_statuses_slugs as $slug) {
                if (strpos($slug, 'wc-') !== 0) {
                    $blocked_wc_order_statuses[] = 'wc-' . $slug;
                } else {
                    $blocked_wc_order_statuses[] = $slug;
                }
            }
            $blocked_wc_order_statuses = apply_filters('zasilkovna_status_cron_order_status', $blocked_wc_order_statuses);

            $blockedPacketaStattuses = $zasilkovna_option['zakazane_statusy'] ?? array();

            $all_wc_statuses = array_keys(wc_get_order_statuses());

            $plugin_specific_statuses = ['wc-order_carrier', 'wc-order_claim'];
            foreach ($plugin_specific_statuses as $pss) {
                if (!in_array($pss, $all_wc_statuses)) {
                    $all_wc_statuses[] = $pss;
                }
            }
            $all_wc_statuses = array_unique($all_wc_statuses);

            $searched_wc_statuses = array_diff($all_wc_statuses, $blocked_wc_order_statuses);
            foreach ($plugin_specific_statuses as $pss) {
                if (!in_array($pss, $blocked_wc_order_statuses) && !in_array($pss, $searched_wc_statuses)) {
                    $searched_wc_statuses[] = $pss;
                }
            }
            $searched_wc_statuses = array_values(array_unique($searched_wc_statuses));

            // Sjednocené načítání objednávek
            $order_ids_to_check = [];
            if (!empty($searched_wc_statuses)) {
                $args = array(
                    'status'       => $searched_wc_statuses,
                    'limit'        => -1,
                    'return'       => 'ids',
                    'date_created' => '>' . $dateMax,
                    'orderby'      => 'date',
                    'order'        => 'DESC',
                );
                $order_ids_to_check = wc_get_orders($args);
            }

            $order_ids_to_check = apply_filters('zasilkovna_status_cron_checked_order_ids', $order_ids_to_check,$searched_wc_statuses);

            if (empty($order_ids_to_check)) {
                if($manual){
                    echo __('No orders to check.', 'zasilkovna') . '<br/>';
                }
                return;
            }

            $valid_ids = [];
            $out_of_order_status_debug = [];
            $out_of_package_status_debug = [];

            foreach ($order_ids_to_check as $order_id) {
                $order = wc_get_order($order_id);
                if (!$order) {
                    continue;
                }

                $current_order_status = $order->get_status();
                $current_order_status_wc = 'wc-' . $current_order_status;

                if (in_array($current_order_status, $option_blocked_order_statuses_slugs)) {
                    $out_of_order_status_debug[] = $order_id . ' (status: ' . $current_order_status . ')';
                    continue;
                }

                $orderPacketaStatusMeta = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_order_status', true);
                $packeta_status_code = '';
                if ($orderPacketaStatusMeta) {
                    $status_parts = explode(';', $orderPacketaStatusMeta);
                    $packeta_status_code = $status_parts[0] ?? '';
                }

                if (!empty($packeta_status_code) && in_array($packeta_status_code, $blockedPacketaStattuses)) {
                    $out_of_package_status_debug[] = $order_id . ' (packeta status: ' . $packeta_status_code . ')';
                    continue;
                }

                $valid_ids[] = $order_id;
            }

            if (empty($valid_ids)) {
                if($manual){
                    echo __('No orders to process.', 'zasilkovna') . '<br/>';
                    echo __('Orders skipped by order status:', 'zasilkovna') . ' ' .count($out_of_order_status_debug) . ' (' . implode(', ', $out_of_order_status_debug) . ')<br/>';
                    echo __('Orders skipped by package status:', 'zasilkovna') . ' ' .count($out_of_package_status_debug) . ' (' . implode(', ', $out_of_package_status_debug) . ')<br/>';
                }
                return;
            }

            $track_claim = false;
            $change_claim = false;
            if (!empty($zasilkovna_option['asistent']) && $zasilkovna_option['asistent'] == 'ok') {
                if (!empty($zasilkovna_option['asisten_track']) && $zasilkovna_option['asisten_track'] == 'ok') {
                    $track_claim = true;
                }
                if (!empty($zasilkovna_option['asisten_change']) && $zasilkovna_option['asisten_change'] == 'ok') {
                    $change_claim = true;
                }
            }

            $allowed_claim_change_statuses = array(9, 10, 15);
            $allowed_claim_change_statuses = apply_filters('zasilkovna_status_cron_claim_change_statuses', $allowed_claim_change_statuses);

            $allowed_claim_disable_standard_change_statuses = array(7);
            $allowed_claim_disable_standard_change_statuses = apply_filters('zasilkovna_status_cron_claim_disable_standard_change_statuses', $allowed_claim_disable_standard_change_statuses);

            foreach ($valid_ids as $order_id) {

                if ($track_claim) {
                    $ToretZasilkovna->Helper->GetPacketStatus($order_id, 'zasilkovna_order_claim_status', 'zasilkovna_barcode_assistent', false);
                }

                $ToretZasilkovna->Helper->GetPacketStatus($order_id, 'zasilkovna_order_status', 'zasilkovna_barcode', false);

                if (!empty($zasilkovna_option['status_change']) && $zasilkovna_option['status_change'] == 'ok') {

                    $packetaStatus = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_order_status');
                    $claimStatus = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_order_claim_status');
                    $claimStatus = explode(';', $claimStatus)[0];
                    $packetaStatus = explode(';', $packetaStatus)[0];

                    $standard_continue = true;
                    if (in_array($packetaStatus, $allowed_claim_disable_standard_change_statuses)) {
                        if ($claimStatus != 1) {
                            $standard_continue = false;
                        }
                    }

                    if ($change_claim) {
                        if ($claimStatus != '' && !empty($claimStatus)) {

                            if (in_array($claimStatus, $allowed_claim_change_statuses)) {
                                if (isset($zasilkovna_option['set_status'])) {
                                    if ($zasilkovna_option['set_status'][$claimStatus] != '') {
                                        $order = new WC_Order($order_id);
                                        $order->update_status(str_replace('wc-', '', $zasilkovna_option['set_status'][$claimStatus]), __('Packeta status change', 'zasilkovna'));
                                    }
                                }
                            } else if ($standard_continue) {
                                if ($packetaStatus != '' && !empty($packetaStatus)) {
                                    if (isset($zasilkovna_option['set_status'])) {
                                        if ($zasilkovna_option['set_status'][$packetaStatus] != '') {
                                            $order = new WC_Order($order_id);
                                            $order->update_status(str_replace('wc-', '', $zasilkovna_option['set_status'][$packetaStatus]), __('Packeta status change', 'zasilkovna'));
                                        }
                                    }
                                }
                            }
                        }else{
                            if ($packetaStatus != '' && !empty($packetaStatus)) {
                                if (isset($zasilkovna_option['set_status'])) {
                                    if ($zasilkovna_option['set_status'][$packetaStatus] != '') {
                                        $order = new WC_Order($order_id);
                                        $order->update_status(str_replace('wc-', '', $zasilkovna_option['set_status'][$packetaStatus]), __('Packeta status change', 'zasilkovna'));
                                    }
                                }
                            }
                        }
                    } else {
                        if ($packetaStatus != '' && !empty($packetaStatus)) {
                            if (isset($zasilkovna_option['set_status'])) {
                                if ($zasilkovna_option['set_status'][$packetaStatus] != '') {
                                    $order = new WC_Order($order_id);
                                    $order->update_status(str_replace('wc-', '', $zasilkovna_option['set_status'][$packetaStatus]), __('Packeta status change', 'zasilkovna'));
                                }
                            }
                        }
                    }
                }
            }

            if($manual){
                echo __('Statuses checked:', 'zasilkovna') . ' ' . count($order_ids_to_check) . '<br/>';
                echo __('Orders skipped by order status:', 'zasilkovna') . ' ' .count($out_of_order_status_debug) . ' (' . implode(', ', $out_of_order_status_debug) . ')<br/>';
                echo __('Orders skipped by package status:', 'zasilkovna') . ' ' .count($out_of_package_status_debug) . ' (' . implode(', ', $out_of_package_status_debug) . ')<br/>';
                echo __('Orders processed:', 'zasilkovna') . ' ' . count($valid_ids) . '<br/>';
            }
        }
    }
}


if (!function_exists('ToretZasilkovnaCron')) {
    function ToretZasilkovnaCron(): ToretZasilkovnaCron
    {
        return new ToretZasilkovnaCron();
    }
}