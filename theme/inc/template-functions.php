<?php

/**
 * ShopChop – Custom Theme Functions
 *
 * Hooks, filters, and helpers that extend the base _Underscores theme and
 * integrate tightly with WooCommerce.
 *
 * Table of Contents
 * ─────────────────────────────────────────────────────────────────────────────
 *  § 1  Core WordPress Hooks          (pingback, comments, archive titles …)
 *  § 2  WooCommerce Layout            (wrapper override, styles, wrappers)
 *  § 3  Product Display               (price, loop structure, category listing)
 *  § 4  Variation Swatches            (pill/radio replacement for <select>)
 *  § 5  Checkout & Address Fields     (field labels, ordering, Select2 removal)
 *  § 6  Reviews & Ratings             (custom star display, meta, date)
 *  § 7  Orders                        (order number format, status notes)
 *  § 8  My Account                    (content titles, auth wrapper, redirects)
 *  § 9  Demo Store & Checkout Layout  (notice relocation, payment hook)
 *  § 10 Authentication                (generic error messages)
 *  § 11 Thank-You Page                (next-steps section)
 *  § 12 AJAX Search                   (product search + category endpoints)
 *  § 13 Search Bar Shortcode          ([shopchop_search_bar])
 *  § 14 Mini Cart AJAX                (get cart, remove item, fragments)
 *  § 15 Mini Cart Shortcodes          ([shopchop_mini_cart] etc.)
 *  § 16 Custom Stock Statuses         (Pre-Order, Coming Soon)
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * @package ShopChop
 */



/* =============================================================================
	§ 1  Core WordPress Hooks
   ============================================================================= */

/**
 * Emit a pingback auto-discovery header for singular, pingable posts.
 */
function shopchop_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'shopchop_pingback_header' );



/**
 * Reduce the comment textarea height to 5 rows.
 *
 * @param array $defaults Default comment-form arguments.
 * @return array Modified arguments.
 */
function shopchop_comment_form_defaults( $defaults ) {
	$defaults['comment_field'] = preg_replace( '/rows="\d+"/', 'rows="5"', $defaults['comment_field'] );
	return $defaults;
}
add_filter( 'comment_form_defaults', 'shopchop_comment_form_defaults' );



/**
 * Replace the default archive title with a labelled, translated version.
 *
 * @return string Translated archive title with a <span>-wrapped term/date.
 */
function shopchop_get_the_archive_title() {
	if ( is_category() ) {
		$title = __( 'Category Archives: ', 'shopchop' ) . '<span>' . single_term_title( '', false ) . '</span>';
	} elseif ( is_tag() ) {
		$title = __( 'Tag Archives: ', 'shopchop' ) . '<span>' . single_term_title( '', false ) . '</span>';
	} elseif ( is_author() ) {
		$title = __( 'Author Archives: ', 'shopchop' ) . '<span>' . get_the_author_meta( 'display_name' ) . '</span>';
	} elseif ( is_year() ) {
		$title = __( 'Yearly Archives: ', 'shopchop' ) . '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', 'shopchop' ) ) . '</span>';
	} elseif ( is_month() ) {
		$title = __( 'Monthly Archives: ', 'shopchop' ) . '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'shopchop' ) ) . '</span>';
	} elseif ( is_day() ) {
		$title = __( 'Daily Archives: ', 'shopchop' ) . '<span>' . get_the_date() . '</span>';
	} elseif ( is_post_type_archive() ) {
		$cpt   = get_post_type_object( get_queried_object()->name );
		$title = sprintf(
			/* translators: %s: Post type singular name */
			esc_html__( '%s Archives', 'shopchop' ),
			$cpt->labels->singular_name
		);
	} elseif ( is_tax() ) {
		$tax   = get_taxonomy( get_queried_object()->taxonomy );
		$title = sprintf(
			/* translators: %s: Taxonomy singular name */
			esc_html__( '%s Archives', 'shopchop' ),
			$tax->labels->singular_name
		);
	} else {
		$title = __( 'Archives:', 'shopchop' );
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'shopchop_get_the_archive_title' );



/**
 * Return true when a post thumbnail may safely be displayed.
 *
 * @return bool
 */
function shopchop_can_show_post_thumbnail() {
	return apply_filters(
		'shopchop_can_show_post_thumbnail',
		! post_password_required() && ! is_attachment() && has_post_thumbnail()
	);
}



/**
 * Return the avatar size (px) used throughout the theme.
 *
 * @return int
 */
