<?php
/**
 * Product display: price HTML, loop structure, category listing, variation swatches.
 *
 * @package ShopChop
 */


/* =============================================================================
	§ 3  Product Display
   ============================================================================= */

/**
 * Override WooCommerce's price HTML to include discount percentages.
 *
 * Regular price  → <span class="price-normal">
 * Sale price     → <span class="discount-price"> with a -%% badge
 *
 * @param string     $price   Default price HTML.
 * @param WC_Product $product Current product.
 * @return string Modified price HTML.
 */
add_filter( 'woocommerce_get_price_html', 'shopchop_price_display', 10, 2 );
function shopchop_price_display( $price, $product ) {

	if ( $product->is_type( 'variable' ) ) {
		// Parent has no price of its own; use variations. Price ranges keep WC's range markup.
		$regular_price = (float) $product->get_variation_regular_price( 'min' );
		$sale_price    = (float) $product->get_variation_price( 'min' );
		if ( $regular_price !== (float) $product->get_variation_regular_price( 'max' )
			|| $sale_price !== (float) $product->get_variation_price( 'max' ) ) {
			return $price;
		}
	} else {
		$regular_price = (float) $product->get_regular_price();
		$sale_price    = (float) $product->get_sale_price();
	}

	if ( ! $regular_price ) return $price;

	if ( ! $product->is_on_sale() || ! $sale_price || $sale_price >= $regular_price ) {
		return sprintf(
			'<span class="price-normal"><span class="regular">%s</span></span>',
			wc_price( $regular_price )
		);
	}

	$discount = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );

	return sprintf(
		'<span class="discount-price">
			<del class="regular">%s</del>
			<span class="discount">-%d%%</span>
			<span class="sale">%s</span>
		</span>',
		wc_price( $regular_price ),
		$discount,
		wc_price( $sale_price )
	);
}



/**
 * Category listing – replace the default link-open hook with a custom wrapper
 * that adds a CSS group class and opens the image container.
 */
remove_action( 'woocommerce_before_subcategory',          'woocommerce_template_loop_category_link_open', 10 );
remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title',     10 );

add_action( 'woocommerce_before_subcategory',          'shopchop_category_link_wrapper', 10 );
add_action( 'woocommerce_shop_loop_subcategory_title', 'shopchop_category_title_custom', 10 );

function shopchop_category_link_wrapper( $category ) { ?>
	<a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="group">
		<div class="category-meta-image">
<?php }

function shopchop_category_title_custom( $category ) { ?>
		</div><!-- .category-meta-image -->
		<div class="category-meta-details">
			<h2 class="woocommerce-loop-category__title"><?php echo esc_html( $category->name ); ?></h2>
			<span class="cat-product-count"><?php echo esc_html( sprintf( _n( '%d product', '%d products', $category->count, 'shopchop' ), $category->count ) ); ?></span>
		</div><!-- .category-meta-details -->
<?php }



/**
 * Product loop – inject structural wrappers around thumbnail, details, and
 * action areas so each card can be styled independently.
 *
 * Priority map:
 *   before_shop_loop_item      @5  → open <a>
 *   before_shop_loop_item_title @5  → open .product-image-wrapper (+ OOS badge)
 *   before_shop_loop_item_title @15 → close image, open .product-details-wrapper
 *   after_shop_loop_item       @5  → close .product-details-wrapper + </a>
 *   after_shop_loop_item       @9  → open .product-actions
 *   after_shop_loop_item       @11 → close .product-actions
 */
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item',  'woocommerce_template_loop_product_link_close', 5 );

add_action( 'woocommerce_before_shop_loop_item', function () {
	global $product;
	echo '<a href="' . esc_url( get_permalink( $product->get_id() ) ) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link group">';
}, 5 );

add_action( 'woocommerce_before_shop_loop_item_title', function () {
	echo '<div class="product-image-wrapper">';
	global $product;
	if ( ! $product->is_in_stock() ) {
		echo '<span class="out-of-stock-badge" aria-hidden="true">' . esc_html__( 'Out of Stock', 'woocommerce' ) . '</span>';
	}
}, 5 );

add_action( 'woocommerce_before_shop_loop_item_title', function () {
	echo '</div>'; // .product-image-wrapper
	echo '<div class="product-details-wrapper">';
}, 15 );

add_action( 'woocommerce_after_shop_loop_item', function () {
	echo '</div>'; // .product-details-wrapper
	echo '</a>';   // main product link
}, 5 );

