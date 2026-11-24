<?php
/**
 * Mini cart: AJAX endpoints, fragments and shortcodes.
 *
 * @package ShopChop
 */


/* =============================================================================
	§ 14 Mini Cart AJAX
   ============================================================================= */

/**
 * Return the current mini-cart HTML, count, and totals.
 *
 * Accepts: POST action=shopchop_get_mini_cart, nonce.
 */
function shopchop_get_mini_cart() {
	check_ajax_referer( 'shopchop_cart_nonce', 'nonce', true );

	ob_start();
	woocommerce_mini_cart();
	$mini_cart = str_replace( "\xEF\xBB\xBF", '', trim( ob_get_clean() ) );

	wp_send_json_success( array(
		'cart_html'     => $mini_cart,
		'cart_count'    => WC()->cart->get_cart_contents_count(),
		'cart_total'    => WC()->cart->get_cart_total(),
		'cart_subtotal' => WC()->cart->get_cart_subtotal(),
		'cart_is_empty' => WC()->cart->is_empty(),
	) );
}
add_action( 'wp_ajax_shopchop_get_mini_cart',        'shopchop_get_mini_cart' );
add_action( 'wp_ajax_nopriv_shopchop_get_mini_cart', 'shopchop_get_mini_cart' );



/**
 * Remove a single item from the cart and return the refreshed mini-cart.
 *
 * Accepts: POST action=shopchop_remove_cart_item, cart_item_key, nonce.
 */
function shopchop_remove_cart_item() {
	check_ajax_referer( 'shopchop_cart_nonce', 'nonce', true );

	$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( $_POST['cart_item_key'] ) : '';

	if ( empty( $cart_item_key ) ) {
		wp_send_json_error( array( 'message' => 'Invalid cart item' ) );
		return;
	}

	WC()->cart->remove_cart_item( $cart_item_key );

	ob_start();
	woocommerce_mini_cart();
	$mini_cart = str_replace( "\xEF\xBB\xBF", '', trim( ob_get_clean() ) );

	wp_send_json_success( array(
		'cart_html'     => $mini_cart,
		'cart_count'    => WC()->cart->get_cart_contents_count(),
		'cart_total'    => WC()->cart->get_cart_total(),
		'cart_is_empty' => WC()->cart->is_empty(),
		'message'       => 'Item removed from cart',
	) );
}
add_action( 'wp_ajax_shopchop_remove_cart_item',        'shopchop_remove_cart_item' );
add_action( 'wp_ajax_nopriv_shopchop_remove_cart_item', 'shopchop_remove_cart_item' ); // guests can have a cart session



/**
 * "N item(s)" span; WooCommerce fragments and the mini-cart JS keep every copy in sync.
 *
 * @return string
 */
function shopchop_cart_items_count_html() {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	$word  = 1 === $count ? 'item' : 'items';
	return '<span class="cart-items-count"><span class="count-number">' . (int) $count . '</span> ' . $word . '</span>';
}

/**
 * Push cart-count fragments so WooCommerce's fragment system keeps
 * the badge and item-count text in sync after any cart mutation.
 *
 * @param array $fragments Existing fragments.
 * @return array Updated fragments.
 */
function shopchop_cart_fragments( $fragments ) {
	$count = WC()->cart->get_cart_contents_count();

	ob_start(); ?>
	<span class="cart-count-badge" aria-hidden="true"><?php echo $count >= 0 ? $count : ''; ?></span>
	<?php $fragments['.cart-count-badge'] = ob_get_clean();

	$fragments['.cart-items-count'] = shopchop_cart_items_count_html();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'shopchop_cart_fragments' );


/* =============================================================================
	§ 15 Mini Cart Shortcodes
   ============================================================================= */

/**
 * [shopchop_mini_cart]
 * Renders the desktop cart trigger button and dropdown shell.
 * Cart contents are injected via AJAX (see ShopChop.CartDropdown in script.js).
 *
 * @return string Shortcode HTML.
 */
