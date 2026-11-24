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


/* =============================================================================
	Order progress, tracking and help (My Account dashboard + order detail)
   ============================================================================= */

/**
 * Three-step progress for an order: Ordered, Preparing, Dispatched.
 * "Completed" in this store means handed to the courier (see the note above),
 * so there is no "Delivered" step. Cancelled, refunded and failed orders get none.
 *
 * @param WC_Order $order Order.
 * @return string Markup, or '' when the status has no progress to show.
 */
function shopchop_order_progress_html( $order ) {
	$status = $order->get_status();
	$map    = array( 'pending' => 1, 'on-hold' => 1, 'processing' => 2, 'completed' => 3 );
	if ( ! isset( $map[ $status ] ) ) {
		return '';
	}
	$current = $map[ $status ];

	$first = __( 'Ordered', 'shopchop' );
	if ( 'pending' === $status ) {
		$first = __( 'Awaiting payment', 'shopchop' );
	} elseif ( 'on-hold' === $status ) {
		$first = __( 'On hold', 'shopchop' );
	}
	$steps = array( 1 => $first, 2 => __( 'Preparing', 'shopchop' ), 3 => __( 'Dispatched', 'shopchop' ) );

	$html = '<ol class="order-progress" aria-label="' . esc_attr__( 'Order progress', 'shopchop' ) . '">';
	foreach ( $steps as $n => $label ) {
		$done  = $n < $current || ( 3 === $current );
		$class = $done ? 'is-done' : ( $n === $current ? 'is-current' : 'is-todo' );
		$attr  = $n === $current ? ' aria-current="step"' : '';
		$state = $done ? __( 'done', 'shopchop' ) : ( $n === $current ? __( 'current step', 'shopchop' ) : __( 'not yet', 'shopchop' ) );
		$html .= '<li class="' . esc_attr( $class ) . '"' . $attr . '><span class="dot" aria-hidden="true"></span><span class="step">' . esc_html( $label ) . '</span><span class="sr-only">, ' . esc_html( $state ) . '</span></li>';
	}
	$html .= '</ol>';

	// Explain the two statuses customers don't recognise.
	if ( 'on-hold' === $status ) {
		$html .= '<p class="order-status-note">' . esc_html__( "We're confirming your payment.", 'shopchop' ) . '</p>';
	} elseif ( 'pending' === $status ) {
		$html .= '<p class="order-status-note">' . esc_html__( "We're waiting for your payment.", 'shopchop' ) . '</p>';
	}
	return $html;
}

/**
 * Tracking numbers saved by a shipment-tracking plugin (the common
 * _wc_shipment_tracking_items meta). Empty when none is installed or saved.
 *
 * @param WC_Order $order Order.
 * @return array[] Each: provider, number, url.
 */
function shopchop_order_tracking( $order ) {
	$items = $order->get_meta( '_wc_shipment_tracking_items' );
	if ( ! is_array( $items ) ) {
		return array();
	}
	$out = array();
	foreach ( $items as $item ) {
		if ( empty( $item['tracking_number'] ) ) {
			continue;
		}
		$out[] = array(
			'provider' => ! empty( $item['custom_tracking_provider'] ) ? $item['custom_tracking_provider'] : ( $item['tracking_provider'] ?? '' ),
			'number'   => $item['tracking_number'],
			'url'      => $item['custom_tracking_link'] ?? '',
		);
	}
	return $out;
}

/**
 * Tracking lines + "Need help with this order?" WhatsApp link.
 *
 * @param WC_Order $order Order.
 * @return string Markup.
 */
function shopchop_order_help_html( $order ) {
	$html = '';
	foreach ( shopchop_order_tracking( $order ) as $t ) {
		$number = $t['url']
			? '<a href="' . esc_url( $t['url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $t['number'] ) . '</a>'
			: esc_html( $t['number'] );
		$html  .= '<p class="order-tracking">' . esc_html__( 'Tracking number:', 'shopchop' ) . ' ' . $number . ( $t['provider'] ? ' <span class="provider">(' . esc_html( $t['provider'] ) . ')</span>' : '' ) . '</p>';
	}

	if ( function_exists( 'shopchop_get_whatsapp_number' ) ) {
		/* translators: %s: order number */
		$message = sprintf( __( 'Hi, I need help with my order #%s.', 'shopchop' ), $order->get_order_number() );
		$url     = 'https://wa.me/' . rawurlencode( shopchop_get_whatsapp_number() ) . '?text=' . rawurlencode( $message );
		$html   .= '<a class="order-help" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Need help with this order?', 'shopchop' ) . '<span class="sr-only"> ' . esc_html__( '(opens WhatsApp)', 'shopchop' ) . '</span></a>';
	}
	return $html;
}

/**
 * Products from the customer's recent paid orders that can still be bought,
 * newest first, one entry per product/variation.
 *
 * @param int $customer_id Customer.
 * @param int $limit       Max products.
 * @return WC_Product[]
 */
function shopchop_buy_again_products( $customer_id, $limit = 4 ) {
	$orders = wc_get_orders(
		array(
			'customer_id' => $customer_id,
			'status'      => array( 'completed', 'processing' ),
			'limit'       => 10,
			'orderby'     => 'date',
			'order'       => 'DESC',
		)
	);
	$seen = array();
	$out  = array();
	foreach ( $orders as $order ) {
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( ! $product || isset( $seen[ $product->get_id() ] ) ) {
				continue;
			}
			$seen[ $product->get_id() ] = true;
			$parent_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
			if ( 'publish' !== get_post_status( $parent_id ) || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
				continue;
			}
			$out[] = $product;
			if ( count( $out ) >= $limit ) {
				return $out;
			}
		}
	}
	return $out;
}
