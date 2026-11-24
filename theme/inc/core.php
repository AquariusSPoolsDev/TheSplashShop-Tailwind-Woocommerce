<?php
/**
 * Core WordPress hooks: pingback, comments, archive titles, excerpts.
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
				<div class="author-avatar">
					<?php if ( 0 !== $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
				</div>
				<div class="author-comment-metadata">
					<div class="author-name">
						<?php
						$comment_author = get_comment_author_link( $comment );
						if ( '0' === $comment->comment_approved && ! $show_pending_links ) {
							$comment_author = get_comment_author( $comment );
						}
						printf(wp_kses_post( $comment_author ));
						?>
					</div>
					<div class="author-time">
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
					</div>
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
