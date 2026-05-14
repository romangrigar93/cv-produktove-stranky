<?php
function tzas_get_shipping_base_from_string($text)
{
    $array = explode('>', $text);
    $zasilkovna_shipping = $array[0];
    if ($zasilkovna_shipping == 'zasilkovna') {
        return $zasilkovna_shipping;
    }
    return false;
}

function tzas_get_service_from_string($text)
{
    $array = explode('>', $text);
    $zasilkovna_shipping = $array[0];
    if ($zasilkovna_shipping == 'zasilkovna') {
        $zasilkovna_service = $array[1];
        return explode(':', $zasilkovna_service)[0];
    }
    return false;
}

function tzas_is_zasilkovna_shipping($method)
{
    if (!empty($method)) {
        $zasilkovna_shipping = explode('>', $method)[0];
        if ($zasilkovna_shipping == 'zasilkovna') {
            return true;
        }
    }
    return false;
}

function tzas_get_native_slug_from_service($service)
{
    foreach (TORET_ZASILKOVNA_NATIVE_SHIPPINGS as $item) {
        if (strpos($service, $item) !== false) {
            return array_search($item, TORET_ZASILKOVNA_NATIVE_SHIPPINGS);
        }
    }
    return false;
}

function tzas_get_service_from_cart()
{
    if (isset(WC()->session)) {
        if (isset(WC()->session->chosen_shipping_methods[0])) {
            $doprava_name = explode('>', WC()->session->chosen_shipping_methods[0]);
            $zasilkovna_shipping = $doprava_name[0];
            if ($zasilkovna_shipping == 'zasilkovna') {
                $zasilkovna_service = $doprava_name[1];
                return explode(':', $zasilkovna_service)[0];
            }
        }
    }
    return false;
}

function tzas_get_shipping_from_cart()
{
    if (isset(WC()->session)) {
        $methods = wc_get_chosen_shipping_method_ids();
        if (!empty($methods)) {
            return $methods[0];
        }
    }
    return false;
}

function tzas_get_native_label($native_type)
{
    return __('Packeta', WOOZASILKOVNASLUG);
}


function tzas_is_native_pickup_method($method)
{
    $native_types = array('packeta-zpoints', 'packeta-zbox');

    $all_native_methods = array();
    foreach (TORET_ZASILKOVNA_NATIVE_COUNTRIES as $NATIVE_COUNTRY) {
        foreach ($native_types as $native_type) {
            $all_native_methods[] = $native_type . '-' . $NATIVE_COUNTRY;
        }
    }
    $all_native_methods[] = 'z-points';

    return in_array($method, $all_native_methods);
}


function tzas_is_native_method($method)
{
    $all_native_methods[] = 'z-points';

    return in_array($method, $all_native_methods);
}


function tzas_is_native_method_bulk($method): bool
{
    return (strpos($method, 'z-points') !== false || strpos($method, 'zpoints') !== false || strpos($method, 'zbox') !== false );
}

function tzas_is_native_zpoint_method($method)
{
    $native_types = array('packeta-zpoints');

    $all_native_methods = array();
    foreach (TORET_ZASILKOVNA_NATIVE_COUNTRIES as $NATIVE_COUNTRY) {
        foreach ($native_types as $native_type) {
            $all_native_methods[] = $native_type . '-' . $NATIVE_COUNTRY;
        }
    }

    return in_array($method, $all_native_methods);
}

function tzas_is_native_zbox_method($method)
{
    $native_types = array('packeta-zbox');

    $all_native_methods = array();
    foreach (TORET_ZASILKOVNA_NATIVE_COUNTRIES as $NATIVE_COUNTRY) {
        foreach ($native_types as $native_type) {
            $all_native_methods[] = $native_type . '-' . $NATIVE_COUNTRY;
        }
    }

    return in_array($method, $all_native_methods);
}

if (!function_exists('toret_get_customer_country')) {
    function toret_get_customer_country()
    {
        if (!WC()->customer) {
            $store_raw_country = get_option('woocommerce_default_country');
            $split_country = explode(":", $store_raw_country);
            if (!empty($split_country)) {
                return $split_country[0];
            }
        } else {
            $country = WC()->customer->get_shipping_country();
            if (empty($country)) {

                $country = WC()->customer->get_billing_country();

            }
            return $country;
        }
        return false;
    }
}

