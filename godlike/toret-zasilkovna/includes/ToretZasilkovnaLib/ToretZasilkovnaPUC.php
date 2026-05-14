<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

add_filter( 'puc_request_info_result-toret-zasilkovna', 'woo_zasilkovna_refreshLicenseFromPluginInfo', 10, 2 );

function woo_zasilkovna_refreshLicenseFromPluginInfo( $pluginInfo, $result ) {
    //Verify that this is an OK response.
    if ( ! is_wp_error( $result )
        && isset( $result['response']['code'] )
        && ( $result['response']['code'] == 200 )
        && ! empty( $result['body'] )
    ) {
        $apiResponse = json_decode( $result['body'] );

        if ( $apiResponse ) {
            if ( $apiResponse->licence_check && $apiResponse->licence_check != 'ok') {
                update_option( 'woo-zasilkovna-licence-server-check', $apiResponse->licence_check );
            }else{
                update_option( 'woo-zasilkovna-licence-server-check', '' );
            }
        }
    }

    //Return the plugin metadata unmodified.
    return $pluginInfo;
}

/**
 * @var $MyUpdateChecker
 */
$MyUpdateChecker->addQueryArgFilter( 'woozasilkovna' );
function woozasilkovna( $queryArgs ) {
    $licence = get_option( 'woo-zasilkovna-licence-key' );
    if ( ! empty( $licence ) ) {
        $queryArgs['license_key'] = $licence;
    }

    return $queryArgs;
}

add_action( 'in_plugin_update_message-toret-zasilkovna/toret-zasilkovna.php', 'woo_zasilkovna_addUpgradeMessageLink', 10, 2 );

function woo_zasilkovna_addUpgradeMessageLink() {
    $licence = get_option( 'woo-zasilkovna-licence-server-check', 'Pro více informací klikněte na odkaz "Zkontrolovat aktualizace".' );
    echo $licence;
}

function woo_zasilkovna_custom_cron_schedule( $schedules ) {
    $schedules['every_twelve_hours'] = array(
        'interval' => 43200, // Every 12 hours
        'display'  => __( 'Every 12 hours' ),
    );

    return $schedules;
}

add_filter( 'cron_schedules', 'woo_zasilkovna_custom_cron_schedule' );

//Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'woo_zasilkovna_cron_hook' ) ) {
    wp_schedule_event( time(), 'every_twelve_hours', 'woo_zasilkovna_cron_hook' );
}

///Hook into that action that'll fire every twelve hours
add_action( 'woo_zasilkovna_cron_hook', 'woo_zasilkovna_lic_litecont' );