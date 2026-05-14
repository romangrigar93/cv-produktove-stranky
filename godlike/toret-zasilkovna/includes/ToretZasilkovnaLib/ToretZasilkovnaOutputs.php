<?php
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
class ToretZasilkovnaOutputs
{

    /**
     * create table to thankyou page
     */
    public static function customer_order_info_table(int $order_id, string $zasilkovna_shipping): string
    {
        $name = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_name', true);
        $place = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_place', true);
        $street = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_street', true);
        $city = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_city', true);
        $zip = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_zip', true);
        $url = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_url', true);
        $lat = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_gps_lat', true);
        $lon = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_gps_lon', true);

        $slug = tzas_get_service_from_string($zasilkovna_shipping);

        if (tzas_is_native_method($slug)) {
            $label = __('Packeta - pick up point: ', 'zasilkovna');
        } else {
            $ToretZasilkovna = ToretZasilkovnaLib();
            $shipping_data = $ToretZasilkovna->Helper->GetServiceBySlug($slug);
            $zasilkovna_services = get_option('zasilkovna_services');
            $label = $zasilkovna_services['service-label-' . $shipping_data['key']];
        }


        $html = '<tr>
						<th colspan="2">' . $label . '</th>
					</tr>
					<tr>
						<th>' . __('Name: ', 'zasilkovna') . '</th>
						<td>' . ($name != '' ? $name : '') . '</td>
					</tr>
					<tr>
						<th>' . __('Place: ', 'zasilkovna') . '</th>
						<td>' . ($place != '' ? $place : '') . '</td>
					</tr>
					<tr>
						<th>' . __('Street: ', 'zasilkovna') . '</th>
						<td>' . ($street != '' ? $street : '') . '</td>
					</tr>
					<tr>
						<th>' . __('City: ', 'zasilkovna') . '</th>
						<td>' . ($city != '' ? $city : '') . '</td>
					</tr>
					<tr>
						<th>' . __('ZIP code: ', 'zasilkovna') . '</th>
						<td>' . ($zip != '' ? $zip : '') . '</td>
					</tr>';
        return apply_filters('zasilkovna_vystup_html', $html, $order_id);
    }

    /**
     * create tatble to email
     */
    public static function customer_email_info(int $order_id, string $zasilkovna_shipping): string
    {
        $html = '';

        if ($zasilkovna_shipping != '') {

            $slug = tzas_get_service_from_string($zasilkovna_shipping);

            if (tzas_is_native_method($slug)) {
                $label = __('Packeta - pick up point: ', 'zasilkovna');
            } else {
                $ToretZasilkovna = ToretZasilkovnaLib();
                $shipping_data = $ToretZasilkovna->Helper->GetServiceBySlug($slug);

                $zasilkovna_services = get_option('zasilkovna_services');
                $label = $zasilkovna_services['service-label-' . $shipping_data['key']];
            }

            $name = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_name', true);
            $place = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_place', true);
            $street = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_street', true);
            $city = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_city', true);
            $zip = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_zip', true);
            $url = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_url', true);
            $lat = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_gps_lat', true);
            $lon = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_gps_lon', true);

            $html = '<p><strong>' . $label . ' </strong><br />';
            $html .= ($name != '' ? $name : '') . '<br />
                 ' . ($place != '' ? $place : '') . '<br />
                 ' . ($street != '' ? $street : '') . '<br />
                 ' . ($city != '' ? $city : '') . '<br />
                 ' . ($zip != '' ? $zip : '') . '<br />';

            if (strpos($url, 'https') !== false) {
                $html .= '<a href="' . $url . '" target="_blank" class="button">' . __('Show location details', 'zasilkovna') . '</a></br>';
            } else {
                if ($lat != '' && $lon != '') {
                    $html .= '<a href="https://www.google.com/maps/place/' . $lat . ',' . $lon . '" target="_blank" class="button">' . __('Show location details', 'zasilkovna') . '</a></br>';
                }
            }

            $html .= '</p>';

        }

        return apply_filters('zasilkovna_vystup_html_email', $html, $order_id);
    }

