<?php
/**
 * Custom stock statuses: Pre-Order and Coming Soon.
 *
 * @package ShopChop
 */


/* =============================================================================
	§ 16 Custom Stock Statuses
   ============================================================================= */

/**
 * Register "Pre-Order" and "Coming Soon" stock statuses and remove "On Backorder".
 *
 * @param array $statuses Registered stock statuses.
 * @return array Modified statuses.
 */
add_filter( 'woocommerce_product_stock_status_options', 'shopchop_custom_stock_status' );
function shopchop_custom_stock_status( $statuses ) {
	unset( $statuses['onbackorder'] );
	$statuses['pre_order']   = __( 'Pre-Order',   'shopchop' );
	$statuses['coming_soon'] = __( 'Coming Soon', 'shopchop' );
	return $statuses;
}



/**
 * Variable products: WooCommerce's own stock-status sync only understands
 * instock/outofstock/onbackorder, so a variable product whose variations are
 * all "Pre-Order" (or "Coming Soon") gets its parent stamped "outofstock" —
 * core has no idea those custom statuses exist. Recompute the parent's
 * status live from its variations instead of trusting the stored meta.
 *
 * Priority when variations disagree: instock > pre_order > coming_soon > outofstock.
 *
 * @param string     $status  Stored stock status.
 * @param WC_Product $product Current product.
 * @return string
 */
add_filter( 'woocommerce_product_get_stock_status', 'shopchop_variable_stock_status_from_children', 10, 2 );
function shopchop_variable_stock_status_from_children( $status, $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return $status;
	}

	$children_statuses = array();
	foreach ( $product->get_children() as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( $variation ) {
			$children_statuses[] = $variation->get_stock_status();
		}
	}

	if ( empty( $children_statuses ) ) {
		return $status;
	}

	foreach ( array( 'instock', 'pre_order', 'coming_soon' ) as $priority_status ) {
		if ( in_array( $priority_status, $children_statuses, true ) ) {
			return $priority_status;
		}
	}

	return 'outofstock';
}



/**
 * Replace the frontend stock HTML for custom statuses.
 *
 * @param string     $html    Default stock HTML.
 * @param WC_Product $product Current product.
 * @return string Modified stock HTML.
 */
add_filter( 'woocommerce_get_stock_html', 'shopchop_custom_stock_status_display', 10, 2 );
function shopchop_custom_stock_status_display( $html, $product ) {
	$status = $product->get_stock_status();

	if ( $status === 'pre_order' ) {
		return '<p class="stock pre-order">'   . __( 'Pre-Order',   'shopchop' ) . '</p>';
	}
	if ( $status === 'coming_soon' ) {
		return '<p class="stock coming-soon">' . __( 'Coming Soon', 'shopchop' ) . '</p>';
	}

	return $html;
}



/**
 * Block purchase for Coming Soon products.
 *
 * @param bool       $purchasable Current purchasable state.
 * @param WC_Product $product     Current product.
 * @return bool
 */
add_filter( 'woocommerce_is_purchasable', 'shopchop_custom_status_purchasable', 10, 2 );
function shopchop_custom_status_purchasable( $purchasable, $product ) {
	return ( $product->get_stock_status() === 'coming_soon' ) ? false : $purchasable;
}



/**
 * On the single product page, hide the default Add to Cart button and show
 * a "Coming Soon" label instead.
 */
add_action( 'woocommerce_single_product_summary', 'shopchop_hide_add_to_cart_coming_soon', 1 );
function shopchop_hide_add_to_cart_coming_soon() {
	global $product;
	if ( $product && $product->get_stock_status() === 'coming_soon' ) {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	}
}



add_action( 'woocommerce_single_product_summary', 'shopchop_coming_soon_button', 31 );
function shopchop_coming_soon_button() {
	global $product;
	if ( $product->get_stock_status() === 'coming_soon' ) {
		echo '<p class="stock coming-soon">' . __( 'Coming Soon', 'shopchop' ) . '</p>';
	}
}



// Replace the loop Add to Cart link with a Coming Soon label in product grids.
add_filter( 'woocommerce_loop_add_to_cart_link', function ( $html, $product ) {
	if ( $product->get_stock_status() === 'coming_soon' ) {
		return '<p class="stock coming-soon">' . __( 'Coming Soon', 'shopchop' ) . '</p>';
	}
	return $html;
}, 10, 2 );



/**
 * Show a notice on checkout if the cart contains any Pre-Order items.
 */
add_action( 'woocommerce_before_checkout_form', 'shopchop_preorder_checkout_notice' );
function shopchop_preorder_checkout_notice() {
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$product = $cart_item['data'];
		if ( $product && $product->get_stock_status() === 'pre_order' ) {
			wc_print_notice(
				__( 'Your cart contains a Pre-Order item. Pre-order items will be shipped as soon as stock is available.', 'shopchop' ),
				'notice'
			);
			break;
		}
	}
}



/**
 * Append a Pre-Order badge to item names in order emails and My Account order details.
 *
 * $is_visible = false in email context; true when product link is rendered on frontend.
 *
 * @param string                $name       Item name.
 * @param WC_Order_Item_Product $item       Order item.
 * @param bool                  $is_visible Whether a product link is being rendered.
 * @return string
 */
add_filter( 'woocommerce_order_item_name', 'shopchop_preorder_item_name_badge', 10, 3 );
function shopchop_preorder_item_name_badge( $name, $item, $is_visible ) {
	$product = $item->get_product();
	if ( ! $product || $product->get_stock_status() !== 'pre_order' ) {
		return $name;
	}

	$badge = '<span class="preorder-badge">'
		. __( 'Pre-Order', 'shopchop' )
		. '</span>';

	return $name . $badge;
}



/**
 * Admin product list customizations (stock status HTML + list CSS) moved to
 * the shopchop-theme-settings plugin: includes/class-admin-product-columns.php
 */
