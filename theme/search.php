<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package ShopChop
 */

get_header();

$shopchop_s             = get_search_query();
$shopchop_product_paged = isset( $_GET['product_page'] ) ? max( 1, absint( $_GET['product_page'] ) ) : 1;
$shopchop_post_paged    = isset( $_GET['post_page'] ) ? max( 1, absint( $_GET['post_page'] ) ) : 1;

$shopchop_products_query = new WP_Query( array(
	's'              => $shopchop_s,
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => 8,
	'paged'          => $shopchop_product_paged,
) );

$shopchop_posts_query = new WP_Query( array(
	's'              => $shopchop_s,
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 6,
	'paged'          => $shopchop_post_paged,
) );

$shopchop_has_results = $shopchop_products_query->found_posts > 0 || $shopchop_posts_query->found_posts > 0;
?>

	<section id="primary" class="search-main-template">
		<main id="main">

		<?php if ( $shopchop_has_results ) : ?>

			<header class="page-header">
				<?php
				printf(
					/* translators: 1: search result title. 2: search term. */
					'<h1 class="page-title">%1$s <span>%2$s</span></h1>',
					esc_html__( 'Search results for:', 'shopchop' ),
					esc_html( $shopchop_s )
				);
				?>
			</header><!-- .page-header -->

			<?php if ( $shopchop_products_query->have_posts() ) : ?>
				<div id="search-products-dynamic">
					<h2 class="shop-section-title"><?php esc_html_e( 'Products', 'shopchop' ); ?></h2>
					<ul class="products columns-4">
						<?php while ( $shopchop_products_query->have_posts() ) : $shopchop_products_query->the_post(); ?>
							<?php wc_get_template_part( 'content', 'product' ); ?>
						<?php endwhile; ?>
					</ul>

					<?php if ( $shopchop_products_query->max_num_pages > 1 ) : ?>
						<nav class="search-pagination">
							<?php
							echo paginate_links( array(
								'base'      => add_query_arg( 'product_page', '%#%' ),
								'format'    => '',
								'current'   => $shopchop_product_paged,
								'total'     => $shopchop_products_query->max_num_pages,
								'prev_text' => '&lsaquo;',
								'next_text' => '&rsaquo;',
								'mid_size'  => 2,
								'type'      => 'list',
							) );
							?>
						</nav>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

			<?php if ( $shopchop_posts_query->have_posts() ) : ?>
				<div id="search-posts-dynamic">
					<h2 class="shop-section-title"><?php esc_html_e( 'Posts', 'shopchop' ); ?></h2>
					<div class="search-results-grid">
						<?php while ( $shopchop_posts_query->have_posts() ) : $shopchop_posts_query->the_post(); ?>
							<?php get_template_part( 'template-parts/content/content', 'excerpt' ); ?>
						<?php endwhile; ?>
					</div>

					<?php if ( $shopchop_posts_query->max_num_pages > 1 ) : ?>
						<nav class="search-pagination">
							<?php
							echo paginate_links( array(
								'base'      => add_query_arg( 'post_page', '%#%' ),
								'format'    => '',
								'current'   => $shopchop_post_paged,
								'total'     => $shopchop_posts_query->max_num_pages,
								'prev_text' => '&lsaquo;',
								'next_text' => '&rsaquo;',
								'mid_size'  => 2,
								'type'      => 'list',
							) );
							?>
						</nav>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

		<?php else : ?>

			<?php get_template_part( 'template-parts/content/content', 'none' ); ?>

		<?php endif; ?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();