    /**
     * Create table to order detail
     */
    public static function admin_customer_order_info(int $order_id, string $zasilkovna_shipping): string
    {
        $html = '';

        if ($zasilkovna_shipping != '') {

            $zasilkovna_service = tzas_get_service_from_string($zasilkovna_shipping);

            if (empty($zasilkovna_service))
                return '';

            if (tzas_is_native_method($zasilkovna_service)) {
                $label = __('Packeta - pick up point: ', 'zasilkovna');
            } else {
                $ToretZasilkovna = ToretZasilkovnaLib();
                $shipping_data = $ToretZasilkovna->Helper->GetServiceBySlug($zasilkovna_service);
                $zasilkovna_services = get_option('zasilkovna_services');
                $label = $zasilkovna_services['service-label-' . $shipping_data['key']];
            }

            $name = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_name', true);
            $place = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_place', true);
            $street = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_street', true);
            $city = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_city', true);
            $zip = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_zip', true);
            $url = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_url', true);
            $lat = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_gps_lat', true);
            $lon = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_gps_lon', true);

            $html = '<strong>' . $label . '</strong><br />';
            $html .= '<strong>' . __('Name: ', 'zasilkovna') . '</strong>: ';
            $html .= '' . ($name != '' ? $name : '') . '<br />';
            $html .= '<strong>' . __('Place: ', 'zasilkovna') . '</strong>: ';
            $html .= '' . ($place != '' ? $place : '') . '<br />';
            $html .= '<strong>' . __('Street: ', 'zasilkovna') . '</strong>: ';
            $html .= '' . ($street != '' ? $street : '') . '<br />';
            $html .= '<strong>' . __('City: ', 'zasilkovna') . '</strong>: ';
            $html .= '' . ($city != '' ? $city : '') . '<br />';
            $html .= '<strong>' . __('ZIP code: ', 'zasilkovna') . '</strong>: ';
            $html .= '' . ($zip != '' ? $zip : '') . '<br />';

            if (strpos($url, 'https') !== false) {
                $html .= '<a href="' . $url . '" target="_blank" class="button">' . __('Show location details', 'zasilkovna') . '</a></br>';
            } else {
                if ($lat != '' && $lon != '') {
                    $html .= '<a href="https://www.google.com/maps/place/' . $lat . ',' . $lon . '" target="_blank" class="button">' . __('Show location details', 'zasilkovna') . '</a></br>';
                }
            }
        }

        return $html;
    }

    /**
     * create tracking link to email
     */
    public static function sledovani_link_gathered(int $order_id, $barcode): string
    {
        if ($barcode != '') {
            $order = wc_get_order($order_id);

            if (!$order) {
                return '';
            }

            $ToretZasilkovna = ToretZasilkovnaLib();
            $locale = $ToretZasilkovna->Helper->get_language_by_country($order->get_shipping_country());

            $html = '<a class="zasilkovna-sledovani" href="https://tracking.packeta.com/' . $locale . '/?id=' . $barcode . '" target="_blank" class="button">' . $barcode . '</a>';

            return apply_filters('zasilkovna-sledovani-link', $html, $barcode, $order_id, "https://tracking.packeta.com/" . $locale . "/?id=" . $barcode);
        } else {
            return '';
        }
    }

    /**
     * return packet status
     */
    public static function packetStatus(int $order_id): string
    {
        $gw = new SoapClient("https://www.zasilkovna.cz/api/soap-php-bugfix.wsdl");
        $zasilkovna_option = get_option('zasilkovna_option');
        $apiPassword = $zasilkovna_option['api_password'];

        $stavy = ToretZasilkovnaHelper::zasilkovna_statuses();

        try {
            $packetId = Toret_HPOS_Compatibility::get_order_meta($order_id, 'zasilkovna_barcode', true);
            $packet = $gw->packetStatus($apiPassword, $packetId);

            return $stavy[$packet->statusCode] ?? __('Failed to retrieve package status', 'zasilkovna');
        } catch (SoapFault $e) {
            zasilkovna_log(array(
                    'order_id' => $order_id,
                    'log' => __('Failed to retrieve package status', TORETZASILKOVNASLUG) . '. ' . $e->getMessage(),
                    'context' => __('Package status', 'zasilkovna')
            ));
            return __('Failed to retrieve package status', 'zasilkovna');
        }

    }

    /**
     * Show error notice
     */
    function show_admin_notice_error($msg, $order_id, $log_url, $slug, $check = false): void
    {
        add_action('admin_notices', function () use ($msg, $order_id, $log_url, $slug, $check) {
            if ($msg && $check) {

                if ($order_id != null) {
                    $url = admin_url() . 'admin.php?page=' . $log_url . '&order_id=' . $order_id;
                } else {
                    $url = admin_url() . 'admin.php?page=' . $log_url;
                }

                ?>
                <div id="message" class="notice notice-error"><p><?php echo $msg . ' '; ?><a
                                href="<?php echo $url; ?>"
                                target="_blank"><?php _e('Check log for details.', $slug); ?></a></p>
                </div>;
                <?php

            } else {

                ?>
                <div id="message" class="notice notice-error"><p><?php echo $msg; ?></p>
                </div>;
                <?php
            }
        });
    }

    /**
     * Show success notice
     */
    function show_admin_notice_success($msg, $order_id, $log_url, $slug): void
    {
        add_action('admin_notices', function () use ($msg, $order_id, $log_url, $slug) {
            if ($order_id != null) {
                $url = admin_url() . 'admin.php?page=' . $log_url . '&order_id=' . $order_id;
            } else {
                $url = admin_url() . 'admin.php?page=' . $log_url;
            }
            ?>
            <div id="message" class="notice notice-success"><p><?php echo $msg . ' '; ?><a
                            href="<?php echo $url; ?>"
                            target="_blank"><?php _e('Check log for details.', $slug); ?></a></p>
            </div>;
            <?php
        });
    }

    /**
     * Show success notice
     */
    function show_admin_notice_warning($msg, $log_url, $slug): void
    {
        add_action('admin_notices', function () use ($msg, $log_url, $slug) {
            ?>
            <div id="message" class="notice notice-warning"><p><?php echo $msg . ' '; ?><a
                            href="<?php echo admin_url() . 'admin.php?page=' . $log_url; ?>"
                            target="_blank"><?php _e('Check log for details.', $slug); ?></a></p>
            </div>;
            <?php
        });
    }
}