<?php
/**
 * @var $draw
 * @var string $licence_key
 * @var string $licence_info
 * @var array $zasilkovna_option
 * @var array $site_url
 * @var array $orderStatuses
 * @var array $orderStatusesAdjusted
 * @var array $statusesZasilkovna
 */


$content =  '<form method="post">';

$content .= '<tr>';
$content .= '<th>' . __('Load parcel status automatically', WOOZASILKOVNASLUG) . '</th>';
$content .= '<td>';
$content .= __('For automatic shipment status updates, set up CRON on the hosting server:', WOOZASILKOVNASLUG);
$content .= ' <code>' . trailingslashit($site_url) . 'wp-json/toret-zasilkovna/v1/statuses</code>';
$content .= $draw->add_copy_link(trailingslashit($site_url) . 'wp-json/toret-zasilkovna/v1/statuses');
$content .= '</td>';
$content .= '</tr>';

$content .= '<tr>';
$content .= '<th>' . __('Check statuses manually', WOOZASILKOVNASLUG) . '</th>';
$content .= '<td>';
$content .= '<a class="button toret-secondary" href="' . trailingslashit($site_url) . 'wp-json/toret-zasilkovna/v1/statuses'.'" target="_blank">' . __('Run', WOOZASILKOVNASLUG) . '</a>';
$content .= '</td>';
$content .= '</tr>';

$content .= $draw::add_text(
    array(
        'id' => 'status_days',
        'placeholder' => '',
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '1',
            'min' => '0',
        ),
        'description' => __('The number of days for which the order status will be checked back.', WOOZASILKOVNASLUG),
        'desc_tip' => false,
        'label' => __('Number of days for back check', WOOZASILKOVNASLUG),
        'default' => 7,
        'value' => $zasilkovna_option['status_days'] ?? 7,
    )
);

$content .= $draw::add_multiselect(
    array(
        'id' => 'zakazane_stavy',
        'class' => 'toret-draw-field-type-multiselect',
        'options' => $orderStatusesAdjusted,
        'label' => __('Do not check shipment status in these order statuses', WOOZASILKOVNASLUG),
        'value' => $zasilkovna_option['zakazane_stavy'] ?? []
    )
);

$content .= $draw::add_multiselect(
    array(
        'id' => 'zakazane_statusy',
        'class' => 'toret-draw-field-type-multiselect',
        'options' => $statusesZasilkovna,
        'label' => __('Do not check the status of the package in these shipment states', WOOZASILKOVNASLUG),
        'value' => $zasilkovna_option['zakazane_statusy'] ?? []
    )
);

$draw->draw_settings_box(
    $content,
    __('Packeta shipment status settings', WOOZASILKOVNASLUG),
    true,
    array('button_text' => __('Save', WOOZASILKOVNASLUG))
);

/**
 * Order status change based on package status
 */

$content = $draw::add_checkbox(
    array(
        'id' => 'status_change',
        'value' => $zasilkovna_option['status_change'] ?? "",
        'label' => __('Enable change of order status based on the parcel status', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$order_statuses_options = [];
$order_statuses_options[''] = __('Do not change', WOOZASILKOVNASLUG);
foreach ($orderStatuses as $slug => $StatusName) {
    $order_statuses_options[$slug] = $StatusName;
}
$content .= '';
foreach ($statusesZasilkovna as $statusId => $nazev) {
    $content .= $draw::add_select(
        array(
            'id' => 'set_status[' . $statusId . ']',
            'options' => $order_statuses_options,
            'label' => $nazev,
            'default' => '',
            'value' => $zasilkovna_option['set_status'][$statusId] ?? '',
        )
    );
}

$draw->draw_settings_box(
    $content,
    __('Order status change based on package status', WOOZASILKOVNASLUG),
    true,
    array('button_text' => __('Save', WOOZASILKOVNASLUG))
);

echo $draw::add_hidden(
    array(
        'id' => 'save_tracking_settings',
        'value' => 'save_tracking_settings',
    )
);

echo '</form>';
