<?php
/**
 * Template part for displaying single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ShopChop
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">

		<?php
		// [ CATEGORY ]
		$categories_list = get_the_category_list( ', ' );
		if ( $categories_list ) : ?>
			<div class="entry-categories">
				<?php echo $categories_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>

		<?php // [ TITLE ] ?>
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<?php if ( 'post' === get_post_type() ) : ?>

			<?php // [ AUTHOR ] [ DATE ] ?>
			<div class="entry-byline">
				<span class="entry-author">
					<span class="sr-only"><?php esc_html_e( 'Posted by', 'shopchop' ); ?></span>
					<span class="author vcard">
						<a class="url fn n" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
							<?php echo get_avatar( get_the_author_meta( 'ID' ), 32 ); ?>
							<?php echo esc_html( get_the_author() ); ?>
						</a>
					</span>
				</span>

				<span class="entry-date">
					<?php
					$time_string = '<time class="published updated" datetime="%1$s">%2$s</time>';
					if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
						$time_string = '<time class="published" datetime="%1$s">%2$s</time>'
							. '<span class="date-separator">·</span>'
							. '<time class="updated" datetime="%3$s">' . esc_html__( 'Updated', 'shopchop' ) . ' %4$s</time>';
					}
					printf(
						$time_string, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						esc_attr( get_the_date( DATE_W3C ) ),
						esc_html( get_the_date() ),
						esc_attr( get_the_modified_date( DATE_W3C ) ),
						esc_html( get_the_modified_date() )
					);
					?>
				</span>
			</div><!-- .entry-byline -->

			<?php // [ TAGS ] ?>
			<?php $tags_list = get_the_tag_list( '', '' ); ?>
			<?php if ( $tags_list ) : ?>
				<div class="entry-tags">
					<span class="sr-only"><?php esc_html_e( 'Tags:', 'shopchop' ); ?></span>
					<?php echo $tags_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

	</header><!-- .entry-header -->

	<?php // [ IMAGE THUMBNAIL ] ?>
	<?php shopchop_post_thumbnail(); ?>

	<?php // [ BLOG CONTENT ] ?>
	<div <?php shopchop_content_class( 'entry-content' ); ?>>
		<?php
		the_content(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers. */
					__( 'Continue reading<span class="sr-only"> "%s"</span>', 'shopchop' ),
					array( 'span' => array( 'class' => array() ) )
				),
				get_the_title()
			)
		);

		wp_link_pages( array(
			'before' => '<div>' . __( 'Pages:', 'shopchop' ),
			'after'  => '</div>',
		) );
		?>
	</div><!-- .entry-content -->

	<?php // [ AUTHOR PORTFOLIO ] ?>
	<?php if ( 'post' === get_post_type() ) : ?>
		<div class="entry-author-card">
			<div class="author-avatar">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 64 ); ?>
			</div>
			<div class="author-info">
				<span class="author-label"><?php esc_html_e( 'Written by', 'shopchop' ); ?></span>
				<a class="author-name" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
					<?php echo esc_html( get_the_author() ); ?>
				</a>
				<?php $bio = get_the_author_meta( 'description' ); ?>
				<?php if ( $bio ) : ?>
					<p class="author-bio"><?php echo esc_html( $bio ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<footer class="entry-footer">
		<?php edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers. */
					__( 'Edit <span class="sr-only">%s</span>', 'shopchop' ),
					array( 'span' => array( 'class' => array() ) )
				),
				get_the_title()
			)
		); ?>
	</footer><!-- .entry-footer -->

</article><!-- #post-<?php the_ID(); ?> -->
