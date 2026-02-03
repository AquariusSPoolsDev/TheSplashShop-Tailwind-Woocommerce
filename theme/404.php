<?php

/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package ShopChop
 */

get_header();
?>

<section id="primary" class="error-404-main-template">
	<main id="main">
		<div class="error-404-wrapper">
			<header class="page-header">
				<p class="error-404-big"><?php esc_html_e('404', 'shopchop'); ?></p>
				<h1 class="page-title error-404-heading"><?php esc_html_e('Page Not Found', 'shopchop'); ?></h1>
			</header><!-- .page-header -->

			<div <?php shopchop_content_class('page-content'); ?>>
				<p><?php esc_html_e('Sorry, we couldn\'t find the page you\'re looking for. Maybe our products are playing hide and seek?', 'shopchop'); ?></p>
				<div class="error-404-buttons">
					<a href="<?php echo esc_url(home_url()); ?>" class="button btn-menu"><?php esc_html_e('Go Back Home', 'shopchop'); ?></a>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="button btn-secondary"><?php esc_html_e('Explore All Products', 'shopchop'); ?></a>
				</div>
			</div><!-- .page-content -->
		</div>
	</main><!-- #main -->
</section><!-- #primary -->

<?php
get_footer();
