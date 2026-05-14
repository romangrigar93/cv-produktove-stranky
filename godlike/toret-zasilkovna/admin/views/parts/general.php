<?php

/**
 * @var $draw
 * @var $localDraw
 * @var string $licence_key
 * @var string $licence_info
 * @var array $zasilkovna_option
 * @var array $site_url
 */


echo '<form method="post">';

$content = $draw::add_text(
    array(
        'id' => 'zas_licence',
        'placeholder' => '',
        'label' => __('License key', WOOZASILKOVNASLUG),
        'description' => $licence_info,
        'value' => $licence_key,
    )
);

$content .= $draw::add_text(
    array(
        'id' => 'api_password',
        'placeholder' => '',
        'label' => __('API password', WOOZASILKOVNASLUG),
        'description' => tzas_check_api_password($zasilkovna_option),
        'default' => '',
        'value' => $zasilkovna_option['api_password'] ?? "",
    )
);

$content .= $draw::add_text(
    array(
        'id' => 'nazev_eshopu',
        'placeholder' => '',
        'label' => __('Indication', WOOZASILKOVNASLUG),
        'description' => __('Detailed guide, how to find it ', WOOZASILKOVNASLUG) . '<a href="https://documentation.toret.cz/zasilkovna/#napojení-na-zásilkovnu"
                          target="_blank">' . __('here', WOOZASILKOVNASLUG) . '.</a>',
        'desc_tip' => false,
        'default' => '',
        'value' => $zasilkovna_option['nazev_eshopu'] ?? "",
    )
);

$draw->draw_settings_box(
    $content,
    __('Connection', WOOZASILKOVNASLUG),
    true,
    array( 'button_text' => __('Save', WOOZASILKOVNASLUG))
);


/**
 * Packeta basic settings
 */
$content = $draw::add_select(
    array(
        'id' => 'doprava_zdarma',
        'options' => array(
            'all' => __('All shipping methods will be free', WOOZASILKOVNASLUG),
            'zasilkovna' => __('Free shipping only for Packeta', WOOZASILKOVNASLUG),
            'default' => __('Free shipping in shipping selection', WOOZASILKOVNASLUG),
            'only' => __('Delete "Free shipping" - Prices will stay', WOOZASILKOVNASLUG),
        ),
        'label' => __('Free shipping', WOOZASILKOVNASLUG),
        'default' => 'default',
        'value' => $zasilkovna_option['doprava_zdarma'] ?? 'default',
    )
);

