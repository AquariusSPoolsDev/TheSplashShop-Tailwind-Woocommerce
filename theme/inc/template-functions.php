<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package ShopChop
 */

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function shopchop_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'shopchop_pingback_header' );

/**
 * Changes comment form default fields.
 *
 * @param array $defaults The default comment form arguments.
 *
 * @return array Returns the modified fields.
 */
function shopchop_comment_form_defaults( $defaults ) {
	$comment_field = $defaults['comment_field'];

	// Adjust height of comment form.
	$defaults['comment_field'] = preg_replace( '/rows="\d+"/', 'rows="5"', $comment_field );

	return $defaults;
}
add_filter( 'comment_form_defaults', 'shopchop_comment_form_defaults' );

/**
 * Filters the default archive titles.
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
 * Determines whether the post thumbnail can be displayed.
 */
function shopchop_can_show_post_thumbnail() {
	return apply_filters( 'shopchop_can_show_post_thumbnail', ! post_password_required() && ! is_attachment() && has_post_thumbnail() );
}

/**
 * Returns the size for avatars used in the theme.
 */
function shopchop_get_avatar_size() {
	return 60;
}

/**
 * Create the continue reading link
 *
 * @param string $more_string The string shown within the more link.
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

// Filter the excerpt more link.
add_filter( 'excerpt_more', 'shopchop_continue_reading_link' );

// Filter the content more link.
add_filter( 'the_content_more_link', 'shopchop_continue_reading_link' );

/**
 * Outputs a comment in the HTML5 format.
 *
 * This function overrides the default WordPress comment output in HTML5
 * format, adding the required class for Tailwind Typography. Based on the
 * `html5_comment()` function from WordPress core.
 *
 * @param WP_Comment $comment Comment to display.
 * @param array      $args    An array of arguments.
 * @param int        $depth   Depth of the current comment.
 */
function shopchop_html5_comment( $comment, $args, $depth ) {
	$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';

	$commenter          = wp_get_current_commenter();
	$show_pending_links = ! empty( $commenter['comment_author'] );

	if ( $commenter['comment_author_email'] ) {
		$moderation_note = __( 'Your comment is awaiting moderation.', 'shopchop' );
	} else {
		$moderation_note = __( 'Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.', 'shopchop' );
	}
	?>
	<<?php echo esc_attr( $tag ); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $comment->has_children ? 'parent' : '', $comment ); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<footer class="comment-meta">
				<div class="comment-author vcard">
					<?php
					if ( 0 !== $args['avatar_size'] ) {
						echo get_avatar( $comment, $args['avatar_size'] );
					}
					?>
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
				comment_reply_link(
					array_merge(
						$args,
						array(
							'add_below' => 'div-comment',
							'depth'     => $depth,
							'max_depth' => $args['max_depth'],
							'before'    => '<div class="reply">',
							'after'     => '</div>',
						)
					)
				);
			}
			?>
		</article><!-- .comment-body -->
	<?php
}

// Hide Admin Bar
add_filter('show_admin_bar', '__return_false');


/**
 * 
 * CUSTOM: Use own main container to replace default WooCommerce wrapper
 * 
 */

// Removes WooCommerce stylings, leave us blank file to be edited.
add_filter( 'woocommerce_enqueue_styles', '__return_false' );

// Remove WooCommerce wrappers, add custom wrappers
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', 'shopchop_wc_wrapper_start' );
add_action( 'woocommerce_after_main_content', 'shopchop_wc_wrapper_end' );

function shopchop_wc_wrapper_start() {
	?>
    <main id="primary" class="shopchop-wrapper shopchop-woocommerce" role="primary">
	<?php 
}

function shopchop_wc_wrapper_end() {
    ?>
    </main> <!-- #main wrapper close -->
	<?php 
}

/**
 * CUSTOM: Display discounted price and its percentage
 */
add_filter( 'woocommerce_get_price_html', 'shopchop_price_display', 10, 2 );

