<?php


use ToretZasilkovna\Toret\Library\Dimensions;

defined('ABSPATH') || exit;

/**
 * Order review functions
 */
if (!class_exists('Toret_Zasilkovna_Bulk')) {
    class Toret_Zasilkovna_Bulk
    {

        /**
         * Add option to bulk actions
         */
        public function add_zasilkovna_bulk_action(array $actions): array
        {
            $actions['hromadny_tisk'] = __('Packeta - Print Labels', WOOZASILKOVNASLUG);
            $actions['dopravci_tisk'] = __('Packeta - Print Labels for Other Carriers', WOOZASILKOVNASLUG);
            $actions['claim_tisk'] = __('Packeta - Print Return Shipment Labels', WOOZASILKOVNASLUG);
            $actions['hromadne_odeslani'] = __('Packeta - Create Shipments', WOOZASILKOVNASLUG);

            return $actions;
        }

        /**
         * Bulk action handler
         */
        public function add_zasilkovna_handle_bulk_action_edit_shop_order($redirect_to, string $action, array $post_ids)
        {
            if ($action) {
                if ($action == 'hromadny_tisk' || $action == 'dopravci_tisk' || $action == 'hromadne_odeslani' || $action == 'claim_tisk') {
                    switch ($action) {
                        case 'hromadny_tisk' :
                            $redirect_to = self::bulk_multi_print($post_ids, $redirect_to);
                            break;
                        case 'dopravci_tisk' :
                            $redirect_to = self::bulk_services_print($post_ids, $redirect_to);
                            break;
                        case 'hromadne_odeslani' :
                            $redirect_to = self::bulk_packeta_send($post_ids, $redirect_to);
                            break;
                        case 'claim_tisk' :
                            $redirect_to = self::bulk_multi_print($post_ids, $redirect_to, true);
                            break;
                    }
                }
            }

            return $redirect_to;
        }

        /**
         * Add admin notice
         */
        public function add_zasilkovna_bulk_action_admin_notice(): void
        {
            if (!empty($_REQUEST['hromadny_tisk']) || !empty($_REQUEST['dopravci_tisk'])) {
                if (!empty($_REQUEST['error_code'])) {
                    switch ($_REQUEST['error_code']) {
                        case 'labelformat':
                            echo '<div id="message" class="error fade">
									<p>' . __('Wrong label format. Format', WOOZASILKOVNASLUG) . $_REQUEST['format'] . __(' not supported. <a href="https://docs.packetery.com/03-creating-packets/06-packetery-api-reference.html#toc-packetlabelpdf" target="_blank">Supported formats can be found here</a>.', WOOZASILKOVNASLUG) . '</p>
								 </div>';
                            break;
                        case 'labelformatser':
                            echo '<div id="message" class="error fade">
									<p>' . __('Wrong label format. Format', WOOZASILKOVNASLUG) . $_REQUEST['format'] . __(' not supported. <a href="https://docs.packetery.com/03-creating-packets/06-packetery-api-reference.html#toc-packetscourierlabelspdfhttps://docs.packetery.com/03-creating-packets/06-packetery-api-reference.html#toc-packetscourierlabelspdf" target="_blank">Supported formats can be found here</a>.', WOOZASILKOVNASLUG) . '</p>
								 </div>';
                            break;
                        case 'noid':
                            echo '<div id="message" class="error fade">
									<p>' . __('Selected orders do not have assigned parcel ID.', WOOZASILKOVNASLUG) . '</p>
								 </div>';
                            break;
                        case 'faultid':
                            echo '<div id="message" class="error fade">
									<p>' . __('Some of the parcels does not have valid parcel ID.', WOOZASILKOVNASLUG) . '</p>
								 </div>';
                            break;
                        case 'nointid':
                        case 'noextid':
                            echo '<div id="message" class="error fade">
									<p>' . __('None of the selected orders has Packeta label. Try to print carrier labels for these orders.', WOOZASILKOVNASLUG) . '</p>
								 </div>';
                            break;
                        case 'weightfault':
                            echo '<div id="message" class="error fade">
									<p>' . __('To download labels, the order\'s weight must be entered. If the data have been sent to Packeta, they have to be modified there.', WOOZASILKOVNASLUG) . '</p>
								 </div>';
                            break;
                        case 'NotSupportedFault':
                            echo '<div id="message" class="error fade">
									<p>' . __('Unknown error. Error text: ', WOOZASILKOVNASLUG) . $_REQUEST['message'] . '</p>
								 </div>';
                            break;
                        case 'extgate':
                            echo '<div id="message" class="error fade">
									<p>' . __('There has been an error in communication with the carrier. Please try later.', WOOZASILKOVNASLUG) . '</p>
								 </div>';
                            break;
                        case 'invCN':
                            echo '<div id="message" class="error fade">
									<p>' . __('Invalid carrier number.', WOOZASILKOVNASLUG) . '</p>
								 </div>';
                            break;
                    }
                }
            }
        }

        /**
         * print zasilkovna tickets
         */
        public function bulk_multi_print(array $post_ids, $redirect_to, $is_claim = false): string
        {
            if (empty($redirect_to)) {
                $redirect_to = admin_url('/edit.php?post_type=shop_order');
            }

            $url_components = parse_url($redirect_to);
            parse_str($url_components['query'], $params);

            $zasilkovna_option = get_option('zasilkovna_option', array());
            $print_claim_together = false;
            if (!empty($zasilkovna_option['asistent']) && $zasilkovna_option['asistent'] == 'ok' && !$is_claim) {
                if (!empty($zasilkovna_option['asisten_print']) && $zasilkovna_option['asisten_print'] == 'ok') {
                    $print_claim_together = true;
                }
            }

            $barcodes = array();

            foreach ($post_ids as $post_id) {


                if ($is_claim) {

                    $barcode = Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_barcode_assistent');
                    if ($barcode) {
                        $barcodes = array_merge($barcodes, explode(';', $barcode));
                    }

                } else {
                    $barcode = Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_barcode');
                    $doprava = Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_id_dopravy');
                    $service = tzas_get_service_from_string($doprava);

                    if ($service) {
                        if (tzas_is_native_method_bulk($service)) {
                            if ($barcode) {
                                $barcodes = array_merge($barcodes, explode(';', $barcode));
                            }
                        }
                        if (strpos($service, 'doruceni-na-adresu-hd') !== false) {
                            if ($barcode) {
                                $barcodes = array_merge($barcodes, explode(';', $barcode));
                            }
                        }
                        if (strpos($service, 'zasilkovna-vecerni-doruceni') !== false) {
                            if ($barcode) {
                                $barcodes = array_merge($barcodes, explode(';', $barcode));
                            }
                        }
                    }

                    if ($print_claim_together) {
                        $barcode_claim = Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_barcode_assistent');
                        if ($barcode_claim) {
                            $barcodes = array_merge($barcodes, explode(';', $barcode_claim));
                        }
                    }
                }
            }

            $apiPassword = $zasilkovna_option['api_password'];

            $gw = new SoapClient("https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl");
            if (!empty($barcodes)) {
                try {
                    $packetId = $barcodes;
                    $format = apply_filters('zasilkovna_group_print_format', str_replace('-', ' ', $params['format']));
                    $packet = $gw->packetsLabelsPdf($apiPassword, $packetId, $format, $params['offset']);

                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename=ticket-' . end($barcodes) . '-' . $barcodes[0] . '.pdf');

                    echo $packet;
                } catch (SoapFault $e) {

                    $detail = $e->detail;
                    if (!empty($detail)) {
                        $error = key($e->detail);
                    } else {
                        $error = $e->getMessage();
                    }

                    switch ($error) {
                        case 'UnknownLabelFormatFault':

                            $format = apply_filters('zasilkovna_group_print_format', "A7 on A7");
                            return add_query_arg(array(
                                'hromadny_tisk' => '1',
                                'error_code' => 'labelformat',
                                'format' => $format
                            ), $redirect_to);
                        case 'NoPacketIdsFault':
                            return add_query_arg(array(
                                'hromadny_tisk' => '1',
                                'error_code' => 'noid'
                            ), $redirect_to);
                        default:
                            return add_query_arg(array(
                                'hromadny_tisk' => '1',
                                'error_code' => 'faultid'
                            ), $redirect_to);
                    }
                }
                die();
            }

            $redirect_to = add_query_arg(array(
                'hromadny_tisk' => '1',
                'error_code' => 'noextid'
            ), $redirect_to);

            $redirect_to = remove_query_arg(array(
                'offset', 'format'
            ), $redirect_to);

            wp_redirect($redirect_to);

            return $redirect_to;
        }

        /**
         * print services ticket
         */
        public function bulk_services_print(array $post_ids, $redirect_to): string
        {
            if (empty($redirect_to)) {
                $redirect_to = admin_url('/edit.php?post_type=shop_order');
            }

            $zasilkovna_option = get_option('zasilkovna_option');
            $apiPassword = $zasilkovna_option['api_password'];

            $url_components = parse_url($redirect_to);
            parse_str($url_components['query'], $params);

            $barcodes = array();

            foreach ($post_ids as $post_id) {
                $barcode = Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_barcode', true);
                $doprava = Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_id_dopravy', true);
                $service = tzas_get_service_from_string($doprava);
                $id_baliku_dopravce = Toret_HPOS_Compatibility::get_order_meta($post_id, 'zasilkovna_id_zasilky_dopravce', true);

                if ($service) {
                    if (tzas_is_zasilkovna_shipping($doprava)) {
                        if ($barcode) {
                            $bh = explode(';', $barcode);
                            $ih = explode(';', $id_baliku_dopravce);
                            foreach ($bh as $index => $b) {
                                $barcodes[] = array('packetId' => $b, 'courierNumber' => $ih[$index]);
                            }
                        }
                    }
                }
            }

            if (!empty($barcodes)) {
                try {
                    $packetId = $barcodes;
                    $format = apply_filters('zasilkovna_group_print_services_format', str_replace('-', ' ', $params['format']));
                    $gw = new SoapClient("https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl");

                    $packet = $gw->packetsCourierLabelsPdf($apiPassword, $packetId, $params['offset'], $format);
                    $last = end($barcodes);

                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename=ticket-' . $last['packetId'] . '-' . $barcodes[0]['packetId'] . '.pdf');

                    echo $packet;
                } catch (SoapFault $e) {

                    $error = key($e->detail) ?? $e->faultstring;

                    switch ($error) {
                        case 'NotSupportedFault':
                            $errorString = $e->faultstring;
                            if (strpos($errorString, 'Packet weight is not entered') !== false) {

                                return add_query_arg(array(
                                    'hromadny_tisk' => '1',
                                    'error_code' => 'weightfault',
                                ), $redirect_to);
                            } else {
                                return add_query_arg(array(
                                    'hromadny_tisk' => '1',
                                    'error_code' => 'NotSupportedFault',
                                    'message' => $errorString
                                ), $redirect_to);
                            }
                        case 'Unknown label format':
                            $format = apply_filters('zasilkovna_group_print_services_format', "A6 on A4");
                            return add_query_arg(array(
                                'hromadny_tisk' => '1',
                                'error_code' => 'labelformatser',
                                'format' => $format
                            ), $redirect_to);
                        case 'NoPacketIdsFault':
                            return add_query_arg(array(
                                'hromadny_tisk' => '1',
                                'error_code' => 'noid'
                            ), $redirect_to);
                        case 'PacketIdFault':
                            return add_query_arg(array(
                                'hromadny_tisk' => '1',
                                'error_code' => 'faultid'
                            ), $redirect_to);
                        case 'ExternalGatewayFault':
                            return add_query_arg(array(
                                'hromadny_tisk' => '1',
                                'error_code' => 'extgate'
                            ), $redirect_to);
                        case 'InvalidCourierNumber':
                            return add_query_arg(array(
                                'hromadny_tisk' => '1',
                                'error_code' => 'invCN'
                            ), $redirect_to);
                    }

                }
                die();
            }

            $redirect_to = add_query_arg(array(
                'hromadny_tisk' => '1',
                'error_code' => 'nointid'
            ), $redirect_to);

            $redirect_to = remove_query_arg(array(
                'offset', 'format'
            ), $redirect_to);


            wp_redirect($redirect_to);

            return $redirect_to;
        }

        /**
         * print services ticket
         */
        public function bulk_packeta_send(array $post_ids, $redirect_to): string
        {
            if (empty($redirect_to)) {
                $redirect_to = admin_url('/edit.php?post_type=shop_order');
            }

            sort($post_ids);
            $batch_key = 'packeta_bulk_lock_' . md5(implode(',', $post_ids));

            if (get_transient($batch_key)) {
                wp_redirect($redirect_to);
                return $redirect_to;
            }

            set_transient($batch_key, true, 5);

            $ToretZasilkovna = ToretZasilkovnaLib();

            foreach ($post_ids as $postID) {

                $zasilkovna_id = Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_id_pobocky', true);

                if (!empty($zasilkovna_id)) {

                    if (Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_status', true) != 'ok') {

                        $vaha = ToretZasilkovnaDimensionHelper::get_zasilkovna_weight($postID);

                        $zasilkovna_shipping = Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_id_dopravy', true);
                        $zasilkovna_service = tzas_get_service_from_string($zasilkovna_shipping);

                        $rozmery = 0;
                        $shippingID = $rozmery_data = '';
                        if ($zasilkovna_shipping) {
                            $komplet_data = $ToretZasilkovna->Helper->komplet_data();
                            if (tzas_is_native_method($zasilkovna_service)) {
                                $shippingID = (Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_carrierId') != 'undefined'
                                    ? Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_carrierId', true)
                                    : 0);
                            }
                            if ($shippingID !== '') {
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
                                    $rozmery_data = Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_custom_dimension', true) ?: '';
                                }
                            }
                            if ($rozmery > 0) {
                                $rozmery_data = Toret_HPOS_Compatibility::get_order_meta($postID, 'zasilkovna_custom_dimension', true) ?: '';
                            }
                        }

                        $qty = 1;
                        $order = wc_get_order($postID);
                        $zasilkovna_option = get_option('zasilkovna_option', array());
                        $max_dim = Dimensions::get_order_max_dimension($order, false, true);
                        $max_dim_sum = Dimensions::get_order_max_sides_sum($order, false, true);
                        $multipackage_data = $ToretZasilkovna->Helper->get_multipackage_data($zasilkovna_option, $vaha, $max_dim, $max_dim_sum);
                        if ($multipackage_data['enabled']) {
                            $qty = $multipackage_data['qty'];
                        }

                        $return = array();
                        if ($rozmery > 0) {
                            if (($rozmery_data != '') && ($vaha > 0)) {
                                $return = $ToretZasilkovna->Send->send_ticket($postID, $qty, false, true);
                            }
                        } else {
                            if ($vaha > 0) {
                                $return = $ToretZasilkovna->Send->send_ticket($postID, $qty, false, true);
                            }
                        }

                        if (in_array('error', $return)) {
                            $redirect_to = add_query_arg(['packeta_bulk_finished' => 'senderror'], $redirect_to);
                        } else {
                            $redirect_to = add_query_arg(['packeta_bulk_finished' => 'sendok'], $redirect_to);
                        }
                    }
                }
            }

            wp_redirect($redirect_to);

            return $redirect_to;
        }
    }
}