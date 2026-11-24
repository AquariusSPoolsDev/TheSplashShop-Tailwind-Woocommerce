<?php
/**
 * Product search: AJAX endpoints and the [shopchop_search_bar] shortcode.
 *
 * @package ShopChop
 */


/* =============================================================================
	§ 12 AJAX Search
   ============================================================================= */

/**
 * Scope the native WP search (search.php) to products and posts only.
 * Pages are excluded — static nav-accessible content, not what visitors
 * search for by keyword.
 */
function shopchop_search_products_only( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$query->set( 'post_type', array( 'product', 'post' ) );
	}
}
add_action( 'pre_get_posts', 'shopchop_search_products_only' );

/**
 * Search products by title, description, short description, and meta fields.
 *
 * The handler runs two WP_Query passes:
 *   1. Native `s` search (covers post_title + post_content) with a filter that
 *      also checks post_excerpt (short description).
 *   2. A meta_query covering SKU and other registered meta keys.
 *
 * Results from both passes are deduped, capped at 10, then fetched in a final
 * query that preserves relevance order.
 *
 * Accepts: POST action=wc_search_products, search_term, category, nonce.
 */
function shopchop_search_products() {
	check_ajax_referer( 'wc_ajax_search_nonce', 'nonce', true );

	$search_term = isset( $_POST['search_term'] ) ? sanitize_text_field( $_POST['search_term'] ) : '';
	$category    = isset( $_POST['category'] )    ? sanitize_text_field( $_POST['category'] )    : '';

	if ( empty( $search_term ) || mb_strlen( $search_term ) < 2 ) {
		wp_send_json_success( array( 'products' => array() ) );
		return;
	}

	// Shared tax query (empty when category = 'all').
	$tax_query = array();
	if ( ! empty( $category ) && $category !== 'all' ) {
		$tax_query = array( array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $category,
		) );
	}

	// ── Pass 1: title + content + excerpt ────────────────────────────────────
	$extend_excerpt = function ( $search, $wp_query ) use ( $search_term ) {
		global $wpdb;
		if ( ! $wp_query->is_search() ) return $search;
		$like    = '%' . $wpdb->esc_like( $search_term ) . '%';
		$search .= $wpdb->prepare( " OR ({$wpdb->posts}.post_excerpt LIKE %s)", $like );
		return $search;
	};
	add_filter( 'posts_search', $extend_excerpt, 10, 2 );

	$content_query = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'fields'         => 'ids',
		's'              => $search_term,
		'tax_query'      => $tax_query,
	) );

	remove_filter( 'posts_search', $extend_excerpt, 10 );
	$ids_content = $content_query->posts;

	// ── Pass 2: meta fields (SKU etc.) ────────────────────────────────────────
	$meta_keys = apply_filters( 'shopchop_search_meta_keys', array(
		'_sku',
		'_short_description',
		'short_description',
	) );

	$meta_clauses = array( 'relation' => 'OR' );
	foreach ( $meta_keys as $key ) {
		$meta_clauses[] = array(
			'key'     => $key,
			'value'   => $search_term,
			'compare' => 'LIKE',
		);
	}

	$meta_query = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'fields'         => 'ids',
		'meta_query'     => $meta_clauses,
		'tax_query'      => $tax_query,
	) );
	$ids_meta = $meta_query->posts;

	// ── Merge, dedup, cap ─────────────────────────────────────────────────────
	$all_ids = array_slice( array_unique( array_merge( $ids_content, $ids_meta ) ), 0, 10 );

	if ( empty( $all_ids ) ) {
		wp_send_json_success( array( 'products' => array() ) );
		return;
	}

	// ── Final fetch ───────────────────────────────────────────────────────────
	$final_query = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => count( $all_ids ),
		'post__in'       => $all_ids,
		'orderby'        => 'post__in',
	) );

	$products = array();
	if ( $final_query->have_posts() ) {
		while ( $final_query->have_posts() ) {
			$final_query->the_post();
			$product = wc_get_product( get_the_ID() );
			if ( ! $product ) continue;

			$products[] = array(
				'id'    => get_the_ID(),
				'title' => get_the_title(),
				'url'   => get_permalink(),
				'image' => get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ),
				'price' => $product->get_price_html(),
			);
		}
		wp_reset_postdata();
	}

	wp_send_json_success( array( 'products' => $products ) );
}
add_action( 'wp_ajax_wc_search_products',        'shopchop_search_products' );
add_action( 'wp_ajax_nopriv_wc_search_products', 'shopchop_search_products' );



/**
 * Return top-level product categories for the search-bar dropdown.
 *
 * Accepts: POST action=wc_get_categories, nonce.
 */
function shopchop_search_get_cat() {
	check_ajax_referer( 'wc_ajax_search_nonce', 'nonce', true );

	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
	) );

	$cats = array();
	if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
		foreach ( $categories as $cat ) {
			$cats[] = array( 'slug' => $cat->slug, 'name' => $cat->name );
		}
	}

	wp_send_json_success( array( 'categories' => $cats ) );
}
add_action( 'wp_ajax_wc_get_categories',        'shopchop_search_get_cat' );
add_action( 'wp_ajax_nopriv_wc_get_categories', 'shopchop_search_get_cat' );


/* =============================================================================
	§ 13 Search Bar Shortcode  [shopchop_search_bar]
   ============================================================================= */

/**
 * Render the search bar HTML.
 *
 * Attribute:
 *   context  "default" | "mobile"  – appended to element IDs to prevent
 *            duplicates when the shortcode is used more than once per page.
 *
 * @param array $atts Shortcode attributes.
 * @return string Shortcode HTML.
 */
function shopchop_search_bar_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'context' => 'default' ), $atts, 'shopchop_search_bar' );

	$input_id  = 'shopchop-search-input__' . esc_attr( $atts['context'] );
	$select_id = 'shopchop-cat-select__'   . esc_attr( $atts['context'] );

	ob_start(); ?>
	<div class="shopchop-search-wrapper" role="search">
		<div class="shopchop-search-bar">
			<svg class="shopchop-search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true"><path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"></path></svg>
			<label class="sr-only" for="<?php echo $input_id; ?>"><?php esc_html_e( 'Search products', 'shopchop' ); ?></label>
			<input type="search" class="shopchop-search-input" id="<?php echo $input_id; ?>" placeholder="Search products" autocomplete="off">
			<button type="button" class="shopchop-search-clear" aria-label="<?php esc_attr_e( 'Clear search', 'shopchop' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256" aria-hidden="true"><path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"></path></svg>
			</button>
			<label class="sr-only" for="<?php echo $select_id; ?>"><?php esc_html_e( 'Category', 'shopchop' ); ?></label>
			<select class="shopchop-cat-select" id="<?php echo $select_id; ?>">
				<option value="all">All Products</option>
			</select>
		</div>
		<div class="shopchop-search-results" style="display:none;"></div>
		<p class="shopchop-search-status sr-only" role="status"></p>
	</div>
	<?php return ob_get_clean();
}
add_shortcode( 'shopchop_search_bar', 'shopchop_search_bar_shortcode' );
