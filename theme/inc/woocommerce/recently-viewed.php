<?php
/**
 * Recently viewed products on single product pages.
 *
 * @package ShopChop
 */


/* =============================================================================
   § 17 — Recently Viewed Products (single product pages)
   ============================================================================= */

define( 'SHOPCHOP_RECENTLY_VIEWED_MAX', 6 );
define( 'SHOPCHOP_RECENTLY_VIEWED_COOKIE', 'shopchop_recently_viewed' );

/**
 * Write current product ID into the recently-viewed cookie on every product page visit.
 */
add_action( 'template_redirect', 'shopchop_track_recently_viewed' );
function shopchop_track_recently_viewed() {
	if ( ! is_product() ) {
		return;
	}

	$product_id = get_the_ID();
	$viewed     = isset( $_COOKIE[ SHOPCHOP_RECENTLY_VIEWED_COOKIE ] )
		? array_map( 'absint', explode( '|', sanitize_text_field( wp_unslash( $_COOKIE[ SHOPCHOP_RECENTLY_VIEWED_COOKIE ] ) ) ) )
		: array();

	// Move current ID to front, remove duplicates, cap at max.
	$viewed = array_filter( $viewed, fn( $id ) => $id !== $product_id );
	array_unshift( $viewed, $product_id );
	$viewed = array_slice( $viewed, 0, SHOPCHOP_RECENTLY_VIEWED_MAX );

	setcookie(
		SHOPCHOP_RECENTLY_VIEWED_COOKIE,
		implode( '|', $viewed ),
		time() + ( 30 * DAY_IN_SECONDS ),
		COOKIEPATH,
		COOKIE_DOMAIN,
		is_ssl(),
		false
	);
}

/**
 * Render the Recently Viewed section on single product pages.
 * Hooked between tabs (10) and related products (20).
 */
add_action( 'woocommerce_after_single_product_summary', 'shopchop_recently_viewed_section', 15 );
function shopchop_recently_viewed_section() {
	if ( ! isset( $_COOKIE[ SHOPCHOP_RECENTLY_VIEWED_COOKIE ] ) ) {
		return;
	}

	$viewed = array_map( 'absint', explode( '|', sanitize_text_field( wp_unslash( $_COOKIE[ SHOPCHOP_RECENTLY_VIEWED_COOKIE ] ) ) ) );

	// Exclude the product currently being viewed.
	$viewed = array_filter( $viewed, fn( $id ) => $id !== get_the_ID() );
	$viewed = array_values( array_slice( $viewed, 0, 4 ) );

	if ( empty( $viewed ) ) {
		return;
	}

	$args = array(
		'post_type'           => 'product',
		'post__in'            => $viewed,
		'orderby'             => 'post__in',
		'posts_per_page'      => 4,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
	);

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return;
	}
	?>
	<section class="shopchop-recently-viewed">
		<h2 class="recently-viewed-heading">
			<?php esc_html_e( 'Recently viewed', 'shopchop' ); ?>
		</h2>
		<?php
		woocommerce_product_loop_start();

		while ( $query->have_posts() ) {
			$query->the_post();
			wc_get_template_part( 'content', 'product' );
		}

		wp_reset_postdata();

		woocommerce_product_loop_end();
		?>
	</section>
	<?php
}
