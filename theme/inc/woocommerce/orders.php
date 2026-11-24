<?php
/**
 * Orders: order number format and automatic status notes.
 *
 * @package ShopChop
 */


/* =============================================================================
	§ 7  Orders
   ============================================================================= */

/**
 * Format the public-facing order number as TSS-YYMMDD-NNNNN.
 *
 * Example: TSS-250401-00042
 *
 * @param int      $order_id Raw WooCommerce order ID.
 * @param WC_Order $order    Order object.
 * @return string Formatted order number.
 */
add_filter( 'woocommerce_order_number', 'shopchop_professional_order_format', 1, 2 );
function shopchop_professional_order_format( $order_id, $order ) {
	$prefix         = 'TSS';
	$date_created   = $order->get_date_created();
	$formatted_date = $date_created ? $date_created->date( 'ymd' ) : date( 'ymd' );
	$padded_id      = str_pad( $order_id, 5, '0', STR_PAD_LEFT );

	return $prefix . '-' . $formatted_date . '-' . $padded_id;
}



/**
 * Append an automatic customer-facing note when an order is cancelled.
 *
 * @param int      $order_id Order ID.
 * @param WC_Order $order    Order object.
 */
add_action( 'woocommerce_order_status_cancelled', 'shopchop_auto_cancelled_note', 10, 2 );

function shopchop_auto_cancelled_note( $order_id, $order ) {
	$order->add_order_note(
		__( 'This order was cancelled automatically due to a payment timeout or system cancellation.', 'woocommerce' ),
		true // visible to customer
	);
}



/**
 * Append an automatic customer-facing note when an order is completed.
 *
 * @param int      $order_id Order ID.
 * @param WC_Order $order    Order object.
 */
add_action( 'woocommerce_order_status_completed', 'shopchop_auto_completed_note', 10, 2 );

function shopchop_auto_completed_note( $order_id, $order ) {
	$order->add_order_note(
		__( 'Your order has been dispatched to the courier.', 'woocommerce' ),
		true // visible to customer
	);
}