function shopchop_get_avatar_size() {
	return 60;
}



/**
 * Build the "Continue reading" link appended to excerpts and content.
 *
 * @param string $more_string The default more string.
 * @return string Modified more string with a permalink anchor.
 */
function shopchop_continue_reading_link( $more_string ) {
	if ( ! is_admin() ) {
		$continue_reading = sprintf(
			/* translators: %s: Name of current post. */
			wp_kses( __( 'Continue reading %s', 'shopchop' ), array( 'span' => array( 'class' => array() ) ) ),
			the_title( '<span class="sr-only">"', '"</span>', false )
		);
		$more_string = '<a href="' . esc_url( get_permalink() ) . '">' . $continue_reading . '</a>';
	}
	return $more_string;
}
add_filter( 'excerpt_more',          'shopchop_continue_reading_link' );
add_filter( 'the_content_more_link', 'shopchop_continue_reading_link' );



/**
 * Render a single comment in HTML5 format.
 *
 * Overrides WordPress core output to inject the Tailwind Typography class.
 * Based on `html5_comment()` in WordPress core.
 *
 * @param WP_Comment $comment Comment to display.
 * @param array      $args    Comment-list arguments.
 * @param int        $depth   Nesting depth of the current comment.
 */
function shopchop_html5_comment( $comment, $args, $depth ) {
	$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';

	$commenter          = wp_get_current_commenter();
	$show_pending_links = ! empty( $commenter['comment_author'] );

	$moderation_note = $commenter['comment_author_email']
		? __( 'Your comment is awaiting moderation.', 'shopchop' )
		: __( 'Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.', 'shopchop' );
	?>
	<<?php echo esc_attr( $tag ); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $comment->has_children ? 'parent' : '', $comment ); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<footer class="comment-meta">
				<div class="comment-author vcard">
					<?php if ( 0 !== $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
					<?php
					$comment_author = get_comment_author_link( $comment );
					if ( '0' === $comment->comment_approved && ! $show_pending_links ) {
						$comment_author = get_comment_author( $comment );
					}
					printf(
						/* translators: %s: Comment author link. */
						wp_kses_post( __( '%s <span class="says">says:</span>', 'shopchop' ) ),
						sprintf( '<b class="fn">%s</b>', wp_kses_post( $comment_author ) )
					);
					?>
				</div><!-- .comment-author -->

				<div class="comment-metadata">
					<?php
					printf(
						'<a href="%s"><time datetime="%s">%s</time></a>',
						esc_url( get_comment_link( $comment, $args ) ),
						esc_attr( get_comment_time( 'c' ) ),
						esc_html(
							sprintf(
								/* translators: 1: Comment date, 2: Comment time. */
								__( '%1$s at %2$s', 'shopchop' ),
								get_comment_date( '', $comment ),
								get_comment_time()
							)
						)
					);
					edit_comment_link( __( 'Edit', 'shopchop' ), ' <span class="edit-link">', '</span>' );
					?>
				</div><!-- .comment-metadata -->

				<?php if ( '0' === $comment->comment_approved ) : ?>
					<em class="comment-awaiting-moderation"><?php echo esc_html( $moderation_note ); ?></em>
				<?php endif; ?>
			</footer><!-- .comment-meta -->

			<div <?php shopchop_content_class( 'comment-content' ); ?>>
				<?php comment_text(); ?>
			</div><!-- .comment-content -->

			<?php
			if ( '1' === $comment->comment_approved || $show_pending_links ) {
				comment_reply_link( array_merge( $args, array(
					'add_below' => 'div-comment',
					'depth'     => $depth,
					'max_depth' => $args['max_depth'],
					'before'    => '<div class="reply">',
					'after'     => '</div>',
				) ) );
			}
			?>
		</article><!-- .comment-body -->
	<?php
}



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
	<main id="primary" class="shopchop-wrapper shopchop-woocommerce" role="primary">
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

	if ( ! $product->is_on_sale() ) {
		$regular_price = (float) $product->get_regular_price();
		if ( ! $regular_price ) return $price;

		return sprintf(
			'<span class="price-normal"><span class="regular">%s</span></span>',
			wc_price( $regular_price )
		);
	}

	$regular_price = (float) $product->get_regular_price();
	$sale_price    = (float) $product->get_sale_price();
	if ( ! $regular_price || ! $sale_price ) return $price;

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
			<span class="cat-product-count"><?php echo esc_html( $category->count ); ?> products</span>
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
		echo '<span class="out-of-stock-badge">' . esc_html__( 'Out of Stock', 'woocommerce' ) . '</span>';
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



