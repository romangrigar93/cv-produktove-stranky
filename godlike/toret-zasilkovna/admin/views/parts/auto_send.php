<?php

/**
 * @var $draw
 * @var string $licence_key
 * @var string $licence_info
 * @var array $zasilkovna_option
 * @var array $site_url
 * @var array $gatewaysModified
 * @var array $orderStatuses
 */

echo '<form method="post">';

/**
 * Action on order status change
 */
$filtere_orderStatuses = [
    'wc-pending',
    'wc-cancelled',
    'wc-refunded',
    'wc-failed',
    'wc-checkout-draft',
];
foreach ($orderStatuses as $key => $status) {
    if (in_array($key, $filtere_orderStatuses)) {
        unset($orderStatuses[$key]);
    }
}
$enabledStatuses = apply_filters('toret_send_enabled_statuses', $orderStatuses);
$order_statuses = [];
$order_statuses[''] = __('Do not send automatically', WOOZASILKOVNASLUG);
foreach ($enabledStatuses as $key => $status) {
    $status_mod = str_replace('wc-', '', $key);
    $order_statuses[$status_mod] = $status;
}

$gateways = WC()->payment_gateways->payment_gateways();

$content = '';
if ($gateways) {
    foreach ($gateways as $gateway) {
        if ($gateway->enabled == 'yes') {
            $content .= $draw::add_select(
                array(
                    'id' => 'status[' . $gateway->id . ']',
                    'options' => $order_statuses,
                    'label' => $gatewaysModified[$gateway->id],
                    'default' => '',
                    'value' => $zasilkovna_send['status'][$gateway->id] ?? '',
                )
            );
        }
    }
}

$draw->draw_settings_box(
    $content,
    __('Send data to Packeta system on order status change', WOOZASILKOVNASLUG),
    true,
    ['button_text' => __('Save', 'toret-ppl')]
);

echo $draw::add_hidden(
    array(
        'id' => 'save_auto_send_settings',
        'value' => 'save_auto_send_settings',
    )
);

echo '</form>';