<?php
/**
 * @var $draw
 * @var string $licence_key
 * @var string $licence_info
 * @var array $zasilkovna_option
 * @var array $site_url
 */

echo '<form method="post">';


/**
 * Exchange rates
 */

$content = $draw::add_checkbox(
    array(
        'id' => 'country_currency_deactivate',
        'value' => $country_currency['country_currency_deactivate'] ?? "",
        'description' => __('The currency conversion will be done by the Packeta according to its own exchange rate at the time of package creation.', WOOZASILKOVNASLUG),
        'label' => __('Disable currency conversion according to plugin settings', WOOZASILKOVNASLUG),
        'cbvalue' => 'ok',
    )
);

$all_currencies = array(
    'CZK' => 'czk',
    'EUR' => 'euro',
    'HUF' => 'forint',
    'PLN' => 'zloty',
    'USD' => 'usd',
    'Lei' => 'lei',
);

$current_currency = get_woocommerce_currency();
$rate_string = '1 ' . $current_currency . ' = ';

foreach ($all_currencies as $code => $currency_slug) {
    if ($code === $current_currency) {
        continue;
    }

    $id = 'kurz-' . $currency_slug;

    $content .= $draw::add_text(
        array(
            'id' => $id,
            'placeholder' => '',
            'label' => __($rate_string . 'x ' . $code),
            'description' => '',
            'value' => $zasilkovna_prices[$id] ?? '',
        )
    );
}

$draw->draw_settings_box(
    $content,
    __('Exchange rates', WOOZASILKOVNASLUG),
    true,
    ['button_text' => __('Save', WOOZASILKOVNASLUG)]
);

/**
 * Currency and country settings
 */
$countries = [
        'country_currency_bg' => array(
            'label' => __('Bulgaria', WOOZASILKOVNASLUG),
            'default' => 'CZK',
            'options' => array(
                'CZK' => __('CZK', WOOZASILKOVNASLUG),
            ),
            'readonly' => true,
        ),
        'country_currency_cz' => array(
            'label' => __('Czech Republic', WOOZASILKOVNASLUG),
            'default' => 'CZK',
            'options' => array(
                'CZK' => __('CZK', WOOZASILKOVNASLUG),
            ),
            'readonly' => true,
        ),
        'country_currency_dk' => array(
            'label' => __('Denmark', 'woocommerce'),
            'default' => 'CZK',
            'options' => array(
                'CZK' => __('CZK', WOOZASILKOVNASLUG),
            ),
            'readonly' => true,
        ),
        'country_currency_hu' => array(
            'label' => __('Hungary', WOOZASILKOVNASLUG),
            'default' => 'CZK',
            'options' => array(
                'CZK' => __('CZK', WOOZASILKOVNASLUG),
                'HUF' => __('HUF', WOOZASILKOVNASLUG),
            ),
            'readonly' => false,
        ),
        'country_currency_de' => array(
            'label' => __('Germany', WOOZASILKOVNASLUG),
            'default' => 'CZK',
            'options' => array(
                'CZK' => __('CZK', WOOZASILKOVNASLUG),
                'EUR' => __('EUR', WOOZASILKOVNASLUG),
            ),
            'readonly' => false,
        ),
        'country_currency_pl' => array(
            'label' => __('Poland', WOOZASILKOVNASLUG),
            'default' => 'CZK',
            'options' => array(
                'PLN"' => __('PLN', WOOZASILKOVNASLUG),
                'CZK' => __('CZK', WOOZASILKOVNASLUG),
                'EUR' => __('EUR', WOOZASILKOVNASLUG),
            ),
            'readonly' => false,
        ),
        'country_currency_at' => array(
            'label' => __('Austria', 'zasilkovna'),
            'default' => 'CZK',
            'options' => array(
                'CZK' => __('CZK', WOOZASILKOVNASLUG),
                'EUR' => __('EUR', WOOZASILKOVNASLUG),
            ),
            'readonly' => false,
        ),
        'country_currency_ro' => array(
            'label' => __('Romania', 'zasilkovna'),
            'default' => 'CZK',
            'options' => array(
                'CZK' => __('CZK', WOOZASILKOVNASLUG),
            ),
            'readonly' => true,
        ),
        'country_currency_sk' => array(
            'label' => __('Slovakia', 'zasilkovna'),
            'default' => 'CZK',
            'options' => array(
                'CZK' => __('CZK', WOOZASILKOVNASLUG),
                'EUR' => __('EUR', WOOZASILKOVNASLUG),
            ),
            'readonly' => false,
        ),
        'country_currency_eu' => array(
            'label' => __('Other eurozone countries', 'zasilkovna'),
            'default' => 'EUR',
            'options' => array(
                'EUR' => __('EUR', WOOZASILKOVNASLUG),
            ),
            'readonly' => true,
        ),
];
$content = '';
foreach ($countries as $key => $country) {
    $content .= $draw::add_select(
        array(
            'id' => $key,
            'options' => $country['options'],
            'custom_attributes' => $country['readonly'] ? array('disabled' => 'disabled') : array(),
            'label' => $country['label'],
            'default' => $country['default'],
            'value' => $country_currency[$key] ?? $country['default'],
        )
    );
    if($country['readonly']) {
        $content .= $draw::add_hidden(
            array(
                'id' => $key,
                'value' => $country['default'],
            )
        );
    }
}

$draw->draw_settings_box(
    $content,
    __('Currency and country settings', WOOZASILKOVNASLUG),
    true,
    ['button_text' => __('Save', 'toret-ppl')]
);


echo $draw::add_hidden(
    array(
        'id' => 'save_exchange_rate_settings',
        'value' => 'save_exchange_rate_settings',
    )
);

echo '</form>';