add_action( 'woocommerce_after_shop_loop_item', function () {
	echo '<div class="product-actions">';
}, 9 );

add_action( 'woocommerce_after_shop_loop_item', function () {
	echo '</div>'; // .product-actions
}, 11 );



/**
 * AJAX add-to-cart for grouped products (own endpoint, not WC core's).
 * WC's own wc-ajax=add_to_cart only accepts a single product_id +
 * quantity, but a grouped product form posts one quantity[child_id]
 * field per sub-product — so this loops through and adds each one that
 * has a quantity > 0. Same response shape as WC_AJAX::add_to_cart() so
 * the same JS success handler works for both.
 */
add_action( 'wc_ajax_shopchop_add_to_cart_grouped', 'shopchop_add_to_cart_grouped' );
add_action( 'wc_ajax_nopriv_shopchop_add_to_cart_grouped', 'shopchop_add_to_cart_grouped' );
function shopchop_add_to_cart_grouped() {
	if ( empty( $_POST['quantity'] ) || ! is_array( $_POST['quantity'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		wp_send_json( array( 'error' => true, 'product_url' => wp_get_referer() ) );
	}

	$added = false;

	foreach ( wp_unslash( $_POST['quantity'] ) as $product_id => $quantity ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$quantity = wc_stock_amount( $quantity );
		if ( $quantity <= 0 ) {
			continue;
		}
		if ( false !== WC()->cart->add_to_cart( absint( $product_id ), $quantity ) ) {
			$added = true;
		}
	}

	if ( ! $added ) {
		wp_send_json( array( 'error' => true, 'product_url' => wp_get_referer() ) );
	}

	do_action( 'woocommerce_ajax_added_to_cart', 0 );

	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

	wp_send_json( array(
		'fragments' => apply_filters(
			'woocommerce_add_to_cart_fragments',
			array( 'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>' )
		),
		'cart_hash' => WC()->cart->get_cart_hash(),
	) );
}



/**
 * Rename "Additional information" to "Details" — both the product tab
 * label and the in-tab heading.
 *
 * @param array $tabs Product tabs.
 * @return array Modified tabs.
 */
add_filter( 'woocommerce_product_tabs', 'shopchop_rename_additional_information_tab' );
function shopchop_rename_additional_information_tab( $tabs ) {
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = __( 'Details', 'shopchop' );
	}
	return $tabs;
}



/**
 * Hide the "(0)" review count from the Reviews tab title when a product
 * has no reviews yet — a zero-count badge reads as negative social proof.
 *
 * @param array $tabs Product tabs.
 * @return array Modified tabs.
 */
add_filter( 'woocommerce_product_tabs', 'shopchop_hide_zero_review_count', 20 );
function shopchop_hide_zero_review_count( $tabs ) {
	if ( isset( $tabs['reviews'] ) ) {
		global $product;
		if ( $product && ! $product->get_review_count() ) {
			$tabs['reviews']['title'] = __( 'Reviews', 'woocommerce' );
		}
	}
	return $tabs;
}


/* =============================================================================
	§ 4  Variation Swatches
   ============================================================================= */

/**
 * Replace the variation <select> with pill/button swatches.
 *
 * The original <select> is hidden but kept in the DOM so WooCommerce's own
 * variation-matching JavaScript continues to work. The pill buttons sync back
 * to the hidden select via the JS in script.js (ShopChop.PillSwatches).
 *
 * @param string $html  Default select HTML.
 * @param array  $args  Variation attribute arguments.
 * @return string Hidden select + pill container.
 */
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'shopchop_variation_swatch_pill', 10, 2 );
function shopchop_variation_swatch_pill( $html, $args ) {
	$options   = $args['options'];
	$product   = $args['product'];
	$attribute = $args['attribute'];
	$selected  = $args['selected'];

	if ( empty( $options ) || ! $product ) return $html;

	$container = '<div class="pill-swatches-container" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '">';
	foreach ( $options as $option ) {
		$active    = ( $selected === $option ) ? 'active' : '';
		$container .= sprintf(
			'<button type="button" class="pill-swatch %s" data-value="%s">%s</button>',
			$active,
			esc_attr( $option ),
			esc_html( $option )
		);
	}
	$container .= '</div>';

	return '<div style="display:none;">' . $html . '</div>' . $container;
}

// Raise the AJAX variation threshold so pill availability checks work reliably
// on products with many variation combinations.
add_filter( 'woocommerce_ajax_variation_threshold', function () {
	return 100;
} );
