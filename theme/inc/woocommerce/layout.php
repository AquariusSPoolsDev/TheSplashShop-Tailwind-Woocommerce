<?php
/**
 * WooCommerce layout: wrappers, styles, demo-store notice, checkout payment placement.
 *
 * @package ShopChop
 */


/* =============================================================================
	§ 2  WooCommerce Layout
   ============================================================================= */

// Suppress the WordPress admin bar site-wide.
add_filter( 'show_admin_bar', '__return_false' );

// Remove default WooCommerce stylesheet (theme ships its own styles).
add_filter( 'woocommerce_enqueue_styles', '__return_false' );

// Replace WooCommerce's default <div> wrappers with the theme's <main> tag.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper',     10 );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', 'shopchop_wc_wrapper_start' );
add_action( 'woocommerce_after_main_content',  'shopchop_wc_wrapper_end'   );

function shopchop_wc_wrapper_start() { ?>
	<main id="primary" class="shopchop-wrapper shopchop-woocommerce woocommerce" role="primary">
<?php }

function shopchop_wc_wrapper_end() { ?>
	</main><!-- #primary -->
<?php }



/**
 * Wrap the result-count and ordering controls in a single utility bar div.
 */
add_action( 'woocommerce_before_shop_loop', 'shopchop_before_listing_start', 15 );
function shopchop_before_listing_start() {
	echo '<div class="shop-utility-wrapper">';
}

add_action( 'woocommerce_before_shop_loop', 'shopchop_before_listing_end', 35 );
function shopchop_before_listing_end() {
	echo '</div>';
}


/* =============================================================================
	§ 9  Demo Store Notice & Checkout Layout
   ============================================================================= */

/**
 * Move the WooCommerce demo-store banner from wp_footer / wp_body_open to a
 * custom action hook (`shopchop_demo_store_wrapper`) placed inside the header.
 */
remove_action( 'wp_footer',    'woocommerce_demo_store', 10 );
remove_action( 'wp_body_open', 'woocommerce_demo_store', 10 );
add_action(    'shopchop_demo_store_wrapper', 'woocommerce_demo_store', 10 );



/**
 * Move the checkout payment block out of the default order-review section and
 * into a custom action hook (`shopchop_checkout_payment`) so the theme can
 * position it freely in the checkout template.
 */
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
add_action(    'shopchop_checkout_payment',          'woocommerce_checkout_payment', 20 );
