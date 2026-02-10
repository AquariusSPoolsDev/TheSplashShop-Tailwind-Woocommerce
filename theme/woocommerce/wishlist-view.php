<?php

/**
 * Wishlist page template - Standard Layout
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Wishlist\Templates\Wishlist\View
 * @version 3.0.0
 */

/**
 * Template variables:
 *
 * @var $wishlist                      \YITH_WCWL_Wishlist Current wishlist
 * @var $wishlist_items                array Array of items to show for current page
 * @var $wishlist_token                string Current wishlist token
 * @var $wishlist_id                   int Current wishlist id
 * @var $users_wishlists               array Array of current user wishlists
 * @var $pagination                    string yes/no
 * @var $per_page                      int Items per page
 * @var $current_page                  int Current page
 * @var $page_links                    array Array of page links
 * @var $is_user_owner                 bool Whether current user is wishlist owner
 * @var $show_price                    bool Whether to show price column
 * @var $show_dateadded                bool Whether to show item date of addition
 * @var $show_stock_status             bool Whether to show product stock status
 * @var $show_add_to_cart              bool Whether to show Add to Cart button
 * @var $show_remove_product           bool Whether to show Remove button
 * @var $show_price_variations         bool Whether to show price variation over time
 * @var $show_variation                bool Whether to show variation attributes when possible
 * @var $show_cb                       bool Whether to show checkbox column
 * @var $show_quantity                 bool Whether to show input quantity or not
 * @var $show_ask_estimate_button      bool Whether to show Ask an Estimate form
 * @var $show_last_column              bool Whether to show last column (calculated basing on previous flags)
 * @var $move_to_another_wishlist      bool Whether to show Move to another wishlist select
 * @var $move_to_another_wishlist_type string Whether to show a select or a popup for wishlist change
 * @var $additional_info               bool Whether to show Additional info textarea in Ask an estimate form
 * @var $price_excl_tax                bool Whether to show price excluding taxes
 * @var $enable_drag_n_drop            bool Whether to enable drag n drop feature
 * @var $repeat_remove_button          bool Whether to repeat remove button in last column
 * @var $available_multi_wishlist      bool Whether multi wishlist is enabled and available
 * @var $no_interactions               bool
 */

if (! defined('YITH_WCWL')) {
	exit;
} // Exit if accessed directly
?>

