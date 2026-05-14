<?php
defined('ABSPATH') || exit;

/**
 * Order review functions
 */
if (!class_exists('Toret_Zasilkovna_Admin_Send')) {
    class Toret_Zasilkovna_Admin_Send
    {

        /**
         * Send ticket or download packet
         */
        public function send_ticket(): void
        {
            $ToretZasilkovna = ToretZasilkovnaLib();
            if (!empty($_GET['zasilkovna_id_objednavky'])) {
                $package_count = $_GET['package_count'] ?? 1;
                $ToretZasilkovna->Send->send_ticket($_GET['zasilkovna_id_objednavky'], $package_count, true);
            }

            if (!empty($_GET['zasilkovna_ticket_id'])) {
                self::send_ticket_click($_GET['zasilkovna_order_id']);
            }

            if (!empty($_GET['zasilkovna_id_objednavky_assistent'])) {
                $ToretZasilkovna->Claim->send_ticket($_GET['zasilkovna_id_objednavky_assistent']);
            }

            if (!empty($_GET['zasilkovna_ticket_id_assistent'])) {
                self::send_ticket_assistent($_GET['zasilkovna_order_id']);
            }


            if (!empty($_GET['packetka_cancel_finished'])) {
                (new ToretZasilkovnaOutputs)->show_admin_notice_warning(__('Package(s) cancel finished.'), TORETZASILKOVNALOGSLUG, 'zasilkovna', true);
            }

            if (!empty($_GET['packetka_delete_finished'])) {
                (new ToretZasilkovnaOutputs)->show_admin_notice_warning(__('Package(s) delete finished.'), TORETZASILKOVNALOGSLUG, 'zasilkovna', true);
            }

            if (!empty($_GET['packeta_bulk_finished'])) {
                if($_GET['packeta_bulk_finished'] == 'senderror') {
                    (new ToretZasilkovnaOutputs)->show_admin_notice_error(__('Package(s) send finished with errors.'), null,TORETZASILKOVNALOGSLUG, 'zasilkovna',true);
                }elseif($_GET['packeta_bulk_finished'] == 'sendok') {
                    (new ToretZasilkovnaOutputs)->show_admin_notice_success(__('Package(s) send finished.'), null,TORETZASILKOVNALOGSLUG, 'zasilkovna');
                }
            }
        }

        /**
         * Send ticket
         */
        public function send_ticket_click($order_id = ''): void
        {
            $gw = new SoapClient("https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl");

            $zasilkovna_option = get_option('zasilkovna_option');
            $apiPassword = $zasilkovna_option['api_password'];

            $id_baliku_dopravce = (!empty($_GET['id_baliku_dopravce']) ? $_GET['id_baliku_dopravce'] : '');

            $doprava = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_dopravy');
            $id_dopravy = tzas_get_service_from_string($doprava);

            if ($id_baliku_dopravce != '') {
                try {
                    $packetIds = explode(';', $_GET['zasilkovna_ticket_id']);
                    $ids_baliku_dopravce = explode(';', $id_baliku_dopravce);

                    $packetId = [];
                    foreach ($packetIds as $index => $id) {
                        $packetId[] = array(
                            'packetId' => $id,
                            'courierNumber' => $ids_baliku_dopravce[$index]
                        );
                    }

                    $barcodes = explode(';', Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode'));

                    $offset = $_GET['offset'] ?? 0;
                    $format = $_GET['format'] ?? ($zasilkovna_option['packeta_services_default_status'] ?? "A6 on A4");
                    $format = str_replace('-', ' ', $format);
                    $format = apply_filters('toret_zasilkovna_ticket_format', $format, $order_id, $id_dopravy);

                    $packet = $gw->packetsCourierLabelsPdf($apiPassword, $packetId, $offset, $format);

                    if (count($barcodes) > 1) {
                        $filename = 'ticket-' . end($barcodes) . '-' . $barcodes[0] . '.pdf';
                    } else {
                        $filename = 'ticket-' . $barcodes[0] . '.pdf';
                    }

                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);

                    echo $packet;
                    exit();

                } catch (SoapFault $e) {
                    $error = (is_array($e->detail) && !empty($e->detail))
                        ? key($e->detail)
                        : ($e->faultstring ?? __('Unknown error.', WOOZASILKOVNASLUG));

                    self::add_zasilkovna_single_action_admin_notice($error);
                }
            } else {
                try {
                    $packetId = explode(';', $_GET['zasilkovna_ticket_id']);
                    $barcodes = explode(';', Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode'));

                    $packetId = self::add_claim_packet_ids($order_id, $packetId);
                    $barcodes = self::add_claim_barcodes($order_id, $barcodes);

                    $offset = $_GET['offset'] ?? 0;
                    $format = $_GET['format'] ?? ($zasilkovna_option['packeta_default_status'] ?? "A7 on A7");
                    $format = str_replace('-', ' ', $format);
                    $format = apply_filters('toret_zasilkovna_ticket_format', $format, $order_id, $id_dopravy);

                    $packet = $gw->packetsLabelsPdf($apiPassword, $packetId, $format, $offset);

                    if (count($barcodes) > 1) {
                        $filename = 'ticket-' . end($barcodes) . '-' . $barcodes[0] . '.pdf';
                    } else {
                        $filename = 'ticket-' . $barcodes[0] . '.pdf';
                    }

                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $filename);

                    echo $packet;
                    exit();
                } catch (SoapFault $e) {
                    $error = (is_array($e->detail) && !empty($e->detail))
                        ? key($e->detail)
                        : ($e->faultstring ?? __('Unknown error.', WOOZASILKOVNASLUG));

                    self::add_zasilkovna_single_action_admin_notice($error);
                }
            }
        }

        function add_claim_barcodes($order_id, $barcodes)
        {
            $zasilkovna_option = get_option('zasilkovna_option', array());
            if (!empty($zasilkovna_option['asistent']) && $zasilkovna_option['asistent'] == 'ok') {
                if (!empty($zasilkovna_option['asisten_print']) && $zasilkovna_option['asisten_print'] == 'ok') {
                    $barcodes = array_merge($barcodes, explode(';', Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_id_zasilky_assistent', true)));
                }
            }

            return $barcodes;
        }

        function add_claim_packet_ids($order_id, $packet_ids)
        {
            $zasilkovna_option = get_option('zasilkovna_option', array());
            if (!empty($zasilkovna_option['asistent']) && $zasilkovna_option['asistent'] == 'ok') {
                if (!empty($zasilkovna_option['asisten_print']) && $zasilkovna_option['asisten_print'] == 'ok') {
                    $packet_ids = array_merge($packet_ids, explode(';', Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode_assistent', true)));
                }
            }

            return $packet_ids;
        }

        /**
         * download packet
         */
        public function send_ticket_assistent($order_id): void
        {
            $gw = new SoapClient("https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl");
            $zasilkovna_option = get_option('zasilkovna_option');
            $apiPassword = $zasilkovna_option['api_password'];

            try {
                $packetId = explode(';', $_GET['zasilkovna_ticket_id_assistent']);
                $barcodes = explode(';', Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode_assistent', true));

                $format = "A7 on A7";
                $offset = 0;
                $format = apply_filters('toret_zasilkovna_claim_ticket_format', $format, $order_id);

                $packet = $gw->packetsLabelsPdf($apiPassword, $packetId, $format, $offset);

                if (count($barcodes) > 1) {
                    $filename = 'ticket-' . end($barcodes) . '-' . $barcodes[0] . '.pdf';
                } else {
                    $filename = 'ticket-' . $barcodes[0] . '.pdf';
                }

                header('Content-type: application/pdf');
                header('Content-Disposition: attachment; filename=' . $filename);

                echo $packet;
                exit();
            } catch (SoapFault $e) {
                $error = (is_array($e->detail) && !empty($e->detail))
                    ? key($e->detail)
                    : ($e->faultstring ?? __('Unknown error.', WOOZASILKOVNASLUG));

                self::add_zasilkovna_single_action_admin_notice($error);
            }
        }

        /**
         * Add admin notice
         */
        public function add_zasilkovna_single_action_admin_notice(string $error): void
        {
            if ($error) {
                switch ($error) {
                    case 'PacketCanceled':
                        echo '<div id="message" class="success fade">
								<p>' . __("Packet canceled.", WOOZASILKOVNASLUG) . '</p>
							 </div>';
                        break;
                    case 'CancelNotAllowedFault':
                        echo '<div id="message" class="error fade">
								<p>' . __("Packet state does not allow this operation.", WOOZASILKOVNASLUG) . '</p>
							 </div>';
                        break;
                    case 'NoPacketIdsFault':
                        echo '<div id="message" class="error fade">
								<p>' . __('Selected order does not have assigned parcel ID.', WOOZASILKOVNASLUG) . '</p>
							 </div>';
                        break;
                    case 'PacketIdFault':
                        echo '<div id="message" class="error fade">
								<p>' . __('Parcel ID not valid.', WOOZASILKOVNASLUG) . '</p>
							 </div>';
                        break;
                    case 'ExternalGatewayFault':
                        echo '<div id="message" class="error fade">
								<p>' . __('There has been an error in communication with the carrier. Please try later.', WOOZASILKOVNASLUG) . '</p>
							 </div>';
                        break;
                    case 'InvalidCourierNumber':
                        echo '<div id="message" class="error fade">
								<p>' . __('Invalid carrier number.', WOOZASILKOVNASLUG) . '</p>
							 </div>';
                        break;
                    case 'UnknownLabelFormatFault':
                        echo '<div id="message" class="error fade">
								<p>' . __('Wrong label format.', WOOZASILKOVNASLUG) . '</p>
							 </div>';
                        break;
                    case 'NotSupportedFault':
                        echo '<div id="message" class="error fade">
								<p>' . __('Unknown error.', WOOZASILKOVNASLUG) . '</p>
							 </div>';
                        break;
                }
            }
        }
    }
}