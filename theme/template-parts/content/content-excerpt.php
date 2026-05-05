<?php
/**
 * Template part for displaying post archives and search results
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ShopChop
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'archive-post-card group' ); ?>>

	<?php // [ THUMBNAIL ] ?>
	<div class="archive-card-thumbnail">
		<?php if ( has_post_thumbnail() ) : ?>
			<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<?php the_post_thumbnail( 'large' ); ?>
			</a>
		<?php else : ?>
			<div class="archive-card-no-image"></div>
		<?php endif; ?>
	</div>

	<div class="archive-card-body">

		<?php // [ CATEGORIES ] ?>
		<?php $categories_list = get_the_category_list( '' ); ?>
		<?php if ( $categories_list ) : ?>
			<div class="archive-card-categories">
				<?php echo $categories_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>

		<?php // [ TITLE ] ?>
		<h2 class="archive-card-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<?php // [ BYLINE: author + date ] ?>
		<div class="archive-card-byline">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 28 ); ?>
			<span class="archive-card-author"><?php echo esc_html( get_the_author() ); ?></span>
			<span class="archive-card-dot">·</span>
			<time class="archive-card-date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
				<?php echo esc_html( get_the_date() ); ?>
			</time>
		</div>

		<?php // [ EXCERPT ] ?>
		<?php if ( has_excerpt() || get_the_excerpt() ) : ?>
			<p class="archive-card-excerpt"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<?php // [ READ MORE ] ?>
		<a href="<?php the_permalink(); ?>" class="archive-card-readmore">
			<?php esc_html_e( 'Read more', 'shopchop' ); ?>
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4" aria-hidden="true">
				<path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
			</svg>
		</a>

	</div>

</article><!-- #post-<?php the_ID(); ?> -->