function shopchop_price_display( $price, $product ) {

    // ---------- NORMAL PRICE ----------
    if ( ! $product->is_on_sale() ) {

        $regular_price = (float) $product->get_regular_price();

        if ( ! $regular_price ) {
            return $price;
        }

        return sprintf(
            '<span class="price-normal">
                <span class="regular">%s</span>
            </span>',
            wc_price( $regular_price )
        );
    }

    // ---------- DISCOUNTED PRICE ----------
    $regular_price = (float) $product->get_regular_price();
    $sale_price    = (float) $product->get_sale_price();

    if ( ! $regular_price || ! $sale_price ) {
        return $price;
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
 * Add custom wrappers to WooCommerce loop items
 */

// --- 1. Remove Default Structure ---
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

// --- 2. Open Main Link with 'group' class & Image Wrapper ---
add_action( 'woocommerce_before_shop_loop_item', function() {
    global $product;
    
    $classes = 'woocommerce-LoopProduct-link woocommerce-loop-product__link group';
    
    echo '<a href="' . esc_url( get_permalink( $product->get_id() ) ) . '" class="' . esc_attr( $classes ) . '">';
}, 5 );

add_action( 'woocommerce_before_shop_loop_item_title', function() {
    echo '<div class="product-image-wrapper">';
}, 5 );

// --- 3. Close Image Wrapper & Open Details Wrapper ---
add_action( 'woocommerce_before_shop_loop_item_title', function() {
    echo '</div>'; // Close .product-image-wrapper
    echo '<div class="product-details-wrapper">';
}, 15 );

// --- 4. Close Details Wrapper & Main Link ---
add_action( 'woocommerce_after_shop_loop_item', function() {
    echo '</div>'; // Close .product-details-wrapper
    echo '</a>'; // Close main link
}, 5 );

// Wrap cart buttons in a container
add_action( 'woocommerce_after_shop_loop_item', function() {
    echo '<div class="product-actions">';
}, 9 );

add_action( 'woocommerce_after_shop_loop_item', function() {
    echo '</div>'; // Close .product-actions
}, 11 );


add_action( 'wp_footer', 'ajax_auto_update_cart_script' );
function ajax_auto_update_cart_script() {
    if ( is_cart() ) {
        ?>
        <script type="text/javascript">
            jQuery( function( $ ) {
                let timeout;
                $( document.body ).on( 'updated_cart_totals', function() {
                    attachQtyListener();
                });

                function attachQtyListener() {
                    $( 'div.woocommerce' ).on( 'change', 'input.qty', function() {
                        if ( timeout !== undefined ) {
                            clearTimeout( timeout );
                        }
                        timeout = setTimeout( function() {
                            $( '[name="update_cart"]' ).trigger( 'click' );
                        }, 500 );
                    });
                }

                attachQtyListener();
            });
        </script>
        <?php
    }
}


// Disable Select2 for WooCommerce country/state fields
add_filter( 'woocommerce_enqueue_styles', 'disable_woo_select2', 99 );
function disable_woo_select2( $enqueue_styles ) {
    wp_dequeue_script( 'select2' );
    wp_deregister_script( 'select2' );
    wp_dequeue_style( 'select2' );
    wp_deregister_style( 'select2' );
    return $enqueue_styles;
}

/**
 * Wrap the Result Count and Ordering dropdown into a custom div
 */

// 1. Open the div BEFORE the result count (Priority 15)
add_action( 'woocommerce_before_shop_loop', 'opening_wrapper_before_listing', 15 );
function opening_wrapper_before_listing() {
    echo '<div class="shop-utility-wrapper">';
}

// 2. Close the div AFTER the ordering form (Priority 35)
add_action( 'woocommerce_before_shop_loop', 'closing_wrapper_before_listing', 35 );
function closing_wrapper_before_listing() {
    echo '</div>';
}

/**
 * Variation Radio Button
 */

// 1. Convert dropdowns to pills
add_filter('woocommerce_dropdown_variation_attribute_options_html', 'pill_button_variation_swatches', 10, 2);
function pill_button_variation_swatches($html, $args) {
    $options = $args['options'];
    $product = $args['product'];
    $attribute = $args['attribute'];
    $selected = $args['selected'];

    if (empty($options) || !$product) return $html;

    $container = '<div class="pill-swatches-container" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '">';
    foreach ($options as $option) {
        $active_class = ($selected === $option) ? 'active' : '';
        $container .= sprintf(
            '<button type="button" class="pill-swatch %s" data-value="%s">%s</button>',
            $active_class, esc_attr($option), esc_html($option)
        );
    }
    $container .= '</div>';

    return '<div style="display:none;">' . $html . '</div>' . $container;
}

// 2. Ensure all variation data is available to JS (Increase AJAX threshold)
add_filter( 'woocommerce_ajax_variation_threshold', function() { return 100; } );

// Form Fields Change
add_filter( 'woocommerce_default_address_fields' , 'shopchop_override_address_fields' );

function shopchop_override_address_fields( $address_fields ) {
    unset($address_fields['last_name']);
    unset($address_fields['address_2']);

    $address_fields['first_name']['label'] = __('Name', 'woocommerce');
    $address_fields['first_name']['placeholder'] = __('Name', 'woocommerce');

    $address_fields['country']['label']   = __('Country', 'woocommerce');
    $address_fields['address_1']['label'] = __('Address', 'woocommerce');
    $address_fields['city']['label']      = __('City', 'woocommerce');
    $address_fields['state']['label']     = __('State', 'woocommerce');
    $address_fields['postcode']['label']  = __('Postcode', 'woocommerce');

	$address_fields['first_name']['autocomplete'] = 'name';

    return $address_fields;
}

add_filter( 'woocommerce_save_account_details_required_fields', 'remove_account_details_last_name', 10, 1 );

function remove_account_details_last_name( $fields ) {
    unset( $fields['account_last_name'] );
    return $fields;
}


// Address Reorder Form Field
// Use this to re-order the fields in your functions.php
add_filter( 'woocommerce_default_address_fields', 'shopchop_reorder_fields' );

function shopchop_reorder_fields( $fields ) {
    $fields['first_name']['priority'] = 10;
    $fields['address_1']['priority']  = 20;
    $fields['postcode']['priority']   = 30; // Moved up
    $fields['city']['priority']       = 40;
    $fields['state']['priority']      = 50;
    $fields['country']['priority']     = 60;
    
    return $fields;
}


// Reviews Rating Modify
remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10 );

add_action( 'woocommerce_review_before_comment_meta', 'shopchop_custom_review_rating', 10 );
function shopchop_custom_review_rating() {
    global $comment;
	$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );

	if ( $rating && wc_review_ratings_enabled() ) : ?>
		<div class="shopchop-rating-wrapper">
			<div class="shopchop-stars" role="img" aria-label="Rated <?php echo $rating; ?> out of 5">
				<?php for ( $i = 1; $i <= 5; $i++ ) : 
					$class = ( $i <= $rating ) ? 'is-filled' : 'is-empty';
				?>
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="shopchop-star <?php echo $class; ?>">
						<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>
					</svg>
				<?php endfor; ?>
			</div>
			
			<span class="shopchop-rating-number"><?php echo number_format($rating, 1); ?> / 5</span>
		</div>
	<?php endif; 
}

