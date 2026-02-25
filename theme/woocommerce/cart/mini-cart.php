<?php

/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.0.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_mini_cart'); ?>

<?php if (WC()->cart && ! WC()->cart->is_empty()) : ?>

	<div class="shopchop-mini-cart-list-wrapper">
		<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr($args['list_class']); ?>">
			<?php
			do_action('woocommerce_before_mini_cart_contents');

			foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
				$_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
				$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

				if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key)) {
					/**
					 * This filter is documented in woocommerce/templates/cart/cart.php.
					 *
					 * @since 2.1.0
					 */
					$product_name      = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
					$thumbnail         = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
					$product_price     = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
					$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
			?>
					<li class="woocommerce-mini-cart-item <?php echo esc_attr(apply_filters('woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key)); ?>">
						<?php
						echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'woocommerce_cart_item_remove_link',
							sprintf(
								'<a role="button" href="%s" class="remove remove_from_cart_button text-error-400" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s" data-success_message="%s"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-icon lucide-trash  h-4 w-4"><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></a>',
								esc_url(wc_get_cart_remove_url($cart_item_key)),
								/* translators: %s is the product name */
								esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
								esc_attr($product_id),
								esc_attr($cart_item_key),
								esc_attr($_product->get_sku()),
								/* translators: %s is the product name */
								esc_attr(sprintf(__('&ldquo;%s&rdquo; has been removed from your cart', 'woocommerce'), wp_strip_all_tags($product_name)))
							),
							$cart_item_key
						);
						?>
						<?php if (empty($product_permalink)) : ?>
							<?php echo $thumbnail . wp_kses_post($product_name); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
							?>
						<?php else : ?>
							<a class="mini-cart-item-link" href="<?php echo esc_url($product_permalink); ?>">
								<?php echo $thumbnail; ?>
								<div class="mini-cart-item-details">
									<div class="mini-cart-item-meta">
										<span class="mini-cart-item-name"><?php echo wp_kses_post($product_name); ?></span>
										<?php echo wc_get_formatted_cart_item_data($cart_item); ?>
										<span class="quantity">&times; <?php echo $cart_item['quantity']; ?></span>
									</div>	
									<span class="price"><?php echo wp_kses_post($product_price); ?></span>
								</div>
							</a>
						<?php endif; ?>

					</li>
			<?php
				}
			}

			do_action('woocommerce_mini_cart_contents');
			?>
		</ul>

		<p class="woocommerce-mini-cart__total total">
			<?php
			/**
			 * Hook: woocommerce_widget_shopping_cart_total.
			 *
			 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
			 */
			do_action('woocommerce_widget_shopping_cart_total');
			?>
		</p>
	</div>

	<?php do_action('woocommerce_widget_shopping_cart_before_buttons'); ?>

	<p class="woocommerce-mini-cart__buttons buttons"><?php do_action('woocommerce_widget_shopping_cart_buttons'); ?></p>

	<?php do_action('woocommerce_widget_shopping_cart_after_buttons'); ?>

<?php else : ?>

	<p class="woocommerce-mini-cart__empty-message"><?php esc_html_e('No products in the cart.', 'woocommerce'); ?></p>

<?php endif; ?>

<?php do_action('woocommerce_after_mini_cart'); ?>