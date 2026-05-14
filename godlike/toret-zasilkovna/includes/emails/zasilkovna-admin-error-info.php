<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @var $email_heading
 * @var $note
 * @var $order
 */

?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<h2><?php printf( __( 'Error in sending parcel for order No.: %s', WOOZASILKOVNASLUG ), $order->get_order_number() ); ?></h2>
<p><?php echo __( 'Parcel has not been entered into Packeta\'s system due to error:', WOOZASILKOVNASLUG ); ?></p>
<p><?php echo $note; ?></p>


<?php do_action( 'woocommerce_email_footer' ); ?>