/* =============================================================================
	§ 5  Checkout & Address Fields
   ============================================================================= */

/**
 * Remove Select2 (we use native <select> elements styled via CSS).
 *
 * @param array $enqueue_styles Registered WooCommerce styles.
 * @return array Unchanged (removal happens via wp_dequeue_*).
 */
add_filter( 'woocommerce_enqueue_styles', 'shopchop_disable_select2', 9999 );
function shopchop_disable_select2( $enqueue_styles ) {
	wp_dequeue_style( 'select2' );
	wp_deregister_style( 'select2' );
	wp_dequeue_script( 'selectWoo' );
	wp_deregister_script( 'selectWoo' );
	return $enqueue_styles;
}



/**
 * Customise default address fields:
 * – Remove last_name and address_2.
 * – Relabel remaining fields for a Malaysian audience.
 * – Reorder fields for a cleaner checkout flow.
 *
 * @param array $fields Default address fields.
 * @return array Modified fields.
 */
add_filter( 'woocommerce_default_address_fields', 'shopchop_override_address_fields' );
function shopchop_override_address_fields( $fields ) {
	unset( $fields['last_name'] );
	unset( $fields['address_2'] );

	// Labels
	$fields['first_name']['label'] = __( 'Name',     'woocommerce' );
	$fields['country']['label']    = __( 'Country',  'woocommerce' );
	$fields['address_1']['label']  = __( 'Address',  'woocommerce' );
	$fields['city']['label']       = __( 'City',     'woocommerce' );
	$fields['state']['label']      = __( 'State',    'woocommerce' );
	$fields['postcode']['label']   = __( 'Postcode', 'woocommerce' );
	$fields['phone']['label']      = __( 'Phone',    'woocommerce' );
	$fields['email']['label']      = __( 'Email',    'woocommerce' );

	// Autocomplete hint
	$fields['first_name']['autocomplete'] = 'name';

	// Placeholders (Malaysia-specific examples)
	$fields['first_name']['placeholder'] = __( 'Name',                                   'woocommerce' );
	$fields['address_1']['placeholder']  = __( '3, Jalan Pembangunan, Taman Perumahan',  'woocommerce' );
	$fields['city']['placeholder']       = __( 'Johor Bahru',                            'woocommerce' );
	$fields['postcode']['placeholder']   = __( '80000',                                  'woocommerce' );
	$fields['phone']['placeholder']      = __( '+60123456789',                           'woocommerce' );
	$fields['email']['placeholder']      = __( 'mail@example.com',                       'woocommerce' );

	return $fields;
}



/**
 * Remove last_name from the required-fields list on the Edit Account screen.
 *
 * @param array $fields Required account fields.
 * @return array Modified fields.
 */
add_filter( 'woocommerce_save_account_details_required_fields', 'shopchop_remove_last_name_field' );
function shopchop_remove_last_name_field( $fields ) {
	unset( $fields['account_last_name'] );
	return $fields;
}



/**
 * Reorder address fields so postcode appears before city.
 *
 * @param array $fields Address fields.
 * @return array Fields with updated priorities.
 */
add_filter( 'woocommerce_default_address_fields', 'shopchop_reorder_fields' );
function shopchop_reorder_fields( $fields ) {
	$fields['first_name']['priority'] = 10;
	$fields['address_1']['priority']  = 20;
	$fields['postcode']['priority']   = 30;
	$fields['city']['priority']       = 40;
	$fields['state']['priority']      = 50;
	$fields['country']['priority']    = 60;
	return $fields;
}



/* =============================================================================
	§ 6  Reviews & Ratings
   ============================================================================= */

/**
 * Replace WooCommerce's default star rating with a custom SVG star row
 * that also displays the numeric score (e.g. "4.0 / 5").
 */
remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10 );
add_action(    'woocommerce_review_before_comment_meta', 'shopchop_custom_review_rating',     10 );

function shopchop_custom_review_rating() {
	global $comment;
	$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );

	if ( ! $rating || ! wc_review_ratings_enabled() ) return;
	?>
	<div class="shopchop-rating-wrapper">
		<div class="shopchop-stars" role="img" aria-label="Rated <?php echo $rating; ?> out of 5">
			<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="shopchop-star <?php echo $i <= $rating ? 'is-filled' : 'is-empty'; ?>">
					<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>
				</svg>
			<?php endfor; ?>
		</div>
		<span class="shopchop-rating-number"><?php echo number_format( $rating, 1 ); ?> / 5</span>
	</div>
	<?php
}



