<?php
/**
 * The template for displaying the blog posts index (Posts page)
 *
 * Used when a static front page is set and a Posts page is assigned
 * under Settings > Reading.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ShopChop
 */

get_header();
?>

<section id="primary" class="archive-main-template">
	<main id="main">

			<?php if ( have_posts() ) : ?>

				<header class="archive-page-header">
					<h1 class="archive-page-title"><?php single_post_title(); ?></h1>
				</header>

				<div class="archive-posts-grid">
					<?php while ( have_posts() ) : the_post(); ?>
						<?php get_template_part( 'template-parts/content/content', 'excerpt' ); ?>
					<?php endwhile; ?>
				</div>

				<nav class="archive-pagination">
					<?php
					the_posts_pagination( array(
						'mid_size'  => 2,
						'prev_text' => '&lsaquo;',
						'next_text' => '&rsaquo;',
					) );
					?>
				</nav>

			<?php else : ?>

				<?php get_template_part( 'template-parts/content/content', 'none' ); ?>

			<?php endif; ?>

		</main><!-- #main -->
</section><!-- #primary -->

<?php
get_footer();
