<?php

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined('ABSPATH') || exit;

get_header('shop');

?>

<?php
/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action('woocommerce_before_main_content');

/**
 * Hook: woocommerce_shop_loop_header.
 *
 * @since 8.6.0
 *
 * @hooked woocommerce_product_taxonomy_archive_header - 10
 */
do_action('woocommerce_shop_loop_header');

if (woocommerce_product_loop()) {

	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	do_action('woocommerce_before_shop_loop');

	$display_type = woocommerce_get_loop_display_mode();

	/**
	 * SHOW BOTH → categories + products (split layout)
	 */
	if ($display_type === 'both') {

		/* ---------- Categories ---------- */
		$parent_id = 0;

		if (is_product_taxonomy()) {
			$current_term = get_queried_object();
			if (isset($current_term->term_id)) {
				$parent_id = $current_term->term_id;
			}
		}

		$categories = get_terms(array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => $parent_id,
		));

		if ($categories) {

			echo '<ul class="products columns-' . esc_attr(wc_get_loop_prop('columns')) . ' category-grid">';

			foreach ($categories as $category) {
				wc_get_template('content-product-cat.php', array(
					'category' => $category,
				));
			}

			woocommerce_product_loop_end();

			echo '<hr class="my-6 border-grey-200">';
		}

		/* ---------- Products ---------- */

		echo '<h2 class="shop-section-title">All Products</h2>';

		echo '<ul class="products columns-' . esc_attr(wc_get_loop_prop('columns')) . '">';

		if (wc_get_loop_prop('total')) {
			while (have_posts()) {
				the_post();

				do_action('woocommerce_shop_loop');

				wc_get_template_part('content', 'product');
			}
		}

		woocommerce_product_loop_end();
	}

	/**
	 * PRODUCTS ONLY
	 */
	elseif ($display_type === 'products') {

		woocommerce_product_loop_start();

		if (wc_get_loop_prop('total')) {
			while (have_posts()) {
				the_post();

				do_action('woocommerce_shop_loop');

				wc_get_template_part('content', 'product');
			}
		}

		woocommerce_product_loop_end();
	}

	/**
	 * CATEGORIES ONLY
	 */
	elseif ($display_type === 'subcategories') {

		$parent_id = 0;

		if (is_product_taxonomy()) {
			$current_term = get_queried_object();
			if (isset($current_term->term_id)) {
				$parent_id = $current_term->term_id;
			}
		}

		$categories = get_terms(array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => $parent_id,
		));

		if ($categories) {

			echo '<ul class="products columns-' . esc_attr(wc_get_loop_prop('columns')) . ' category-grid">';

			foreach ($categories as $category) {
				wc_get_template('content-product-cat.php', array(
					'category' => $category,
				));
			}

			woocommerce_product_loop_end();
		}
	}


	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action('woocommerce_after_shop_loop');
} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action('woocommerce_no_products_found');
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action('woocommerce_sidebar');

get_footer('shop');