/**
 * Replace the default review meta (date/author line) with a custom layout
 * that shows the author name and a verified-owner badge inline.
 *
 * @param WP_Comment $comment Current comment.
 */
remove_action( 'woocommerce_review_meta', 'woocommerce_review_display_meta', 10 );
add_action(    'woocommerce_review_meta', 'shopchop_hooked_review_meta',     10 );

function shopchop_hooked_review_meta( $comment ) {
	$verified = wc_review_is_from_verified_owner( $comment->comment_ID );

	if ( '0' === $comment->comment_approved ) {
		echo '<strong class="woocommerce-review__author">' . get_comment_author() . '</strong><br>';
		echo '<em class="woocommerce-review__awaiting-approval">Your review is awaiting approval</em>';
	} else {
		echo '<strong class="woocommerce-review__author">' . get_comment_author() . '</strong> ';
		if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && $verified ) {
			echo '<em class="woocommerce-review__verified verified">(verified owner)</em>';
		}
	}
}



/**
 * Output the review date below the review body.
 *
 * @param WP_Comment $comment Current comment.
 */
add_action( 'woocommerce_review_after_comment_text', 'shopchop_hooked_review_date', 20 );
function shopchop_hooked_review_date( $comment ) { ?>
	<time class="shopchop-review-date"><?php echo esc_html( get_comment_date( wc_date_format() ) ); ?></time>
<?php }



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
		__( 'Your order is ready! It has been dispatched to the courier for delivery.', 'woocommerce' ),
		true // visible to customer
	);
}



/* =============================================================================
	§ 8  My Account
   ============================================================================= */

/**
 * Insert a contextual <h1> title at the top of every My Account content area.
 */
add_action( 'woocommerce_account_content', 'shopchop_account_content_title', 1 );
function shopchop_account_content_title() {
	$endpoint_titles = array(
		'orders'       => 'Orders',
		'downloads'    => 'Downloads',
		'view-order'   => 'Order Details',
		'edit-address' => 'Addresses',
		'edit-account' => 'Account Details',
	);
	
	$title = 'Dashboard'; // default
	foreach ( $endpoint_titles as $endpoint => $label ) {
		if ( is_wc_endpoint_url( $endpoint ) ) {
			$title = $label;
			break;
		}
	}

	echo '<h1 class="account-content-title">' . esc_html( $title ) . '</h1>';
}



/**
 * Wrap all authentication forms (login, register, lost/reset password)
 * in a shared <div class="wc-auth-wrapper"> for consistent styling.
 */
function shopchop_auth_wrapper_start() { echo '<div class="wc-auth-wrapper">'; }
function shopchop_auth_wrapper_end()   { echo '</div>'; }

add_action( 'woocommerce_before_customer_login_form',          'shopchop_auth_wrapper_start', 1 );
add_action( 'woocommerce_after_customer_login_form',           'shopchop_auth_wrapper_end'     );
add_action( 'woocommerce_before_lost_password_form',           'shopchop_auth_wrapper_start', 1 );
add_action( 'woocommerce_after_lost_password_form',            'shopchop_auth_wrapper_end'     );
add_action( 'woocommerce_before_reset_password_form',          'shopchop_auth_wrapper_start', 1 );
add_action( 'woocommerce_after_reset_password_form',           'shopchop_auth_wrapper_end'     );
add_action( 'woocommerce_before_lost_password_confirmation_message', 'shopchop_auth_wrapper_start', 1 );
add_action( 'woocommerce_after_lost_password_confirmation_message',  'shopchop_auth_wrapper_end'     );



/**
 * Redirect bare /login and /register slugs to the WooCommerce My Account page.
 * Only fires for non-logged-in users.
 */
add_action( 'template_redirect', function () {
	if ( is_user_logged_in() ) return;

	$path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
	if ( in_array( $path, array( 'login', 'register' ), true ) ) {
		wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
		exit;
	}
} );



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



/* =============================================================================
	§ 10 Authentication
   ============================================================================= */

/**
 * Return a generic error message on failed login to avoid username enumeration.
 *
 * @param WP_User|WP_Error $user     Authentication result.
 * @param string           $username Submitted username.
 * @param string           $password Submitted password.
 * @return WP_User|WP_Error
 */
