<?php

/**
 * @var $draw
 * @var $localDraw
 * @var $zasilkovna_services
 * @var $zasilkovna_prices
 * @var $ToretZasilkovna
 * @var string $licence_key
 * @var string $licence_info
 * @var array $zasilkovna_option
 * @var array $site_url
 */

$country = $_GET['country'] ?? 'CZ';

echo '<form method="post">';

$carriers = $ToretZasilkovna->Helper->get_active_carriers_in_country(false,$country,false);

foreach ($carriers as $key => $carrier) {
    $localDraw::draw_carrier_settings($carrier, strtolower($country), $zasilkovna_services, $zasilkovna_option, $zasilkovna_prices);
}

echo '<form method="post">';
echo $draw::add_hidden(
    array(
        'id' => 'save_country_settings',
        'value' => $country,
    )
);
echo '</form>';