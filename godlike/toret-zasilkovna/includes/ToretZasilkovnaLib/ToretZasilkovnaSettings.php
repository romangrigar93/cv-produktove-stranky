<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

add_action( 'admin_init', 'check_woo_zasilkovna_licence' );
add_action( 'init', 'check_woo_zasilkovna_licence' );

/**
 * Check if licence is active

 */
if ( ! function_exists( 'check_woo_zasilkovna_licence' ) ) {
    function check_woo_zasilkovna_licence() {
        $licence_status = get_option( 'woo-zasilkovna-licence' );
        if ( ! empty( $licence_status ) ) {
            if ( $licence_status == 'active' ) {
                global $lic;
                $lic = 'active';
            }
        }
    }
}

/**
 * Control licence
 */
if ( ! function_exists( 'woo_zasilkovna_control_licence' ) ) {
    function woo_zasilkovna_control_licence( $licence ) {
        $ip = $_SERVER['REMOTE_ADDR'];

        $url = tzas_get_site_url();

        $api_params = array(
            'licence' => $licence,
            'ip'      => $ip,
            'url'     => $url,
            'slug'    => 'woocommerce-zasilkovna'
        );

        // Call the custom API.
        $response = wp_remote_post( 'http://licence.toret.cz/wp-content/plugins/plc/heavycontrol.php', array(
            'timeout'   => 35,
            'sslverify' => false,
            'body'      => $api_params
        ) );

        // make sure the response came back okay
        if ( ! is_wp_error( $response ) ) {
            woo_zasilkovna_lic_cont( $response['body'], $licence );
        }
    }
}

if ( ! function_exists( 'woo_zasilkovna_lic_litecont' ) ) {
    function woo_zasilkovna_lic_litecont() {
        $licence = get_option( 'woo-zasilkovna-licence-key' );

        $url = tzas_get_site_url();

        $countz = get_option('toret_zpoint_count',0);
        $counts = get_option('toret_service_count',0);

        $api_params = array(
            'licence' => $licence,
            'url'     => $url,
            'slug'    => 'woocommerce-zasilkovna',
            'counts'  =>  $counts,
            'countz'  =>  $countz
        );

        // Call the custom API.
        $response = wp_remote_post( 'http://licence.toret.cz/wp-content/plugins/plc/litecontrol.php', array(
            'timeout'   => 35,
            'sslverify' => false,
            'body'      => $api_params
        ) );

        // make sure the response came back okay
        if ( ! is_wp_error( $response ) ) {
            woo_zasilkovna_lic_cont( $response['body'], $licence );
        }
    }
}

if ( ! function_exists( 'woo_zasilkovna_lic_cont' ) ) {
    function woo_zasilkovna_lic_cont( $status, $licence ) {
        update_option('toret_zpoint_count',0);
        update_option('toret_service_count',0);
        if ( $status == 'ok' ) {
            update_option( 'woo-zasilkovna-licence', 'active' );
            update_option( 'woo-zasilkovna-info', '<span class="toret-license-success">' . __( 'Your license has been activated.', 'zasilkovna' ) . '</span>' );
        } elseif ( $status == 'fail' ) {
            update_option( 'woo-zasilkovna-info', '<span class="toret-license-error">' . __( 'Invalid license key.<br />Please contract support at <a href="https://www.toret.cz">Toret.cz</a>.', 'zasilkovna' ) . '</span>' );
            update_option( 'woo-zasilkovna-licence', '' );
        } elseif ( $status == 'double' ) {
            update_option( 'woo-zasilkovna-info', '<span class="toret-license-error">' . __( 'Submitted license key does not correspond to the website\'s URL. <br />Please check your license key in section <a href="https://toret.cz/muj-ucet/">My Account</a>. In case of other issues contact support at <a href="https://www.toret.cz">Toret.cz</a>.', 'zasilkovna' ) . '</span>' );
            update_option( 'woo-zasilkovna-licence', '' );
        } elseif ( $status == 'empty' ) {
            update_option( 'woo-zasilkovna-info', '<span class="toret-license-error">' . __( 'You do not own the entered license key <br />Please contact support at <a href="https://www.toret.cz">Toret.cz</a>.', 'zasilkovna' ) . '</span>' );
            update_option( 'woo-zasilkovna-licence', '' );
        } else {
            update_option( 'woo-zasilkovna-info', '<span class="toret-license-error">' . __( 'Invalid license key.<br />Please contract support at <a href="https://www.toret.cz">Toret.cz</a>.', 'zasilkovna' ) . '</span>' );
            update_option( 'woo-zasilkovna-licence', '' );
        }
        update_option( 'woo-zasilkovna-licence-key', $licence );
    }
}
