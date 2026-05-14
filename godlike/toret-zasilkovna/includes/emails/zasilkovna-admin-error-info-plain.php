<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * @var $email_heading
 * @var $note
 * @var $order
 */

echo "= " . $email_heading . " =\n\n";

printf( __( 'Error in sending parcel for order No.: %d', WOOZASILKOVNASLUG ), $order->get_order_number() ) . '\n';
echo __( 'Parcel has not been entered into Packeta\'s system due to error:', WOOZASILKOVNASLUG ) . '\n';
echo $note . '\n';

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
