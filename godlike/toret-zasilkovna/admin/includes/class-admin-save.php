<?php


defined('ABSPATH') || exit;

/**
 * Admin Save Class
 */
if (!class_exists('Toret_Admin_save')) {


    class Toret_Admin_save
    {

        /**
         * Save all settings from admin
         */
        public static function save_setting(): void
        {
            if (isset($_POST['save_general_settings'])) {
                self::save_general();
            }

            if (isset($_POST['save_country_settings'])) {
                self::save_country_settings($_POST['save_country_settings']);
            }

            if (isset($_POST['save_tracking_settings'])) {
                self::save_tracking();
            }

            if (isset($_POST['save_auto_send_settings'])) {
                self::save_auto_send();
            }

            if (isset($_POST['save_exchange_rate_settings'])) {
                self::save_exchange_rate();
            }
        }

        /**
         * Save global settings
         */
        private static function save_general(): void
        {
            woo_zasilkovna_control_licence($_POST['zas_licence']);

            $zasilkovna_option = get_option('zasilkovna_option', array());

            if (!is_array($zasilkovna_option)) {
                $zasilkovna_option = [];
            }

            $fields = array(
                'api_password',
                'nazev_eshopu',
                'priplatek_dobirka',
                'zaokrouhleni_dobirka',
                'odeslani_zasilky',
                'doprava_zdarma',
                'free_coupon',
                'tzas_icon_custom_css',
                'tzas_icon_pickup_custom_css',
                'widget_position',
                'packeta_default_status',
                'packeta_services_default_status',
                'invoice_number_source',
                'invoice_date_source',
                'ref_text_before',
                'ref_text_after',
                'tzas_packet_note',
                'cod_tax_class',
                'zas_add_wrap_weight',
                'zas_default_weight',
                'multipackage_treshold_weight',
                'multipackage_treshold_longest',
                'multipackage_treshold_sum',
                'email_tracking_email_hook',
            );

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $zasilkovna_option[$field] = $_POST[$field];
                }
            }

            if (isset($_POST['multipackage_source'])) {
                $zasilkovna_option['multipackage_source'] = $_POST['multipackage_source'];
            }else{
                $zasilkovna_option['multipackage_source'] = [];
            }

            // Create API key from API password
            if (isset($_POST['api_password'])) {
                $zasilkovna_option['api_key'] = substr($_POST['api_password'], 0, 16);
            }

            $fields_checkbox = array(
                'tzas_show_icon',
                'tzas_show_pickup_icon',
                'error_email',
                'cod_point_check',
                'cod_round_hu',
                'asistent',
                'asisten_direct',
                'asisten_print',
                'asisten_track',
                'asisten_change',
                'change_shipping_address',
                'disable_popup_print',
                'tzas_modal_show_on_select',
                'tzas_hide_product_tabs',
                'checkout_point_js_check',
                'price_with_vat',
                'multipackage_enable',
                'multipackage_widget_nolimit',
                'pricelimit_reduce',
                'enableHDChecker',
                'forceHDChecker',
                'customs_en_as_orig',
                'disableLog',
                'branchLog',
            );

            foreach ($fields_checkbox as $field) {
                if (isset($_POST[$field]) && $_POST[$field] == 'ok') {
                    $zasilkovna_option[$field] = 'ok';
                } else {
                    $zasilkovna_option[$field] = '';
                }
            }

            update_option('zasilkovna_option', $zasilkovna_option);

            $fields_checkbox = array(
                'error_email',
            );

            foreach ($fields_checkbox as $field) {
                if (isset($_POST[$field]) && $_POST[$field] == 'email') {
                    $zasilkovna_option[$field] = 'email';
                } else {
                    $zasilkovna_option[$field] = '';
                }
            }

            update_option('zasilkovna_option', $zasilkovna_option);

            $fileds_option_checkbox = array(
                'tzas_show_icon',
                'tzas_show_pickup_icon',
            );
            foreach ($fileds_option_checkbox as $field) {
                if (isset($_POST[$field]) && $_POST[$field] == 'ok') {
                    update_option($field, 'ok');
                } else {
                    update_option($field, '');
                }
            }

            $fileds_option_checkbox = array(
                'tzas_icon_custom_css',
                'tzas_icon_pickup_custom_css',
            );
            foreach ($fileds_option_checkbox as $field) {
                if (isset($_POST[$field])) {
                    update_option($field, $_POST[$field]);
                }
            }
        }

        private static function save_country_settings($country)
        {
            $ToretZasilkovna = ToretZasilkovnaLib();

            $zasilkovna_services = get_option('zasilkovna_services', []);
            $zasilkovna_option = get_option('zasilkovna_option', []);
            $zasilkovna_prices = get_option('zasilkovna_prices', []);

            $currencies = ['CZK','EUR', 'USD', 'GBP', 'PLN', 'HUF', 'RON'];

            if (!is_array($zasilkovna_services)) {
                $zasilkovna_services = [];
            }

            if (!is_array($zasilkovna_option)) {
                $zasilkovna_option = [];
            }

            if (!is_array($zasilkovna_prices)) {
                $zasilkovna_prices = [];
            }

            $fields = array();

            foreach ($ToretZasilkovna->Helper->komplet_data() as $key => $data) {
                $stat = strtolower($data['stat']);
                if ($stat == strtolower($country)) {

                    /**
                     *
                     */
                    $slug_active = 'service-active-' . $key;
                    $old_active = $zasilkovna_services[$slug_active] ?? '';
                    if (isset($_POST['service-active-' . $key])) {
                        $zasilkovna_services['service-active-' . $key] = $_POST['service-active-' . $key];
                    } else {
                        $zasilkovna_services['service-active-' . $key] = '';
                    }

                    if (isset($_POST[$slug_active]) && empty($old_active) ) {
                        update_option('zasilkovna_services', $zasilkovna_services);
                        continue;
                    }

                    /**
                     *
                     */
                    if (isset($_POST['icon_url_' . $data['slug']])) {
                        $zasilkovna_option['icon_url_' . $data['slug']] = sanitize_text_field($_POST['icon_url_' . $data['slug']]);
                    }

                    if (isset($_POST['icon_select_url_' . $data['slug']])) {
                        $zasilkovna_option['icon_select_url_' . $data['slug']] = sanitize_text_field($_POST['icon_select_url_' . $data['slug']]);
                    }

                    /**
                     *
                     */
                    if (isset($_POST['service-label-' . $key])) {
                        $zasilkovna_services['service-label-' . $key] = $_POST['service-label-' . $key];
                        if (function_exists('icl_register_string')) {
                            icl_register_string('Zasilkovna', $zasilkovna_services['service-label-' . $key], $_POST['service-label-' . $key]);
                        }
                    }

                    /**
                     *
                     */
                    //$fields[] = $data['slug'] . '-hmo-' . strtolower($data['stat']);
                    $fields[] = $data['slug'] . '-hmd-' . strtolower($data['stat']);
                    $fields[] = $data['slug'] . '-cena-' . strtolower($data['stat']);

                    //$fields[] = $data['slug'] . '-dmo-' . strtolower($data['stat']);
                    $fields[] = $data['slug'] . '-dmd-' . strtolower($data['stat']);
                    $fields[] = $data['slug'] . '-cenadm-' . strtolower($data['stat']);

                    //$fields[] = $data['slug'] . '-pro-' . strtolower($data['stat']);
                    $fields[] = $data['slug'] . '-prd-' . strtolower($data['stat']);
                    $fields[] = $data['slug'] . '-cenapr-' . strtolower($data['stat']);

                    //$fields[] = $data['slug'] . '-feeo-' . strtolower($data['stat']);
                    $fields[] = $data['slug'] . '-feed-' . strtolower($data['stat']);
                    $fields[] = $data['slug'] . '-cenafee-' . strtolower($data['stat']);

                    $fields[] = $data['slug'] . '-celk';
                    $fields[] = $data['slug'] . '-hmotnost';
                    $fields[] = $data['slug'] . '-totalprice';
                    $fields[] = $data['slug'] . '-dim-check';
                    $fields[] = $data['slug'] . '-dim-check-box';
                    $fields[] = $data['slug'] . '-free-coupon';
                    $fields[] = $data['slug'] . '-dim-sum';
                    $fields[] = $data['slug'] . '-dim-one';
                    $fields[] = $data['slug'] . '-dim-one-l';
                    $fields[] = $data['slug'] . '-dim-one-h';
                    $fields[] = $data['slug'] . '-dim-one-w';
                    $fields[] = $data['slug'] . '-dobirka';
                    $fields[] = $data['slug'] . '-dobirka-max';
                    $fields[] = $data['slug'] . '-free';
                    $fields[] = $data['slug'] . '-free';
                    $fields[] = $data['slug'] . '-weight-type';
                    $fields[] = $data['slug'] . '-fee-type';


                    $fields[] = $data['slug'] . '-flr-enabled';
                    $fields[] = $data['slug'] . '-flr-cod-enabled';
                    foreach ($currencies as $currency) {
                        $fields[] = $data['slug'] . '-flr-' . $currency;
                        $fields[] = $data['slug'] . '-flr-cod-' . $currency;
                    }

                    $upper_limit_checks = [
                        'hmo' => [$data['slug'] . '-hmd-' . strtolower($data['stat']), 'hmd'],
                        'dmo' => [$data['slug'] . '-dmd-' . strtolower($data['stat']), 'dmd'],
                        'pro' => [$data['slug'] . '-prd-' . strtolower($data['stat']), 'prd'],
                        'feeo' => [$data['slug'] . '-feed-' . strtolower($data['stat']), 'feed'],
                    ];

                    $zasilkovna_prices = self::add_lower_limit($upper_limit_checks,$zasilkovna_prices);
                }
            }

            $stat = strtolower($country);
            foreach (TORET_ZASILKOVNA_NATIVE_TYPES as $native_type) {


                /**
                 *
                 */
                $slug_active = 'vydejnimista' . $native_type . '-active' . $stat;
                $old_active = $zasilkovna_services[$slug_active] ?? '';
                if (isset($_POST[$slug_active])) {
                    $zasilkovna_services[$slug_active] = $_POST[$slug_active];
                } else {
                    $zasilkovna_services[$slug_active] = '';
                }

                if (isset($_POST[$slug_active]) && empty($old_active) ) {
                    update_option('zasilkovna_services', $zasilkovna_services);
                    continue;
                }

                /**
                 *
                 */
                $slug_label = 'vydejnimista' . $native_type . $stat;
                if (isset($_POST[$slug_label])) {
                    $zasilkovna_services[$slug_label] = $_POST[$slug_label];
                    if (function_exists('icl_register_string')) {
                        icl_register_string('Zasilkovna', $zasilkovna_services[$slug_label], $_POST[$slug_label]);
                    }
                }


                /**
                 *
                 */
                if (isset($_POST['icon_url_' . $stat])) {
                    $zasilkovna_option['icon_url_' . $stat] = $_POST['icon_url_' . $stat];
                }
                if (isset($_POST['icon_select_url_' . $stat])) {
                    $zasilkovna_option['icon_select_url_' . $stat] = $_POST['icon_select_url_' . $stat];
                }

                /**
                 *
                 */
                //$fields[] = 'zasilkovna' . $native_type . '-hmo-' . $stat;
                $fields[] = 'zasilkovna' . $native_type . '-hmd-' . $stat;
                $fields[] = 'zasilkovna' . $native_type . '-cena-' . $stat;

                //$fields[] = 'zasilkovna' . $native_type . '-dmo-' . $stat;
                $fields[] = 'zasilkovna' . $native_type . '-dmd-' . $stat;
                $fields[] = 'zasilkovna' . $native_type . '-cenadm-' . $stat;

                //$fields[] = 'zasilkovna' . $native_type . '-pro-' . $stat;
                $fields[] = 'zasilkovna' . $native_type . '-prd-' . $stat;
                $fields[] = 'zasilkovna' . $native_type . '-cenapr-' . $stat;

                //$fields[] = 'zasilkovna' . $native_type . '-feeo-' . $stat;
                $fields[] = 'zasilkovna' . $native_type . '-feed-' . $stat;
                $fields[] = 'zasilkovna' . $native_type . '-cenafee-' . $stat;

                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-celk';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-hmotnost';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-totalprice';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-dim-check';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-dim-check-box';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-free-coupon';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-dim-one';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-dim-one-l';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-dim-one-w';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-dim-one-h';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-dim-sum';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-dobirka';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-dobirka-max';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-free';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-weight-type';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-fee-type';

                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-flr-' . 'enabled';
                $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-flr-cod-' . 'enabled';
                foreach ($currencies as $currency) {
                    $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-flr-' . $currency;
                    $fields[] = 'zasilkovna' . $native_type . '-' . $stat . '-flr-cod-' . $currency;
                }

                $upper_limit_checks =[
                    'hmo' => ['zasilkovna' . $native_type . '-hmd-' . $stat, 'hmd'],
                    'dmo' => ['zasilkovna' . $native_type . '-dmd-' . $stat, 'dmd'],
                    'pro' => ['zasilkovna' . $native_type . '-prd-' . $stat, 'prd'],
                    'feeo' => ['zasilkovna' . $native_type . '-feed-' . $stat, 'feed']
                ];

                $zasilkovna_prices = self::add_lower_limit($upper_limit_checks,$zasilkovna_prices);
            }

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $zasilkovna_prices[$field] = $_POST[$field];
                } else {
                    $zasilkovna_prices[$field] = "";
                }
            }

            $zasilkovna_option['povolene_staty'] = array_unique($ToretZasilkovna->Helper->get_povolene_staty());

            update_option('zasilkovna_services', $zasilkovna_services);
            update_option('zasilkovna_option', $zasilkovna_option);
            update_option('zasilkovna_prices', $zasilkovna_prices);
        }

        private static function save_tracking()
        {
            $zasilkovna_option = get_option('zasilkovna_option', array());

            if (!is_array($zasilkovna_option)) {
                $zasilkovna_option = [];
            }

            $fields_multiple = array(
                'zakazane_stavy',
                'zakazane_statusy',
            );

            foreach ($fields_multiple as $field) {
                if (isset($_POST[$field])) {
                    $zasilkovna_option[$field] = $_POST[$field];
                } else {
                    $zasilkovna_option[$field] = [];
                }
            }

            $fields = array(
                'status_limit',
                'status_days',
                'set_status',
            );

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $zasilkovna_option[$field] = $_POST[$field];
                }
            }

            $fields_checkbox = array(
                'status_change',
            );

            foreach ($fields_checkbox as $field) {
                if (isset($_POST[$field]) && $_POST[$field] == 'ok') {
                    $zasilkovna_option[$field] = 'ok';
                } else {
                    $zasilkovna_option[$field] = '';
                }
            }

            update_option('zasilkovna_option', $zasilkovna_option);
        }

        private static function save_auto_send()
        {
            $zasilkovna_send = get_option('zasilkovna_send', []);

            if (!is_array($zasilkovna_send)) {
                $zasilkovna_send = [];
            }

            $fields = array(
                'status'
            );

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $zasilkovna_send[$field] = $_POST[$field];
                }
            }

            update_option('zasilkovna_send', $zasilkovna_send);
        }

        private static function save_exchange_rate()
        {
            $zasilkovna_prices = get_option('zasilkovna_prices', array());

            if (!is_array($zasilkovna_prices)) {
                $zasilkovna_prices = array();
            }

            $fields = array(
                'kurz-czk',
                'kurz-euro',
                'kurz-forint',
                'kurz-zloty',
                'kurz-lei',
                'kurz-usd',
            );

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $zasilkovna_prices[$field] = $_POST[$field];
                }
            }

            update_option('zasilkovna_prices', $zasilkovna_prices);

            /**
             *
             */
            $country_currency = get_option('zasilkovna_country_currency', []);

            if (!is_array($country_currency)) {
                $country_currency = [];
            }

            $fields = array(
                'country_currency_bg',
                'country_currency_cz',
                'country_currency_dk',
                'country_currency_hu',
                'country_currency_de',
                'country_currency_pl',
                'country_currency_at',
                'country_currency_ro',
                'country_currency_sk',
                'country_currency_eu',
            );

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $country_currency[$field] = $_POST[$field];
                }
            }

            $fields_checkbox = array(
                'country_currency_deactivate',
            );

            foreach ($fields_checkbox as $field) {
                if (isset($_POST[$field]) && ($_POST[$field] == 'ok')) {
                    $country_currency[$field] = 'ok';
                } else {
                    $country_currency[$field] = "";
                }
            }

            update_option('zasilkovna_country_currency', $country_currency);
        }

        static function add_lower_limit($upper_limit_checks,$zasilkovna_prices) {
            foreach ($upper_limit_checks as $target => $upper_limit_check) {
                if (isset($_POST[$upper_limit_check[0]])) {
                    $upper_limits = $_POST[$upper_limit_check[0]];
                    $lower_limits = array();

                    $lower_limits[] = "0";

                    foreach ($upper_limits as $index => $value) {
                        if ($index > 0) {
                            $lower_limits[] = $upper_limits[$index - 1];
                        }
                    }

                    $zasilkovna_prices[str_replace($upper_limit_check[1], $target, $upper_limit_check[0])] = $lower_limits;
                }
            }
            return $zasilkovna_prices;
        }

    }


}