add_filter( 'authenticate', 'shopchop_remove_login_errors', 20, 3 );
function shopchop_remove_login_errors( $user, $username, $password ) {
	if ( empty( $username ) || empty( $password ) || is_wp_error( $user ) ) {
		return new WP_Error(
			'authentication_failed',
			__( '<strong>Error</strong>: Invalid username or password. Please try again.' )
		);
	}
	return $user;
}



/* =============================================================================
	§ 11 Thank-You Page
   ============================================================================= */

/**
 * Append a "What to do Next?" section below the standard order confirmation.
 *
 * @param int $order_id The newly placed order's ID.
 */
add_action( 'woocommerce_thankyou', 'shopchop_add_next_steps', 10 );
function shopchop_add_next_steps( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) return;
	?>
	<section class="wc-next-steps-order">
		<h2 class="wc-next-steps-title">What to do Next?</h2>
		<ul class="list-disc ml-5 space-y-1">
			<li><strong>Order Confirmation:</strong> You'll receive an email notification as soon as your order is processed and ready for shipment.</li>
			<li><strong>Track Your Package:</strong> Once dispatched, a tracking number will be sent to you to monitor your delivery status.</li>
			<li>
				<strong>Need Assistance?</strong> Contact us through
				<a href="https://wa.me/" class="underline! font-bold text-primary-400" target="_blank" rel="noreferrer">WhatsApp</a>
				or email us at
				<a href="mailto:<?php echo get_option( 'woocommerce_email_from_address' ); ?>" class="underline! font-bold text-primary-400" rel="noreferrer"><?php echo get_option( 'woocommerce_email_from_address' ); ?></a>
				with your Order ID: <strong><?php echo $order->get_order_number(); ?></strong>
			</li>
		</ul>
	</section>
	<?php
}



/* =============================================================================
	§ 12 AJAX Search
   ============================================================================= */

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
	check_ajax_referer( 'wc_ajax_search_nonce', 'nonce' );

	$search_term = isset( $_POST['search_term'] ) ? sanitize_text_field( $_POST['search_term'] ) : '';
	$category    = isset( $_POST['category'] )    ? sanitize_text_field( $_POST['category'] )    : '';

	if ( empty( $search_term ) ) {
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
	check_ajax_referer( 'wc_ajax_search_nonce', 'nonce' );

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
	<div class="shopchop-search-wrapper">
		<div class="shopchop-search-bar">
			<input type="text" class="shopchop-search-input" id="<?php echo $input_id; ?>" placeholder="Search Here..." autocomplete="off">
			<select class="shopchop-cat-select" id="<?php echo $select_id; ?>">
				<option value="all">All Products</option>
			</select>
		</div>
		<div class="shopchop-search-results" style="display:none;"></div>
	</div>
	<?php return ob_get_clean();
}
add_shortcode( 'shopchop_search_bar', 'shopchop_search_bar_shortcode' );



/* =============================================================================
	§ 14 Mini Cart AJAX
   ============================================================================= */

/**
 * Return the current mini-cart HTML, count, and totals.
 *
 * Accepts: POST action=shopchop_get_mini_cart, nonce.
 */
function shopchop_get_mini_cart() {
	check_ajax_referer( 'wc_ajax_search_nonce', 'nonce' );

	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

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
	check_ajax_referer( 'wc_ajax_search_nonce', 'nonce' );

	$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( $_POST['cart_item_key'] ) : '';

	if ( empty( $cart_item_key ) ) {
		wp_send_json_error( array( 'message' => 'Invalid cart item' ) );
		return;
	}

	WC()->cart->remove_cart_item( $cart_item_key );

	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

	wp_send_json_success( array(
		'cart_html'     => $mini_cart,
		'cart_count'    => WC()->cart->get_cart_contents_count(),
		'cart_total'    => WC()->cart->get_cart_total(),
		'cart_is_empty' => WC()->cart->is_empty(),
		'message'       => 'Item removed from cart',
	) );
}
add_action( 'wp_ajax_shopchop_remove_cart_item',        'shopchop_remove_cart_item' );
add_action( 'wp_ajax_nopriv_shopchop_remove_cart_item', 'shopchop_remove_cart_item' );



/**
 * Push cart-count fragments so WooCommerce's fragment system keeps
 * the badge and item-count text in sync after any cart mutation.
 *
 * @param array $fragments Existing fragments.
 * @return array Updated fragments.
 */