/**
 * Add native Packeta method to carriers table
 */
function tzas_add_native_to_carriers()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'zasilkovna_dopravci';

    $native_types = array('packeta-zpoints', 'packeta-zbox');
    $native_labels = array('packeta-zpoints' => __('Zásilkovna Z-Point', WOOZASILKOVNASLUG), 'packeta-zbox' => __('Zásilkovna Z-BOX', WOOZASILKOVNASLUG));

    $id = 1000000;
    foreach (TORET_ZASILKOVNA_NATIVE_COUNTRIES as $NATIVE_COUNTRY) {
        foreach ($native_types as $native_type) {

            $dopravce_id = $id;
            $nazev = strtoupper($NATIVE_COUNTRY) . ' ' . $native_labels[$native_type];
            $stat = strtoupper($NATIVE_COUNTRY);
            $statnazev = WC()->countries->countries[$stat];
            $pobocky = 1;
            $api = 1;
            $vaha = 15;
            $slug = $native_type . '-' . $NATIVE_COUNTRY;
            $prac = 'zasilkovna>' . $slug;

            $dobirka = ($native_types == 'packeta-zpoints' ? 1 : 0);
            $deklarace = 0;
            $rozmery = 0;
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
                        'type' => 'packeta'
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
                        '%s'
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
                        'type' => 'packeta'
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
                        '%s'
                    )
                );
            }
            $id = $id + 1;
        }
    }
}

function tzas_sort_countries($countries)
{
    $native_countries = [];
    $other_countries = [];
    $custom_order = array_map("strtoupper", TORET_ZASILKOVNA_NATIVE_COUNTRIES);

    foreach ($custom_order as $code) {
        if (isset($countries[$code])) {
            $native_countries[$code] = $countries[$code];
        }
    }

    foreach ($countries as $code => $name) {
        if (!in_array($code, $custom_order)) {
            $other_countries[$code] = $name;
        }
    }

    asort($other_countries);

    return $native_countries + $other_countries;
}

function tzas_adjust_order_statuses_slugs(){
    $orderStatuses = wc_get_order_statuses();
    $adjsutedStatuses = [];
    foreach ($orderStatuses as $key => $value) {
        $adjsutedStatuses[str_replace('wc-', '', $key)] = $value;
    }
    return $adjsutedStatuses;
}

function tzas_custom_sort_array_by_keys($array)
{
    $custom_order = array_map("strtoupper", TORET_ZASILKOVNA_NATIVE_COUNTRIES);

    uksort($array, function ($a, $b) use ($custom_order) {
        $index_a = array_search($a, $custom_order);
        $index_b = array_search($b, $custom_order);

        if ($index_a !== false && $index_b !== false) {
            return $index_a - $index_b;
        }

        if ($index_a !== false) {
            return -1;
        }
        if ($index_b !== false) {
            return 1;
        }

        return strcmp($a, $b);
    });

    return $array;
}


