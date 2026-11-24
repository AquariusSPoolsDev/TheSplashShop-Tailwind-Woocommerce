<?php
/**
 * Reviews and ratings: star display, review meta and date.
 *
 * @package ShopChop
 */


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
				<?php if ( $i <= $rating ) : ?>
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" class="shopchop-star is-filled" aria-hidden="true"><path d="M234.29,114.85l-45,38.83L203,211.75a16.4,16.4,0,0,1-24.5,17.82L128,198.49,77.47,229.57A16.4,16.4,0,0,1,53,211.75l13.76-58.07-45-38.83A16.46,16.46,0,0,1,31.08,86l59-4.76,22.76-55.08a16.36,16.36,0,0,1,30.27,0l22.75,55.08,59,4.76a16.46,16.46,0,0,1,9.37,28.86Z"></path></svg>
				<?php else : ?>
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" class="shopchop-star is-empty" aria-hidden="true"><path d="M239.18,97.26A16.38,16.38,0,0,0,224.92,86l-59-4.76L143.14,26.15a16.36,16.36,0,0,0-30.27,0L90.11,81.23,31.08,86a16.46,16.46,0,0,0-9.37,28.86l45,38.83L53,211.75a16.38,16.38,0,0,0,24.5,17.82L128,198.49l50.53,31.08A16.4,16.4,0,0,0,203,211.75l-13.76-58.07,45-38.83A16.43,16.43,0,0,0,239.18,97.26Zm-15.34,5.47-48.7,42a8,8,0,0,0-2.56,7.91l14.88,62.8a.37.37,0,0,1-.17.48c-.18.14-.23.11-.38,0l-54.72-33.65a8,8,0,0,0-8.38,0L69.09,215.94c-.15.09-.19.12-.38,0a.37.37,0,0,1-.17-.48l14.88-62.8a8,8,0,0,0-2.56-7.91l-48.7-42c-.12-.1-.23-.19-.13-.5s.18-.27.33-.29l63.92-5.16A8,8,0,0,0,103,91.86l24.62-59.61c.08-.17.11-.25.35-.25s.27.08.35.25L153,91.86a8,8,0,0,0,6.75,4.92l63.92,5.16c.15,0,.24,0,.33.29S224,102.63,223.84,102.73Z"></path></svg>
				<?php endif; ?>
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
		echo '<strong class="woocommerce-review__author">' . esc_html( get_comment_author() ) . '</strong><br>';
		echo '<em class="woocommerce-review__awaiting-approval">Your review is awaiting approval</em>';
	} else {
		echo '<strong class="woocommerce-review__author">' . esc_html( get_comment_author() ) . '</strong> ';
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