<!-- WISHLIST TABLE -->
<table
	class="shop_table cart wishlist_table wishlist_view traditional responsive shopchop-desktop-view <?php echo $no_interactions ? 'no-interactions' : ''; ?> <?php echo $enable_drag_n_drop ? 'sortable' : ''; ?> "
	data-pagination="<?php echo esc_attr($pagination); ?>" data-per-page="<?php echo esc_attr($per_page); ?>" data-page="<?php echo esc_attr($current_page); ?>"
	data-id="<?php echo esc_attr($wishlist_id); ?>" data-token="<?php echo esc_attr($wishlist_token); ?>">

	<?php $column_count = 2; ?>

	<!-- hidden thead  -->
	<thead class="hidden!">
		<tr>
			<?php if ($show_cb) : ?>
				<?php ++$column_count; ?>
				<th class="product-checkbox">
					<input type="checkbox" value="" name="" id="bulk_add_to_cart" />
				</th>
			<?php endif; ?>

			<?php if ($show_remove_product) : ?>
				<?php ++$column_count; ?>
				<th class="product-remove">
					<span class="nobr">
						<?php
						/**
						 * APPLY_FILTERS: yith_wcwl_wishlist_view_remove_heading
						 *
						 * Filter the heading of the column to remove the product from the wishlist in the wishlist table.
						 *
						 * @param string             $heading  Heading text
						 * @param YITH_WCWL_Wishlist $wishlist Wishlist object
						 *
						 * @return string
						 */
						echo esc_html(apply_filters('yith_wcwl_wishlist_view_remove_heading', '', $wishlist));
						?>
					</span>
				</th>
			<?php endif; ?>

			<th class="product-thumbnail"></th>

			<th class="product-name">
				<span class="nobr">
					<?php
					/**
					 * APPLY_FILTERS: yith_wcwl_wishlist_view_name_heading
					 *
					 * Filter the heading of the column to show the product name in the wishlist table.
					 *
					 * @param string             $heading  Heading text
					 * @param YITH_WCWL_Wishlist $wishlist Wishlist object
					 *
					 * @return string
					 */
					echo esc_html(apply_filters('yith_wcwl_wishlist_view_name_heading', __('Product name', 'yith-woocommerce-wishlist'), $wishlist));
					?>
				</span>
			</th>

			<?php if ($show_price || $show_price_variations) : ?>
				<?php ++$column_count; ?>
				<th class="product-price">
					<span class="nobr">
						<?php
						/**
						 * APPLY_FILTERS: yith_wcwl_wishlist_view_price_heading
						 *
						 * Filter the heading of the column to show the product price in the wishlist table.
						 *
						 * @param string             $heading  Heading text
						 * @param YITH_WCWL_Wishlist $wishlist Wishlist object
						 *
						 * @return string
						 */
						echo esc_html(apply_filters('yith_wcwl_wishlist_view_price_heading', __('Unit price', 'yith-woocommerce-wishlist'), $wishlist));
						?>
					</span>
				</th>
			<?php endif; ?>

			<?php if ($show_quantity) : ?>
				<?php ++$column_count; ?>
				<th class="product-quantity">
					<span class="nobr">
						<?php
						/**
						 * APPLY_FILTERS: yith_wcwl_wishlist_view_quantity_heading
						 *
						 * Filter the heading of the column to show the product quantity in the wishlist table.
						 *
						 * @param string             $heading  Heading text
						 * @param YITH_WCWL_Wishlist $wishlist Wishlist object
						 *
						 * @return string
						 */
						echo esc_html(apply_filters('yith_wcwl_wishlist_view_quantity_heading', __('Quantity', 'yith-woocommerce-wishlist'), $wishlist));
						?>
					</span>
				</th>
			<?php endif; ?>

			<?php if ($show_stock_status) : ?>
				<?php ++$column_count; ?>
				<th class="product-stock-status">
					<span class="nobr">
						<?php
						/**
						 * APPLY_FILTERS: yith_wcwl_wishlist_view_stock_heading
						 *
						 * Filter the heading of the column to show the product stock status in the wishlist table.
						 *
						 * @param string             $heading  Heading text
						 * @param YITH_WCWL_Wishlist $wishlist Wishlist object
						 *
						 * @return string
						 */
						echo esc_html(apply_filters('yith_wcwl_wishlist_view_stock_heading', __('Stock status', 'yith-woocommerce-wishlist'), $wishlist));
						?>
					</span>
				</th>
			<?php endif; ?>

			<?php if ($show_last_column) : ?>
				<?php ++$column_count; ?>
				<th class="product-add-to-cart">
					<span class="nobr">
						<?php
						/**
						 * APPLY_FILTERS: yith_wcwl_wishlist_view_cart_heading
						 *
						 * Filter the heading of the cart column in the wishlist table.
						 *
						 * @param string             $heading  Heading text
						 * @param YITH_WCWL_Wishlist $wishlist Wishlist object
						 *
						 * @return string
						 */
						echo esc_html(apply_filters('yith_wcwl_wishlist_view_cart_heading', '', $wishlist));
						?>
					</span>
				</th>
			<?php endif; ?>

			<?php if ($enable_drag_n_drop) : ?>
				<?php ++$column_count; ?>
				<th class="product-arrange">
					<span class="nobr">
						<?php
						/**
						 * APPLY_FILTERS: yith_wcwl_wishlist_view_arrange_heading
						 *
						 * Filter the heading of the column to change order of the items in the wishlist table.
						 *
						 * @param string             $heading  Heading text
						 * @param YITH_WCWL_Wishlist $wishlist Wishlist object
						 *
						 * @return string
						 */
						echo esc_html(apply_filters('yith_wcwl_wishlist_view_arrange_heading', __('Arrange', 'yith-woocommerce-wishlist'), $wishlist));
						?>
					</span>
				</th>
			<?php endif; ?>
		</tr>
	</thead>

	<tbody class="wishlist-items-wrapper">
		<?php
		if ($wishlist && $wishlist->has_items()) :
			foreach ($wishlist_items as $item) :
				/**
				 * Each of the wishlist items
				 *
				 * @var $item \YITH_WCWL_Wishlist_Item
				 */
				global $product;

				$product = $item->get_product();

				if ($product && $product->exists()) :
		?>
					<tr class="wishlist-normal-full" id="yith-wcwl-row-<?php echo esc_attr($item->get_product_id()); ?>" data-row-id="<?php echo esc_attr($item->get_product_id()); ?>">
						<?php if ($show_cb) : ?>
							<td class="product-checkbox">
								<input type="checkbox" value="yes" name="items[<?php echo esc_attr($item->get_product_id()); ?>][cb]" />
							</td>
						<?php endif ?>

						<?php if ($show_remove_product) : ?>
							<td class="product-remove">
								<div>
									<?php
									/**
									 * APPLY_FILTERS: yith_wcwl_remove_product_wishlist_message_title
									 *
									 * Filter the title of the icon to remove the product from the wishlist.
									 *
									 * @param string $title Icon title
									 *
									 * @return string
									 */
									?>
									<a href="<?php echo esc_url($item->get_remove_url()); ?>" class="remove remove_from_wishlist" title="<?php echo esc_html(apply_filters('yith_wcwl_remove_product_wishlist_message_title', __('Remove this product', 'yith-woocommerce-wishlist'))); ?>">&times;</a>
								</div>
							</td>
						<?php endif; ?>

						<td class="product-thumbnail">
							<?php
							/**
							 * DO_ACTION: yith_wcwl_table_before_product_thumbnail
							 *
							 * Allows to render some content or fire some action before the product thumbnail in the wishlist table.
							 *
							 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
							 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
							 */
							do_action('yith_wcwl_table_before_product_thumbnail', $item, $wishlist);
							?>

							<a href="<?php echo esc_url(get_permalink(apply_filters('woocommerce_in_cart_product', $item->get_product_id()))); ?>">
								<?php echo wp_kses_post($product->get_image()); ?>
							</a>

							<?php
							/**
							 * DO_ACTION: yith_wcwl_table_after_product_thumbnail
							 *
							 * Allows to render some content or fire some action after the product thumbnail in the wishlist table.
							 *
							 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
							 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
							 */
							do_action('yith_wcwl_table_after_product_thumbnail', $item, $wishlist);
							?>
						</td>

						<td class="product-meta">

							<div class="product-name">
								<?php
								/**
								 * DO_ACTION: yith_wcwl_table_before_product_name
								 *
								 * Allows to render some content or fire some action before the product name in the wishlist table.
								 *
								 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
								 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
								 */
								do_action('yith_wcwl_table_before_product_name', $item, $wishlist);
								?>

								<a href="<?php echo esc_url(get_permalink(apply_filters('woocommerce_in_cart_product', $item->get_product_id()))); ?>">
									<?php echo wp_kses_post(apply_filters('woocommerce_in_cartproduct_obj_title', $product->get_title(), $product)); ?>
								</a>

								<?php
								if ($show_variation && $product->is_type('variation')) {
									/**
									 * Product is a Variation
									 *
									 * @var $product \WC_Product_Variation
									 */
									echo wp_kses_post(wc_get_formatted_variation($product));
								}
								?>

								<?php
								/**
								 * DO_ACTION: yith_wcwl_table_after_product_name
								 *
								 * Allows to render some content or fire some action after the product name in the wishlist table.
								 *
								 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
								 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
								 */
								do_action('yith_wcwl_table_after_product_name', $item, $wishlist);
								?>
							</div>

							<?php if ($show_price || $show_price_variations) : ?>
								<div class="product-price">
									<?php
									/**
									 * DO_ACTION: yith_wcwl_table_before_product_price
									 *
									 * Allows to render some content or fire some action before the product price in the wishlist table.
									 *
									 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
									 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
									 */
									do_action('yith_wcwl_table_before_product_price', $item, $wishlist);
									?>

									<?php
									if ($show_price) {
										echo wp_kses_post($item->get_formatted_product_price());
									}

									if ($show_price_variations) {
										echo wp_kses_post($item->get_price_variation());
									}
									?>

									<?php
									/**
									 * DO_ACTION: yith_wcwl_table_after_product_price
									 *
									 * Allows to render some content or fire some action after the product price in the wishlist table.
									 *
									 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
									 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
									 */
									do_action('yith_wcwl_table_after_product_price', $item, $wishlist);
									?>
								</div>
							<?php endif ?>

							<?php if ($show_quantity) : ?>
								<div class="product-quantity">
									<?php
									/**
									 * DO_ACTION: yith_wcwl_table_before_product_quantity
									 *
									 * Allows to render some content or fire some action before the product quantity in the wishlist table.
									 *
									 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
									 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
									 */
									do_action('yith_wcwl_table_before_product_quantity', $item, $wishlist);
									?>

									<?php if (! $no_interactions && $wishlist->current_user_can('update_quantity')) : ?>
										<input type="number" min="1" step="1" name="items[<?php echo esc_attr($item->get_product_id()); ?>][quantity]" value="<?php echo esc_attr($item->get_quantity()); ?>" />
									<?php else : ?>
										<?php echo esc_html($item->get_quantity()); ?>
									<?php endif; ?>

									<?php
									/**
									 * DO_ACTION: yith_wcwl_table_after_product_quantity
									 *
									 * Allows to render some content or fire some action after the product quantity in the wishlist table.
									 *
									 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
									 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
									 */
									do_action('yith_wcwl_table_after_product_quantity', $item, $wishlist);
									?>
								</div>
							<?php endif; ?>

							<?php if ($show_stock_status) : ?>
								<div class="product-stock-status">
									<?php
									/**
									 * DO_ACTION: yith_wcwl_table_before_product_stock
									 *
									 * Allows to render some content or fire some action before the product stock in the wishlist table.
									 *
									 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
									 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
									 */
									do_action('yith_wcwl_table_before_product_stock', $item, $wishlist);
									?>

									<?php
									/**
									 * APPLY_FILTERS: yith_wcwl_out_of_stock_label
									 *
									 * Filter the label when the product in the wishlist is out of stock.
									 *
									 * @param string $label Label
									 *
									 * @return string
									 */
									/**
									 * APPLY_FILTERS: yith_wcwl_in_stock_label
									 *
									 * Filter the label when the product in the wishlist is in stock.
									 *
									 * @param string $label Label
									 *
									 * @return string
									 */
									$stock_status_html = 'out-of-stock' === $item->get_stock_status() ? '<span class="wishlist-out-of-stock">' . esc_html(apply_filters('yith_wcwl_out_of_stock_label', __('Out of stock', 'yith-woocommerce-wishlist'))) . '</span>' : '<span class="wishlist-in-stock">' . esc_html(apply_filters('yith_wcwl_in_stock_label', __('In Stock', 'yith-woocommerce-wishlist'))) . '</span>';

									/**
									 * APPLY_FILTERS: yith_wcwl_stock_status
									 *
									 * Filters the HTML for the stock status label.
									 *
									 * @param string                  $stock_status_html Stock status HTML.
									 * @param YITH_WCWL_Wishlist_Item $item              Wishlist item object.
									 * @param YITH_WCWL_Wishlist      $wishlist          Wishlist object.
									 *
									 * @return string
									 */
									echo wp_kses_post(apply_filters('yith_wcwl_stock_status', $stock_status_html, $item, $wishlist));
									?>

									<?php
									/**
									 * DO_ACTION: yith_wcwl_table_after_product_stock
									 *
									 * Allows to render some content or fire some action after the product stock in the wishlist table.
									 *
									 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
									 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
									 */
									do_action('yith_wcwl_table_after_product_stock', $item, $wishlist);
									?>
								</div>
							<?php endif ?>
						</td>


						<?php if ($show_last_column) : ?>
							<td class="product-add-to-cart">
								<?php
								/**
								 * DO_ACTION: yith_wcwl_table_before_product_cart
								 *
								 * Allows to render some content or fire some action before the product cart in the wishlist table.
								 *
								 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
								 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
								 */
								do_action('yith_wcwl_table_before_product_cart', $item, $wishlist);
								?>

								<!-- Date added -->
								<?php
								if ($show_dateadded && $item->get_date_added()) :
									// translators: 1. Date product was added to wishlist.
									echo '<span class="dateadded">' . esc_html(sprintf(__('Added on: %s', 'yith-woocommerce-wishlist'), $item->get_date_added_formatted())) . '</span>';
								endif;
								?>

								<?php
								/**
								 * DO_ACTION: yith_wcwl_table_product_before_add_to_cart
								 *
								 * Allows to render some content or fire some action before the 'Add to cart' in the wishlist table.
								 *
								 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
								 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
								 */
								do_action('yith_wcwl_table_product_before_add_to_cart', $item, $wishlist);
								?>

								<!-- Add to cart button -->
								<?php
								/**
								 * APPLY_FILTERS: yith_wcwl_table_product_show_add_to_cart
								 *
								 * Filter if show the 'Add to cart' button in the wishlist table for each product.
								 *
								 * @param bool                    $show_add_to_cart Show 'Add to cart' button or not
								 * @param YITH_WCWL_Wishlist_Item $item             Wishlist item object
								 * @param YITH_WCWL_Wishlist      $wishlist         Wishlist object
								 *
								 * @return bool
								 */
								$show_add_to_cart = apply_filters('yith_wcwl_table_product_show_add_to_cart', $show_add_to_cart, $item, $wishlist);
								?>
								<?php if ($show_add_to_cart && $item->is_purchasable() && 'out-of-stock' !== $item->get_stock_status()) : ?>
									<?php woocommerce_template_loop_add_to_cart(array('quantity' => $show_quantity ? $item->get_quantity() : 1)); ?>
								<?php endif ?>

								<?php
								/**
								 * DO_ACTION: yith_wcwl_table_product_after_add_to_cart
								 *
								 * Allows to render some content or fire some action after the 'Add to cart' in the wishlist table.
								 *
								 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
								 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
								 */
								do_action('yith_wcwl_table_product_after_add_to_cart', $item, $wishlist);
								?>

								<!-- Change wishlist -->
								<?php
								/**
								 * APPLY_FILTERS: yith_wcwl_table_product_move_to_another_wishlist
								 *
								 * Filter if show the 'Move to another wishlist' button in the wishlist table for each product.
								 *
								 * @param bool                    $move_to_another_wishlist Show 'Move to another wishlist' button or not
								 * @param YITH_WCWL_Wishlist_Item $item                     Wishlist item object
								 * @param YITH_WCWL_Wishlist      $wishlist                 Wishlist object
								 *
								 * @return bool
								 */
								$move_to_another_wishlist = apply_filters('yith_wcwl_table_product_move_to_another_wishlist', $move_to_another_wishlist, $item, $wishlist);
								?>
								<?php if ($move_to_another_wishlist && $available_multi_wishlist && count($users_wishlists) > 1) : ?>
									<?php if ('select' === $move_to_another_wishlist_type) : ?>
										<select class="change-wishlist selectBox">
											<option value=""><?php esc_html_e('Move', 'yith-woocommerce-wishlist'); ?></option>
											<?php
											foreach ($users_wishlists as $wl) :
												/**
												 * Each of customer's wishlists
												 *
												 * @var $wl \YITH_WCWL_Wishlist
												 */
												if ($wl->get_token() === $wishlist_token) {
													continue;
												}
											?>
												<option value="<?php echo esc_attr($wl->get_token()); ?>">
													<?php printf('%s - %s', esc_html($wl->get_formatted_name()), esc_html($wl->get_formatted_privacy())); ?>
												</option>
											<?php
											endforeach;
											?>
										</select>
									<?php else : ?>
										<a href="#move_to_another_wishlist" class="move-to-another-wishlist-button" data-rel="prettyPhoto[move_to_another_wishlist]">
											<?php
											/**
											 * APPLY_FILTERS: yith_wcwl_move_to_another_list_label
											 *
											 * Filter the label to move the product to another wishlist.
											 *
											 * @param string $label Label
											 *
											 * @return string
											 */
											echo esc_html(apply_filters('yith_wcwl_move_to_another_list_label', __('Move to another list &rsaquo;', 'yith-woocommerce-wishlist')));
											?>
										</a>
									<?php endif; ?>

									<?php
									/**
									 * DO_ACTION: yith_wcwl_table_product_after_move_to_another_wishlist
									 *
									 * Allows to render some content or fire some action after the 'Move to another wishlist' in the wishlist table.
									 *
									 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
									 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
									 */
									do_action('yith_wcwl_table_product_after_move_to_another_wishlist', $item, $wishlist);
									?>

								<?php endif; ?>

								<!-- Remove from wishlist -->
								<?php
								if ($repeat_remove_button) :
									/**
									 * APPLY_FILTERS: yith_wcwl_remove_product_wishlist_message_title
									 *
									 * Filter the title of the button to remove the product from the wishlist.
									 *
									 * @param string $title Button title
									 *
									 * @return string
									 */
								?>
									<a href="<?php echo esc_url($item->get_remove_url()); ?>" class="remove_from_wishlist" title="<?php echo esc_html(apply_filters('yith_wcwl_remove_product_wishlist_message_title', __('Remove this product', 'yith-woocommerce-wishlist'))); ?>"><?php esc_html_e('Remove', 'yith-woocommerce-wishlist'); ?></a>
								<?php endif; ?>

								<?php
								/**
								 * DO_ACTION: yith_wcwl_table_after_product_cart
								 *
								 * Allows to render some content or fire some action after the product cart in the wishlist table.
								 *
								 * @param YITH_WCWL_Wishlist_Item $item     Wishlist item object
								 * @param YITH_WCWL_Wishlist      $wishlist Wishlist object
								 */
								do_action('yith_wcwl_table_after_product_cart', $item, $wishlist);
								?>
							</td>
						<?php endif; ?>

						<?php if ($enable_drag_n_drop) : ?>
							<td class="product-arrange ">
								<i class="fa fa-arrows"></i>
								<input type="hidden" name="items[<?php echo esc_attr($item->get_product_id()); ?>][position]" value="<?php echo esc_attr($item->get_position()); ?>" />
							</td>
						<?php endif; ?>
					</tr>
			<?php
				endif;
			endforeach;
		else :
			?>
			<tr class="wishlist-normal-empty">
				<?php
				/**
				 * APPLY_FILTERS: yith_wcwl_no_product_to_remove_message
				 *
				 * Filter the message shown when there are no products in the wishlist.
				 *
				 * @param string             $message  Message
				 * @param YITH_WCWL_Wishlist $wishlist Wishlist object
				 *
				 * @return string
				 */
				?>
				<td colspan="<?php echo esc_attr($column_count); ?>" class="wishlist-empty">
					<svg class="h-25 w-25 fill-current" xmlns="http://www.w3.org/2000/svg"viewBox="0 0 512 512" width="100" height="100">
						<path d="M200 55a785 785 0 0 1 6.938 3.313l1.998.935c1.727.842 3.4 1.791 5.064 2.752 1 3 1 3-.025 5.315l-1.631 2.751-1.826 3.12-2.018 3.376q-1.034 1.756-2.064 3.514-2.145 3.655-4.303 7.303c-3.255 5.515-6.44 11.07-9.633 16.621l-3.746 6.492a22002 22002 0 0 0-7.54 13.078A8175 8175 0 0 1 156 167l-7 12h214l-15-26-20.627-35.754-3.728-6.46a16804 16804 0 0 1-11.739-20.368q-1.906-3.308-3.808-6.617l-1.758-3.051-1.676-2.918a221 221 0 0 0-3.464-5.695l-1.696-2.7-1.533-2.378C297 65 297 65 298 62a92 92 0 0 1 7.063-3.687l2.01-.971c1.638-.79 3.282-1.567 4.927-2.342 7.78 13.145 15.422 26.368 23.044 39.605q4.726 8.199 9.456 16.395 5.646 9.783 11.285 19.57a7134 7134 0 0 0 18.965 32.743l1.772 3.037c2.235 3.828 2.235 3.828 4.478 7.65l1.204 2.61c1.592 2.656 1.592 2.656 4.544 3.009l3.369-.007 3.825.03 4.148-.04q2.136.007 4.273.024c3.758.023 7.516.018 11.275.006q8.975-.026 17.948.026c2.074.006 4.147-.007 6.221-.022 14.991.042 26.657 3.164 37.88 13.364 8.986 9.527 12.995 21.036 13.313 34-.467 12.357-5.216 23.02-13.738 31.965C462.544 270.408 452.362 271 435 271l-6 38 5 2c15.535 10.726 26.947 25.101 33 43l.965 2.758c6.132 21.195 2.553 44.506-7.828 63.613-2.864 5.006-6.241 9.383-10.137 13.629l-2.012 2.29c-14.13 15.2-34.82 24.009-55.442 24.805a512 512 0 0 1-8.032.045l-3.089.01q-5.094.01-10.19.011l-7.33.015q-9.936.019-19.872.024l-12.426.012q-17.23.019-34.46.026h-2.224l-6.7.002h-2.24q-17.958.009-35.919.046-18.474.035-36.949.037-10.357-.001-20.717.025-9.745.024-19.492.01-3.567-.002-7.134.013c-18.327.072-32.413-.89-47.96-11.558-12.637-12.638-15.61-26.448-18.468-43.395q-.513-2.97-1.029-5.938A4986 4986 0 0 1 95 381l-1.157-6.824c-2.608-15.4-5.178-30.806-7.736-46.214l-2.48-14.885q-1.201-7.201-2.4-14.404-.567-3.418-1.138-6.836-.784-4.704-1.564-9.41l-.472-2.824-.423-2.565-.371-2.23C77 273 77 273 77 271l-3.297.14c-14.62.441-25.457-2.157-36.703-12.14-8.441-8.427-13.52-19.207-14.059-31.2.284-13.544 4.407-25.269 13.747-35.3 9.333-8.625 20.405-12.865 33.062-12.406q2.89.145 5.78.304c4.283.214 8.57.29 12.857.394 3.741.097 7.47.241 11.205.487 14.47.996 14.47.996 28.408-2.279 6.835-6.055 10.231-14.314 13.565-22.638 2.294-5.374 5.331-10.261 8.373-15.237q1.738-2.96 3.464-5.93l1.77-3.038c3.303-5.703 6.564-11.43 9.828-17.157 11.468-20.117 23.194-40.08 35-60M46 208c-4.893 7.461-6.754 14.219-5.375 23 2.014 8.651 7.077 15.013 14.375 20 4.876 2.388 8.381 3.376 13.893 3.383l2 .015c2.204.013 4.408.005 6.611-.004q2.4.007 4.798.019c4.39.016 8.778.014 13.167.008 4.735-.002 9.47.012 14.206.025q13.914.028 27.827.023a19521 19521 0 0 1 29.135.017l3.275.003c20.445.019 40.89.018 61.335.01 18.7-.005 37.4.016 56.1.05q28.807.05 57.612.043c10.78-.002 21.561.003 32.342.028q13.767.033 27.534.006c4.682-.01 9.364-.01 14.045.01q6.434.028 12.867-.008a405 405 0 0 1 4.645.013c11.011.09 19.065-1.442 27.483-8.891 4.682-5.09 7.664-11.574 8.219-18.52-.359-8.523-2.92-16.243-9.094-22.23-6.746-5.416-12.946-8.247-21.707-8.257-.979-.006-.979-.006-1.977-.01-2.183-.01-4.365-.005-6.548 0l-4.746-.014c-4.346-.012-8.69-.012-13.036-.01-4.688 0-9.375-.01-14.062-.02q-13.775-.023-27.548-.023-11.198-.001-22.394-.012-31.745-.026-63.49-.025h-6.92c-18.513.001-37.026-.018-55.539-.046q-28.514-.043-57.027-.04c-10.673 0-21.345-.006-32.017-.027q-13.63-.029-27.26-.009c-4.635.006-9.27.007-13.906-.01a1631 1631 0 0 0-12.739.004q-2.3.005-4.599-.012C65.014 196.404 54.494 198.091 46 208m49 63c.733 9.35.733 9.35 1.953 18.563l.564 3.49.613 3.695.65 3.996c.706 4.326 1.422 8.65 2.138 12.975l.743 4.501q1.747 10.572 3.508 21.14 2.024 12.136 4 24.28 1.54 9.433 3.12 18.861c.625 3.74 1.244 7.481 1.848 11.225q.853 5.284 1.755 10.56c.318 1.884.616 3.772.913 5.66 1.983 11.4 5.795 21.077 14.957 28.5 6.93 4.812 13.88 5.707 22.166 5.674l2.402.003q3.977 0 7.954-.01l5.707.001q7.745-.001 15.488-.013 8.094-.007 16.188-.008 15.326-.004 30.652-.021 17.449-.016 34.897-.022 35.892-.016 71.784-.05l-2.35-2.35c-16.324-16.326-27.713-35.457-27.98-59.285l.017-2.615.015-2.77c.36-20.895 8.815-40.301 23.42-55.226 16.471-15.757 37.426-23.044 59.913-22.922 7.036.298 13.991 1.222 20.965 2.168.675-3.874 1.338-7.75 2-11.625l.578-3.316.547-3.223.508-2.952c.493-3.391.493-3.391.367-8.884zm248 63c-13.73 15.769-19.183 32.196-18.16 52.822 1.198 16.339 9.828 29.101 21.16 40.178l1.7 1.797c10.553 10.305 26.992 15.086 41.3 15.578 17.714-.61 34.464-7.78 47-20.375 6.127-7 10.755-14.284 14-23l.965-2.395c5.088-14.889 2.985-32.396-3.25-46.515-8.228-16.295-21.363-27.849-38.453-34.09-23.084-7.286-49.065-1.288-66.262 16" />
						<path d="M381 351h17v20h19v17h-19v20h-17v-20h-20v-17h20z" />
					</svg>
					<?php echo esc_html(apply_filters('yith_wcwl_no_product_to_remove_message', __('No products added to the wishlist', 'yith-woocommerce-wishlist'), $wishlist)); ?>
				</td>
			</tr>
		<?php
		endif;

		if (! empty($page_links)) :
		?>
			<tr class="pagination-row wishlist-pagination">
				<td colspan="<?php echo esc_attr($column_count); ?>">
					<?php echo wp_kses_post($page_links); ?>
				</td>
			</tr>
		<?php endif ?>
	</tbody>

</table>