function tzas_add_methods_to_shipping_zones($shipping_methods)
{
    global $wpdb;
	
	$zasilkovna_services = get_option('zasilkovna_services',[]);


    $table = $wpdb->prefix . 'woocommerce_shipping_zone_methods';

    // ID dopravy, který hledáme (Zásilkovna)
    $existing_zasilkovna_method_id = 'zasilkovna';
    $new_zasilkovna_method_id = 'zasilkovna>z-points';

    // Najdeme všechny zóny, které obsahují metodu Zásilkovna
    $zones_with_zasilkovna = $wpdb->get_results($wpdb->prepare("
        SELECT zone_id, is_enabled, method_order 
        FROM {$table} 
        WHERE method_id = %s
    ", $existing_zasilkovna_method_id), ARRAY_A);

    // Pokud žádné zóny neexistují, ukončíme
    if (empty($zones_with_zasilkovna)) {
        return;
    }

    // Projdeme všechny zóny a přidáme metodu zasilkovna>z-points
    $nativeLocations = array_map('strtoupper', TORET_ZASILKOVNA_NATIVE_COUNTRIES);

    foreach ($zones_with_zasilkovna as $zone) {

        $zone_id = (int)$zone['zone_id'];
		$locations = tzas_get_countries_and_continents_by_zone($zone_id);

        // Zkontrolujeme, jestli už zasilkovna>z-points není v zóně
        $already_exists = $wpdb->get_var($wpdb->prepare("
            SELECT method_id 
            FROM {$table} 
            WHERE zone_id = %d 
            AND method_id = %s
        ", $zone_id, $new_zasilkovna_method_id));

        $method_order = $zone['method_order'];
        $is_enabled = $zone['is_enabled'];

        // Add legacy Zasilkovna
        if (!$already_exists) {
			if(!empty(array_intersect($nativeLocations,$locations))){
				foreach($locations as $country){
					$slug_active = 'vydejnimista' . '' . '-active' . strtolower($country);
					if (!empty($zasilkovna_services[$slug_active])){
						$wpdb->insert($table, [
							'zone_id' => $zone_id,
							'method_id' => $new_zasilkovna_method_id,
							'method_order' => $method_order,
							'is_enabled' => $is_enabled
						]);
						break;
					}
				}
       		}
        }
        // Add single methods
        foreach ($shipping_methods as $method_slug => $shipping_method) {
            $enable_in_zone = true;
            if ($zone_id != 0) {
                $locations = tzas_get_countries_and_continents_by_zone($zone_id);

                if (!empty($locations)) {
                    if (empty($shipping_method['country']) || !in_array($shipping_method['country'], $locations)) {
                        $enable_in_zone = false;
                    }
                }
            }

            if (!$enable_in_zone) {
                continue;
            }

            $method_id = 'zasilkovna>' . $method_slug;
            $is_enabled = $shipping_method['active'];

            // Check if method already exists
            $already_exists = $wpdb->get_var($wpdb->prepare("
                SELECT method_id 
                FROM {$table} 
                WHERE zone_id = %d 
                AND method_id = %s
            ", $zone_id, $method_id));

            // If method already exists, skip
            if ($already_exists) {
                continue;
            }

            // Add method to zone
            $wpdb->insert($table, [
                'zone_id' => $zone_id,
                'method_id' => $method_id,
                'method_order' => $method_order, // Určení pořadí metody ve zóně
                'is_enabled' => $is_enabled // Aktivní doprava
            ]);
        }
    }
}

function tzas_get_countries_and_continents_by_zone($zone_id)
{
    global $wpdb;

    // SQL dotaz pro získání zemí a kontinentů spojených s danou dopravní zónou
    $results = $wpdb->get_results(
        $wpdb->prepare("
            SELECT 
                zone.zone_id, 
                zone.zone_name, 
                location.location_code, 
                location.location_type 
            FROM 
                {$wpdb->prefix}woocommerce_shipping_zones AS zone
            INNER JOIN 
                {$wpdb->prefix}woocommerce_shipping_zone_locations AS location 
                ON zone.zone_id = location.zone_id
            WHERE 
                zone.zone_id = %d
        ", $zone_id),
        ARRAY_A
    );

    // Inicializace pole pro kontinenty a země
    $countries = [];
    $continents = [];

    // Projdeme všechny výsledky
    foreach ($results as $result) {
        if ($result['location_type'] === 'country') {
            $countries[] = $result['location_code'];
        } elseif ($result['location_type'] === 'continent') {
            $continents[] = $result['location_code'];
        }
    }

    $countries = array_unique($countries);
    $continent_countries = [];
    foreach (array_unique($continents) as $continent) {
        $continent_countries = array_merge(tzas_get_countries_from_continent($continent), $continent_countries);
    }

    return array_merge($countries, $continent_countries);
}

function tzas_get_countries_from_continent($continent_code)
{
    // Načtení seznamu kontinentů z WooCommerce
    $wc_countries = new WC_Countries();
    $continents = $wc_countries->get_continents();

    // Zkontrolujeme, zda existuje zadaný kontinent
    if (!isset($continents[$continent_code])) {
        return [];
    }

    // Vracíme pole kódů zemí pro tento kontinent
    return $continents[$continent_code]['countries'];
}

function tzas_get_admin_menu_items()
{
    return array(
        'general' => __('General settings', WOOZASILKOVNASLUG),
        'carriers' => __('Carriers', WOOZASILKOVNASLUG),
        'auto_send' => __('Automatic sending', WOOZASILKOVNASLUG),
        'tracking' => __('Tracking', WOOZASILKOVNASLUG),
        'exchange_rates' => __('Exchange rates', WOOZASILKOVNASLUG),
        'support' => __('Support', WOOZASILKOVNASLUG),
    );
}

function tzas_check_api_password($zasilkovna_option)
{
    $content = '';
    if (!empty($zasilkovna_option['api_password'])) {
        $api = $zasilkovna_option['api_password'];
        $delka_chyba = '';
        $obsah_chyba = '';
        $chyba = false;
        if (strlen($api) != 32) {
            $chyba = true;
            $delka_chyba = '<br/>' . __('API password do not have correct length.', WOOZASILKOVNASLUG);
        }
        if (!preg_match("/^[a-z0-9]+$/", $api)) {
            $chyba = true;
            $obsah_chyba = '<br/>' . __('API password contains forbidden characters.', WOOZASILKOVNASLUG);
        }
        if ($chyba) {
            $content .= __('The API password is probably not probably. Please check it. ', WOOZASILKOVNASLUG);
            $content .= $delka_chyba;
            $content .= $obsah_chyba;
        }
    }
    return $content;
}

function tzas_get_cod_fee_type($zasilkovna_option, $zasilkovna_prices, $slug)
{
    if (isset($zasilkovna_prices[$slug . '-fee-type']) && !empty($zasilkovna_prices[$slug . '-fee-type'])) {
        return $zasilkovna_prices[$slug . '-fee-type'];
    }

    $def_type_cod = (!empty($zasilkovna_option['fee_by_price']) && $zasilkovna_option['fee_by_price'] == 'ok' ? 'single' : 'total');
    return $zasilkovna_prices[$slug . '-fee-type'] ?? $def_type_cod;
}

function tzas_get_rate_fee_type($zasilkovna_option, $zasilkovna_prices, $slug)
{
    if (isset($zasilkovna_prices[$slug . '-weight-type']) && !empty($zasilkovna_prices[$slug . '-weight-type'])) {
        return $zasilkovna_prices[$slug . '-weight-type'];
    }

    $def_type_cod = (!empty($zasilkovna_option['price_by_weight']) && $zasilkovna_option['price_by_weight'] == 'ok' ? 'weight' : 'single');
    return $zasilkovna_prices[$slug . '-weight-type'] ?? $def_type_cod;
}

function tzas_get_note_with_shortcodes($order, $note)
{
    $note = str_replace('{order_id}', $order->get_id(), $note);
    $note = str_replace('{customer_note}', $order->get_customer_note(), $note);
    $note = str_replace('{idoklad_invoice_nr}', Toret_HPOS_Compatibility::get_order_meta($order->get_id(), 'idoklad_invoice_document_number'), $note);
    $note = str_replace('{idoklad_invoice_id}', Toret_HPOS_Compatibility::get_order_meta($order->get_id(), 'idoklad_invoice_id'), $note);
    $note = str_replace('{idoklad_proforma_nr}', Toret_HPOS_Compatibility::get_order_meta($order->get_id(), 'idoklad_proforma_document_number'), $note);
    $note = str_replace('{idoklad_proforma_id}', Toret_HPOS_Compatibility::get_order_meta($order->get_id(), 'idoklad_proforma_id'), $note);
    $note = str_replace('{idoklad_proforma_vs}', Toret_HPOS_Compatibility::get_order_meta($order->get_id(), 'idoklad_proforma_var_symbol'), $note);
    return str_replace('{order_number}', $order->get_order_number(), $note);
}

function tzas_get_site_url()
{
    global $wpdb;
    $siteurl = $wpdb->get_row("SELECT * FROM $wpdb->options WHERE option_name = 'siteurl'");
    $site_url = $siteurl->option_value;
    if (is_multisite()) {
        $site_url = network_site_url();
    }
    return $site_url;
}

function tzas_get_shipping_method_id($order)
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

function tzas_round_to_nearest_multiple($n,$x=5) {
    return (round($n)%$x === 0) ? round($n) : round(($n+$x/2)/$x)*$x;
}