function shopchop_cart_fragments( $fragments ) {
	$count = WC()->cart->get_cart_contents_count();
	$word  = $count === 1 ? 'item' : 'items';

	ob_start(); ?>
	<span class="cart-count-badge"><?php echo $count >= 0 ? $count : ''; ?></span>
	<?php $fragments['.cart-count-badge'] = ob_get_clean();

	ob_start(); ?>
	<span class="cart-items-count"><span class="count-number"><?php echo $count; ?></span> <?php echo $word; ?></span>
	<?php $fragments['.cart-items-count'] = ob_get_clean();

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
		<a href="<?php echo wc_get_cart_url(); ?>" class="shopchop-cart-trigger" aria-label="Shopping Cart" aria-expanded="false">
			<div class="cart-icon-wrapper">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cart-icon">
					<circle cx="8" cy="21" r="1"/>
					<circle cx="19" cy="21" r="1"/>
					<path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
				</svg>
				<span class="cart-count-badge"><?php echo $count >= 0 ? $count : ''; ?></span>
			</div>
			<span class="cart-label">Cart</span>
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
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cart-icon">
			<circle cx="8" cy="21" r="1"/>
			<circle cx="19" cy="21" r="1"/>
			<path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
		</svg>
		<span class="cart-count-badge"><?php echo $count >= 0 ? $count : ''; ?></span>
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
		<h3>Cart
			<span>(<span class="cart-items-count"><span class="count-number"><?php echo $count; ?></span> <?php echo $word; ?></span>)</span>
		</h3>
		<button id="cart-close">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M18 6 6 18"/><path d="m6 6 12 12"/>
			</svg>
		</button>
	</div>
	<div class="mobile-cart-content">
		<div class="cart-loading">Loading cart...</div>
	</div>
	<?php return ob_get_clean();
}
add_shortcode( 'shopchop_mobile_cart_details_display', 'shopchop_mobile_cart_details' );



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
 * Render styled marks for custom stock statuses in the WP-Admin product list.
 *
 * @param string     $html    Default admin stock HTML.
 * @param WC_Product $product Current product.
 * @return string Modified admin HTML.
 */
add_filter( 'woocommerce_admin_stock_html', function ( $html, $product ) {
	$status = $product->get_stock_status();

	if ( $status === 'pre_order' ) {
		return '<mark class="pre-order">'   . __( 'Pre-Order Item', 'shopchop' ) . '</mark>';
	}
	if ( $status === 'coming_soon' ) {
		return '<mark class="coming-soon">' . __( 'Coming Soon',    'shopchop' ) . '</mark>';
	}

	return $html;
}, 10, 2 );



/**
 * Inject admin-only CSS to style status badges and tighten the product list table.
 * Scoped to the Products list screen only.
 */
add_action( 'admin_head', 'shopchop_admin_custom_status_styles' );
function shopchop_admin_custom_status_styles() {
	$screen = get_current_screen();
	if ( ! $screen || $screen->id !== 'edit-product' ) return;
	?>
	<style>
		mark.instock     { background:#d1fae5!important; color:#065f46!important; padding:2px 8px; border-radius:4px; font-weight:600!important; }
		mark.outofstock  { background:#ffe4e6!important; color:#9f1239!important; padding:2px 8px; border-radius:4px; font-weight:600!important; }
		mark.pre-order   { background:#d1ecff; color:#0073aa; padding:2px 8px; border-radius:4px; font-weight:600; }
		mark.coming-soon { background:#fff3cd; color:#b45309; padding:2px 8px; border-radius:4px; font-weight:600; }

		.wp-list-table .column-thumb { width:80px!important; }
		.thumb.column-thumb .attachment-thumbnail { max-width:80px; max-height:80px; }

		.wp-list-table .column-name a.row-title {
			display:-webkit-box;
			-webkit-line-clamp:1;
			-webkit-box-orient:vertical;
			overflow:hidden;
			max-width:300px;
		}

		.wp-list-table .column-cogs_value,
		.wp-list-table th#cost { display:none; }

		.wp-list-table .column-is_in_stock,
		.wp-list-table .column-price { width:20ch!important; }

		.wp-list-table .column-price .discount-price { display:flex; align-items:center; gap:4px; flex-wrap:wrap; white-space:nowrap; }
		.wp-list-table .column-price .discount-price del.regular  { color:#999; font-size:.8rem; }
		.wp-list-table .column-price .discount-price .discount    { background:#ffe4e6; color:#9f1239; font-size:.7rem; font-weight:700; padding:1px 5px; border-radius:4px; }
		.wp-list-table .column-price .discount-price .sale        { font-weight:700; color:#065f46; }
	</style>
	<?php
}