$content .= $draw::add_select(
    array(
        'id' => 'free_coupon',
        'options' => array(
            'all' => __('All Packeta shipping methods for free', WOOZASILKOVNASLUG),
            'selected' => __('Selected shipping method for free', WOOZASILKOVNASLUG),
            'none' => __('Not applicable', WOOZASILKOVNASLUG),
        ),
        'label' => __('Free shipping coupon behaviour', WOOZASILKOVNASLUG),
        'default' => 'all',
        'value' => $zasilkovna_option['free_coupon'] ?? 'all',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'error_email',
        'value' => $zasilkovna_option['error_email'] ?? "",
        'label' => __('Send email notifications about failed shipment creation', WOOZASILKOVNASLUG),
        'cbvalue' => 'email',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'change_shipping_address',
        'value' => $zasilkovna_option['change_shipping_address'] ?? "",
        'label' => __('Replace the shipping address with selected Packeta branch address', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'pricelimit_reduce',
        'value' => $zasilkovna_option['pricelimit_reduce'] ?? "",
        'label' => __('Set the shipment value to the maximum allowed value if exceeded', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'tzas_hide_product_tabs',
        'value' => $zasilkovna_option['tzas_hide_product_tabs'] ?? "",
        'label' => __('Hide Zasilkovna tabs in product and category details', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'disableLog',
        'value' => $zasilkovna_option['disableLog'] ?? "",
        'label' => __('Disable log', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'branchLog',
        'value' => $zasilkovna_option['branchLog'] ?? "",
        'label' => __('Enable pickup point selection log', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_select(
    array(
        'id' => 'email_tracking_email_hook',
        'options' => array(
            'woocommerce_email_after_order_table' => __('woocommerce_email_after_order_table', WOOZASILKOVNASLUG),
            'woocommerce_email_order_details' => __('woocommerce_email_order_details', WOOZASILKOVNASLUG),
            'woocommerce_email_before_order_table' => __('woocommerce_email_before_order_table', WOOZASILKOVNASLUG),
            'woocommerce_email_order_meta' => __('woocommerce_email_order_meta', WOOZASILKOVNASLUG),
            'woocommerce_email_customer_details' => __('woocommerce_email_customer_details', WOOZASILKOVNASLUG),
        ),
        'label' => __('Tracking link in email position', WOOZASILKOVNASLUG),
        'default' => 'woocommerce_email_after_order_table',
        'value' => $zasilkovna_option['email_tracking_email_hook'] ?? 'woocommerce_email_after_order_table',
    )
);

$draw->draw_settings_box(
    $content,
    __('Packeta basic settings', WOOZASILKOVNASLUG),
    true,
    array('button_text' => __('Save', WOOZASILKOVNASLUG))
);

/**
 * Cash on delivery
 */

$tax_classes_default = [];
$tax_classes_default[] = (object)array('slug' => 'inherit', 'name' => __('Shipping tax class based on cart items', WOOZASILKOVNASLUG));
$tax_classes_default[] = (object)array('slug' => '', 'name' => __('Standard rate', WOOZASILKOVNASLUG));
$tax_classes_current = WC_Tax::get_tax_rate_classes();
$tax_classes = array_merge($tax_classes_default, $tax_classes_current);
$tax_classes = (object)$tax_classes;
$option = 'cod_tax_class';
$value = $zasilkovna_option['cod_tax_class'] ?? 'inherit';

$options = [];
foreach ($tax_classes as $tax_class) {
    $slug = $tax_class->slug;
    $title = $tax_class->name;
    $options[$slug] = $title;
}
$content = $draw::add_select(
    array(
        'id' => 'cod_tax_class',
        'options' => $options,
        'label' => __('Cash on delivery tax class', WOOZASILKOVNASLUG),
        'default' => 'inherit',
        'value' => $zasilkovna_option['cod_tax_class'] ?? 'inherit',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'cod_round_hu',
        'value' => $zasilkovna_option['cod_round_hu'] ?? "",
        'label' => __('Round Cash on delivery value to Hungary to multiples of five', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_select(
    array(
        'id' => 'priplatek_dobirka',
        'options' => array(
            'ano' => __('Yes', WOOZASILKOVNASLUG),
            'ne' => __('No', WOOZASILKOVNASLUG),
        ),
        'label' => __('Keep Cash on delivery fee for orders with free shipping', WOOZASILKOVNASLUG),
        'default' => 'ano',
        'value' => $zasilkovna_option['priplatek_dobirka'] ?? 'ano',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'cod_point_check',
        'value' => $zasilkovna_option['cod_point_check'] ?? "",
        'label' => __('Disable cash on delivery if unavailable for the selected pickup point', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$draw->draw_settings_box(
    $content,
    __('Cash on delivery', WOOZASILKOVNASLUG),
    true,
    array('button_text' => __('Save', WOOZASILKOVNASLUG))
);


/**
 * Price settings
 */

if (get_option('woocommerce_calc_taxes') === 'yes') {
    $content = $draw::add_checkbox(
        array(
            'id' => 'price_with_vat',
            'value' => $zasilkovna_option['price_with_vat'] ?? "",
            'label' => __('Set price incl. VAT', WOOZASILKOVNASLUG),
            'cbvalue' => 'ok',
        )
    );

    $draw->draw_settings_box(
        $content,
        __('Price settings', WOOZASILKOVNASLUG),
        true,
        array('button_text' => __('Save', WOOZASILKOVNASLUG))
    );
}

/**
 * Weight settings
 */
$content = $draw::add_text(
    array(
        'id' => 'zas_default_weight',
        'placeholder' => '',
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '0.01',
            'min' => '0',
        ),
        'label' => __('Default shipment weight', WOOZASILKOVNASLUG),
        'default' => '',
        'value' => $zasilkovna_option['zas_default_weight'] ?? "",
    )
);

$content .= $draw::add_text(
    array(
        'id' => 'zas_add_wrap_weight',
        'placeholder' => '',
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '0.01',
            'min' => '0',
        ),
        'label' => __('Packaging weight in kg', WOOZASILKOVNASLUG),
        'default' => '',
        'description' => __("It is included in the carrier's maximum weight limit and in the shipping cost based on weight.", WOOZASILKOVNASLUG),
        'value' => $zasilkovna_option['zas_add_wrap_weight'] ?? "",
    )
);

$draw->draw_settings_box(
    $content,
    __('Weight settings', WOOZASILKOVNASLUG),
    true,
    array('button_text' => __('Save', WOOZASILKOVNASLUG))
);

/**
 * Claim assistant
 */
$content = $draw::add_checkbox(
    array(
        'id' => 'asistent',
        'value' => $zasilkovna_option['asistent'] ?? "",
        'label' => __('Activate a claim assistant', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'asisten_direct',
        'value' => $zasilkovna_option['asisten_direct'] ?? "",
        'label' => __('Create a return shipment along with a standard shipment', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'asisten_print',
        'value' => $zasilkovna_option['asisten_print'] ?? "",
        'label' => __('Print labels for return and standard shipments together', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'asisten_track',
        'value' => $zasilkovna_option['asisten_track'] ?? "",
        'label' => __('Track the status of return shipment along with the standard shipment', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'asisten_change',
        'value' => $zasilkovna_option['asisten_change'] ?? "",
        'label' => __('Include return shipment in order status change based on package status', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$draw->draw_settings_box(
    $content,
    __('Return shipment', WOOZASILKOVNASLUG),
    true,
    array('button_text' => __('Save', WOOZASILKOVNASLUG))
);


/**
 * Multiple packages
 */
$content = $draw::add_checkbox(
    array(
        'id' => 'multipackage_enable',
        'value' => $zasilkovna_option['multipackage_enable'] ?? "",
        'label' => __('Allow splitting into multiple shipments', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$selected = $zasilkovna_option['multipackage_source'] ?? [];
if(!is_array($selected)){
    $selected = array($selected);
}
$content .= $draw::add_multiselect(
    array(
        'id' => 'multipackage_source',
        'options' => array(
            'weight' => __('Weight', WOOZASILKOVNASLUG),
            'longest' => __('Longest side', WOOZASILKOVNASLUG),
            'sum' => __('Sum of dimensions of all three sides', WOOZASILKOVNASLUG),
        ),
        'label' => __('Division by', WOOZASILKOVNASLUG),
        'default' => ['weight'],
        'class' => 'toret-draw-field-type-multiselect',
        'value' => $selected,
    )
);

$thresholdSources = $selected;
$thresholdLabels = ToretZasilkovnaHelper::getThresholdLabels();

$content .= $draw::add_text(
    array(
        'id' => 'multipackage_treshold_weight',
        'placeholder' => '',
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '0.00001',
            'min' => '0',
        ),
        'label' => $thresholdLabels['weight'],
        'default' => 7,
        'wrapper_class' => (!in_array('weight',$thresholdSources) ? 'tzas-multiple-weight-hidden' : ''),
        'value' => $zasilkovna_option['multipackage_treshold_weight'] ?? ($zasilkovna_option['multipackage_treshold'] ?? 15),
    )
);
$content .= $draw::add_text(
    array(
        'id' => 'multipackage_treshold_longest',
        'placeholder' => '',
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '0.00001',
            'min' => '0',
        ),
        'label' => $thresholdLabels['longest'],
        'default' => 7,
        'wrapper_class' => (!in_array('longest',$thresholdSources) ? 'tzas-multiple-weight-hidden' : ''),
        'value' => $zasilkovna_option['multipackage_treshold_longest'] ?? ($zasilkovna_option['multipackage_treshold'] ?? ''),
    )
);
$content .= $draw::add_text(
    array(
        'id' => 'multipackage_treshold_sum',
        'placeholder' => '',
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '0.00001',
            'min' => '0',
        ),
        'label' => $thresholdLabels['sum'],
        'default' => 7,
        'wrapper_class' => (!in_array('sum',$thresholdSources) ? 'tzas-multiple-weight-hidden' : ''),
        'value' => $zasilkovna_option['multipackage_treshold_sum'] ?? ($zasilkovna_option['multipackage_treshold'] ?? ''),
    )
);


$content .= $draw::add_checkbox(
    array(
        'id' => 'multipackage_widget_nolimit',
        'value' => $zasilkovna_option['multipackage_widget_nolimit'] ?? "",
        'label' => __('Disable pickup points filter by order weight', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$draw->draw_settings_box(
    $content,
    __('Multiple shipments', WOOZASILKOVNASLUG),
    true,
    array('button_text' => __('Save', WOOZASILKOVNASLUG))
);


/**
 * Widget
 */
$content = $draw::add_checkbox(
    array(
        'id' => 'enableHDChecker',
        'value' => $zasilkovna_option['enableHDChecker'] ?? "",
        'label' => __('Enable widget for address selection (Home delivery carriers in CZ and SK)', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'forceHDChecker',
        'value' => $zasilkovna_option['forceHDChecker'] ?? "",
        'label' => __('Set address selection from HD widget as mandatory', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'tzas_modal_show_on_select',
        'value' => $zasilkovna_option['tzas_modal_show_on_select'] ?? "",
        'label' => __('Automatically open the pickup point widget when selecting shipping*', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_select(
    array(
        'id' => 'widget_position',
        'options' => array(
            'after' => __('Right after shipping', WOOZASILKOVNASLUG),
            'below' => __('Below shipping list', WOOZASILKOVNASLUG),
        ),
        'label' => __('Pickup point widget button position*', WOOZASILKOVNASLUG),
        'default' => 'below',
        'value' => $zasilkovna_option['widget_position'] ?? 'below',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'checkout_point_js_check',
        'value' => $zasilkovna_option['checkout_point_js_check'] ?? "",
        'label' => __('Enable selected pickup point check via JS. This can be possibly incomatible with your checkout theme.', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_note(
    array(
        'message' => __('*This setup is not applicable for block checkout.', WOOZASILKOVNASLUG),
    )
);

$draw->draw_settings_box(
    $content,
    __('Widget', WOOZASILKOVNASLUG),
    true,
    array('button_text' => __('Save', WOOZASILKOVNASLUG))
);

/**
 * Icons setup
 */

$content = $draw::add_note(
    array(
        'message' => __('This setup is not applicable for block checkout.', WOOZASILKOVNASLUG),
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'tzas_show_icon',
        'value' => get_option('tzas_show_icon'),
        'label' => __('Show icon next to shipping method', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_checkbox(
    array(
        'id' => 'tzas_show_pickup_icon',
        'value' => get_option('tzas_show_pickup_icon', 'ok'),
        'label' => __('Display an icon next to the pickup point selection button at checkout', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_textarea(
    array(
        'id' => 'tzas_icon_custom_css',
        'placeholder' => '',
        'label' => __('Icon custom css', WOOZASILKOVNASLUG),
        'value' => get_option('tzas_icon_custom_css'),
    )
);

$content .= $draw::add_textarea(
    array(
        'id' => 'tzas_icon_pickup_custom_css',
        'placeholder' => '',
        'label' => __('Choose pickup logo custom css', WOOZASILKOVNASLUG),
        'value' => get_option('tzas_icon_pickup_custom_css'),
    )
);
$draw->draw_settings_box(
    $content,
    __('Icons setup', WOOZASILKOVNASLUG),
    true,
    array('button_text' => __('Save', WOOZASILKOVNASLUG))
);


/**
 * Labels
 */
$content = $draw::add_checkbox(
    array(
        'id' => 'disable_popup_print',
        'value' => $zasilkovna_option['disable_popup_print'] ?? "",
        'label' => __('Deactivate pop-up for label printing', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$content .= $draw::add_select(
    array(
        'id' => 'packeta_default_status',
        'options' => array(
            'A6-on-A4' => __('labels, 1/4 A4, direct print, print on A4, 4pcs/page', WOOZASILKOVNASLUG),
            'A6-on-A6' => __('labels, 1/4 A4, direct print, 1 pc/page', WOOZASILKOVNASLUG),
            'A7-on-A7' => __('labels, 1/8 A4, 1 pc/page', WOOZASILKOVNASLUG),
            'A7-on-A4' => __('labels, 1/8 A4, print on A4, 8pcs/page', WOOZASILKOVNASLUG),
            'A8-on-A8' => __('labels, 1/16 A4, 1 pc/page', WOOZASILKOVNASLUG),
            '105x35mm-on-A4' => __('labels, 105x35mm A4, print on A4, 16pcs/page', WOOZASILKOVNASLUG),
        ),
        'label' => __('Default label type for Packeta', WOOZASILKOVNASLUG),
        'default' => 'A6-on-A4',
        'value' => $zasilkovna_option['packeta_default_status'] ?? 'A6-on-A4',
    )
);

$content .= $draw::add_select(
    array(
        'id' => 'packeta_services_default_status',
        'options' => array(
            'A6-on-A4' => __('labels, 1/4 A4, direct print, print on A4, 4pcs/page', WOOZASILKOVNASLUG),
            'A6-on-A6' => __('labels, 1/4 A4, direct print, 1 pc/page', WOOZASILKOVNASLUG),
        ),
        'label' => __('Default Label Type for Other Carrier', WOOZASILKOVNASLUG),
        'default' => 'A6-on-A4',
        'value' => $zasilkovna_option['packeta_services_default_status'] ?? 'A6-on-A4',
    )
);

$content .= $draw::add_text(
    array(
        'id' => 'ref_text_before',
        'placeholder' => '',
        'label' => __('Text Before Shipment Number - Shipment Reference', WOOZASILKOVNASLUG),
        'default' => '',
        'value' => $zasilkovna_option['ref_text_before'] ?? "",
    )
);

$content .= $draw::add_text(
    array(
        'id' => 'ref_text_after',
        'placeholder' => '',
        'label' => __('Text After Shipment Number - Shipment Reference', WOOZASILKOVNASLUG),
        'default' => '',
        'value' => $zasilkovna_option['ref_text_after'] ?? "",
    )
);

$tip = '<ul>';
$tip .= __('*Possible shortcodes:', WOOZASILKOVNASLUG);
$tip .= '<li class="tzas-shortcode-li"><code>{order_id}</code> - ' . __('Order ID', WOOZASILKOVNASLUG) . '</li>';
$tip .= '<li class="tzas-shortcode-li"><code>{order_number}</code> - ' . __('Order number', WOOZASILKOVNASLUG) . '</li>';
$tip .= '<li class="tzas-shortcode-li"><code>{customer_note}</code> - ' . __('Customer note', WOOZASILKOVNASLUG) . '</li>';
$tip .= '<li class="tzas-shortcode-li"><code>{idoklad_invoice_nr}</code> - ' . __('iDoklad invoice document number', WOOZASILKOVNASLUG) . '</li>';
$tip .= '<li class="tzas-shortcode-li"><code>{idoklad_invoice_id}</code> - ' . __('iDoklad invoice id', WOOZASILKOVNASLUG) . '</li>';
$tip .= '<li class="tzas-shortcode-li"><code>{idoklad_proforma_nr}</code> - ' . __('iDoklad proforma document number', WOOZASILKOVNASLUG) . '</li>';
$tip .= '<li class="tzas-shortcode-li"><code>{idoklad_proforma_id}</code> - ' . __('iDoklad proforma id', WOOZASILKOVNASLUG) . '</li>';
$tip .= '<li class="tzas-shortcode-li"><code>{idoklad_proforma_vs}</code> - ' . __('iDoklad proforma variable symbol', WOOZASILKOVNASLUG) . '</li>';
$tip .= '</ul>';
$tip .= '</ul>';
$content .= $draw::add_text(
    array(
        'id' => 'tzas_packet_note',
        'placeholder' => '',
        'label' => __('Package note visible on label*', WOOZASILKOVNASLUG),
        'default' => '',
        'description' => $tip,
        'value' => $zasilkovna_option['tzas_packet_note'] ?? "",
    )
);

$draw->draw_settings_box(
    $content,
    __('Shipment labels', WOOZASILKOVNASLUG),
    true,
    array('button_text' => __('Save', WOOZASILKOVNASLUG))
);

echo $draw::add_hidden(
    array(
        'id' => 'save_general_settings',
        'value' => 'save_general_settings',
    )
);

echo '</form>';