// Review Meta Modification
remove_action( 'woocommerce_review_meta', 'woocommerce_review_display_meta', 10 );

add_action( 'woocommerce_review_meta', 'shopchop_hooked_review_meta', 10 );
function shopchop_hooked_review_meta( $comment ) {
    $verified = wc_review_is_from_verified_owner( $comment->comment_ID );
    
    // If awaiting approval, show the message on top next to name
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

// Add review date at bottom
add_action( 'woocommerce_review_after_comment_text', 'shopchop_hooked_review_date', 20 );
function shopchop_hooked_review_date( $comment ) { ?>
	<time class="shopchop-review-date">
		<?php echo esc_html( get_comment_date( wc_date_format() ) ); ?>
	</time>
    <?php
}

// ShopChop Order Number Custom. To be replace from default.
add_filter( 'woocommerce_order_number', 'shopchop_professional_order_format', 1, 2 );

function shopchop_professional_order_format( $order_id, $order ) {
    $prefix = 'TSS'; 

    $date_created = $order->get_date_created();
    $formatted_date = $date_created ? $date_created->date( 'ymd' ) : date( 'ymd' );

    $padded_id = str_pad( $order_id, 5, '0', STR_PAD_LEFT );

    return $prefix . '-' . $formatted_date . '-' . $padded_id;
}

// ShopChop My Account Title Custom Layout
add_action( 'woocommerce_account_content', 'shopchop_account_content_title', 1 );

function shopchop_account_content_title() {
    global $wp;

    // Check which endpoint we are on to show the correct title
    if ( is_wc_endpoint_url( 'orders' ) ) {
        echo '<h1 class="account-content-title">Orders</h1>';
	} elseif ( is_wc_endpoint_url( 'downloads' ) ) {
        echo '<h1 class="account-content-title">Downloads</h1>';
    } elseif ( is_wc_endpoint_url( 'view-order' ) ) {
        echo '<h1 class="account-content-title">Order Details</h1>';
    } elseif ( is_wc_endpoint_url( 'edit-address' ) ) {
        echo '<h1 class="account-content-title">Addresses</h1>';
    } elseif ( is_wc_endpoint_url( 'edit-account' ) ) {
        echo '<h1 class="account-content-title">Account Details</h1>';
    } else {
        echo '<h1 class="account-content-title">Dashboard</h1>';
    }
}