function shopchop_mini_cart_shortcode() {
	$count = WC()->cart->get_cart_contents_count();
	$word  = $count === 1 ? 'item' : 'items';

	ob_start(); ?>
	<div class="shopchop-cart-wrapper">
		<a href="<?php echo wc_get_cart_url(); ?>" class="shopchop-cart-trigger">
			<div class="cart-icon-wrapper">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" class="cart-icon" aria-hidden="true"><path d="M230.14,58.87A8,8,0,0,0,224,56H62.68L56.6,22.57A8,8,0,0,0,48.73,16H24a8,8,0,0,0,0,16h18L67.56,172.29a24,24,0,0,0,5.33,11.27,28,28,0,1,0,44.4,8.44h45.42A27.75,27.75,0,0,0,160,204a28,28,0,1,0,28-28H91.17a8,8,0,0,1-7.87-6.57L80.13,152h116a24,24,0,0,0,23.61-19.71l12.16-66.86A8,8,0,0,0,230.14,58.87ZM104,204a12,12,0,1,1-12-12A12,12,0,0,1,104,204Zm96,0a12,12,0,1,1-12-12A12,12,0,0,1,200,204Zm4-74.57A8,8,0,0,1,196.1,136H77.22L65.59,72H214.41Z"></path></svg>
				<span class="cart-count-badge" aria-hidden="true"><?php echo $count >= 0 ? $count : ''; ?></span>
			</div>
			<span class="cart-label"><?php esc_html_e( 'Cart', 'shopchop' ); ?></span><span class="sr-only">, <?php echo shopchop_cart_items_count_html(); ?></span>
		</a>

		<div class="shopchop-cart-dropdown" style="display:none;">
			<div class="cart-dropdown-header">
				<h3>Shopping Cart</h3>
				<span class="cart-items-count"><span class="count-number"><?php echo $count; ?></span> <?php echo $word; ?></span>
			</div>
			<div class="cart-dropdown-content">
				<div class="cart-loading">Loading cart...</div>
			</div>
		</div>
	</div>
	<?php return ob_get_clean();
}
add_shortcode( 'shopchop_mini_cart', 'shopchop_mini_cart_shortcode' );



/**
 * [shopchop_mobile_cart_icon_display]
 * Renders only the cart icon + badge (used in the mobile header bar).
 *
 * @return string Shortcode HTML.
 */
function shopchop_mobile_cart_icon() {
	$count = WC()->cart->get_cart_contents_count();
	ob_start(); ?>
	<div class="cart-icon-wrapper">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" class="cart-icon" aria-hidden="true"><path d="M230.14,58.87A8,8,0,0,0,224,56H62.68L56.6,22.57A8,8,0,0,0,48.73,16H24a8,8,0,0,0,0,16h18L67.56,172.29a24,24,0,0,0,5.33,11.27,28,28,0,1,0,44.4,8.44h45.42A27.75,27.75,0,0,0,160,204a28,28,0,1,0,28-28H91.17a8,8,0,0,1-7.87-6.57L80.13,152h116a24,24,0,0,0,23.61-19.71l12.16-66.86A8,8,0,0,0,230.14,58.87ZM104,204a12,12,0,1,1-12-12A12,12,0,0,1,104,204Zm96,0a12,12,0,1,1-12-12A12,12,0,0,1,200,204Zm4-74.57A8,8,0,0,1,196.1,136H77.22L65.59,72H214.41Z"></path></svg>
		<span class="cart-count-badge" aria-hidden="true"><?php echo $count >= 0 ? $count : ''; ?></span>
	</div>
	<?php return ob_get_clean();
}
add_shortcode( 'shopchop_mobile_cart_icon_display', 'shopchop_mobile_cart_icon' );



/**
 * [shopchop_mobile_cart_details_display]
 * Renders the mobile cart drawer header + content shell.
 * Cart contents are injected via AJAX (see ShopChop.MobileCart in script.js).
 *
 * @return string Shortcode HTML.
 */
function shopchop_mobile_cart_details() {
	$count = WC()->cart->get_cart_contents_count();
	$word  = $count === 1 ? 'item' : 'items';

	ob_start(); ?>
	<div class="mobile-cart-header">
		<h3 id="mobile-cart-title">Cart
			<span class="mobile-cart-items-count">(<span class="count-number"><?php echo $count; ?></span> <?php echo $word; ?>)</span>
		</h3>
		<button id="cart-close" aria-label="Close cart">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true"><path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"></path></svg>
		</button>
	</div>
	<div class="mobile-cart-content">
		<div class="cart-loading">Loading cart...</div>
	</div>
	<?php return ob_get_clean();
}
add_shortcode( 'shopchop_mobile_cart_details_display', 'shopchop_mobile_cart_details' );
