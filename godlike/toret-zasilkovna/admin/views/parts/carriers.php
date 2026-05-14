<?php

/**
 * @var $draw
 * @var $ToretZasilkovna
 * @var string $licence_key
 * @var string $licence_info
 * @var array $zasilkovna_option
 * @var array $site_url
 */


$all_packeta_countries = $ToretZasilkovna->Helper->get_all_packetka_countries();
$all_packeta_countries = tzas_sort_countries($all_packeta_countries);

$content = '<tr>';
$content .= '<th>' . __('Automatically load carriers', WOOZASILKOVNASLUG) . '</th>';
$content .= '<td>';
$content .= __('For automatic carrier loading, set up CRON on the hosting server:', WOOZASILKOVNASLUG);
$content .= '<code>' . trailingslashit($site_url) . 'wp-json/toret-zasilkovna/v1/services</code>';
$content .= $draw->add_copy_link(trailingslashit($site_url) . 'wp-json/toret-zasilkovna/v1/services');
$content .= '</td>';
$content .= '</tr>';

$content .= '<tr>';
$content .= '<th>' . __('Load carriers manually', WOOZASILKOVNASLUG) . '</th>';
$content .= '<td>';
$content .= '<a class="button toret-secondary" href="' . home_url('?packetaservices=run').'" target="_blank">' . __('Run', WOOZASILKOVNASLUG) . '</a>';
$content .= '</td>';
$content .= '</tr>';

$draw->draw_settings_box(
    $content,
    __('Carriers', WOOZASILKOVNASLUG),
    false
);


$content = '<tr>';
$content .= '<th>' . __('Country name', WOOZASILKOVNASLUG) . '</th>';
$content .= '<th>' . __('Country code', WOOZASILKOVNASLUG) . '</th>';
$content .= '<th>' . __('Active carriers', WOOZASILKOVNASLUG) . '</th>';
$content .= '<th>' . __('Action', WOOZASILKOVNASLUG) . '</th>';
$content .= '</tr>';
foreach ($all_packeta_countries as $code => $name) {
    $active_carriers = $ToretZasilkovna->Helper->get_active_carriers_in_country(true,$code,true);
    $content .= '<tr>';
    $content .= '<td>' . $name . '</td>';
    $content .= '<td>' . $code . '</td>';
    $content .= '<td class="toret-active-carriers-list">' . $active_carriers . '</td>';
    $content .= '<td><a class="button toret-secondary" href="' . admin_url(TORETZASILKOVNASETTINGS . '&form=carriers-settings&country=' . $code) . '">' . __('Setup', WOOZASILKOVNASLUG) . '</a></td>';
    $content .= '</tr>';
}

$draw->draw_settings_box(
    $content,
    __('Countries', WOOZASILKOVNASLUG),
    false
);