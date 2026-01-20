<?php

/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.5.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_orders', $has_orders); ?>

<?php if ($has_orders) : ?>

	<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<?php /* We hide thead via PHP
		<thead>
			<tr>
				<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
					<th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		*/ ?>

		<tbody>
			<?php
			foreach ($customer_orders->orders as $customer_order) {
				$order      = wc_get_order($customer_order);
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();
				$items      = $order->get_items();
			?>
				<tr class="woocommerce-orders-table__row order-status-<?php echo esc_attr($order->get_status()); ?>">

					<td class="order-number-status">
						<div class="order-number">
							<a href="<?php echo esc_url($order->get_view_order_url()); ?>"><strong><?php echo 'Order #' . esc_html($order->get_order_number()); ?></strong></a>
						</div>
						<span class="status-badge status-<?php echo esc_attr($order->get_status()); ?>">
							<?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
						</span>
					</td>

					<td class="order-product-details">
						<?php
						$display_limit = 1;
						$i = 0;
						foreach ($items as $item) {
							if ($i < $display_limit) {
								$product = $item->get_product();
								echo '<div class="product-info-wrapper">';
								echo $product ? $product->get_image(array(96, 96)) : '';
								echo '<div class="product-meta-detail">';

								$base_name = $product->get_name();
								if ($product->is_type('variation')) {
									$parent_product = wc_get_product($product->get_parent_id());
									$base_name = $parent_product->get_name();
								}
								echo '<span class="name">' . esc_html($base_name) . '</span>';

								if ($product && $product->is_type('variation')) {
									$variation_list = wc_get_formatted_variation($product->get_variation_attributes(), true);
									echo '<span class="variation-meta">' . esc_html($variation_list) . '</span>';
								}

								echo '<span class="price">' . $order->get_formatted_line_subtotal($item) . '</span>';
								echo '</div></div>';
							}
							$i++;
						}
						if ($item_count > $display_limit) {
							echo '<p class="more-items">+' . ($item_count - $display_limit) . ' more items</p>';
						}
						?>
					</td>

					<td class="order-date-actions">
						<time datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>">
							<?php echo esc_html(wc_format_datetime($order->get_date_created())); ?>
						</time>
						<div class="order-actions-wrapper">
							<?php
							$actions = wc_get_account_orders_actions($order);
							if (! empty($actions)) {
								foreach ($actions as $key => $action) {
									echo '<a href="' . esc_url($action['url']) . '" class="button ' . sanitize_html_class($key) . '">' . esc_html($action['name']) . '</a>';
								}
							}
							?>
						</div>
					</td>
				</tr>
			<?php
			}
			?>
		</tbody>
	</table>

	<?php do_action('woocommerce_before_account_orders_pagination'); ?>

	<?php if (1 < $customer_orders->max_num_pages) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if (1 !== $current_page) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>"><?php esc_html_e('Previous', 'woocommerce'); ?></a>
			<?php endif; ?>

			<?php if (intval($customer_orders->max_num_pages) !== $current_page) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>"><?php esc_html_e('Next', 'woocommerce'); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>

	<?php wc_print_notice(esc_html__('No order has been made yet.', 'woocommerce') . ' <a class="woocommerce-Button wc-forward button' . esc_attr($wp_button_class) . '" href="' . esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))) . '">' . esc_html__('Browse products', 'woocommerce') . '</a>', 'notice'); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment 
	?>

<?php endif; ?>

<?php do_action('woocommerce_after_account_orders', $has_orders); ?>