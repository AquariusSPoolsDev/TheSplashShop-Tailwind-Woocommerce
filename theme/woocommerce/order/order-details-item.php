<?php

/**
 * Order Item Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-item.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! apply_filters('woocommerce_order_item_visible', true, $item)) {
	return;
}
?>
<tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order)); ?>">

	<td class="woocommerce-table__product-name product-name">
		<div class="shopchop-item-image">
			<?php echo $product ? $product->get_image(array(96, 96)) : ''; ?>
		</div>

		<div class="shopchop-item-content">
			<?php
			$base_name = $product ? $product->get_name() : $item->get_name();
			if ($product && $product->is_type('variation')) {
				$parent_product = wc_get_product($product->get_parent_id());
				$base_name = $parent_product->get_name();
			}

			$is_visible        = $product && $product->is_visible();
			$product_permalink = apply_filters('woocommerce_order_item_permalink', $is_visible ? $product->get_permalink($item) : '', $item, $order);

			echo wp_kses_post(apply_filters('woocommerce_order_item_name', $product_permalink ? sprintf('<a class="item-title" href="%s">%s</a>', $product_permalink, esc_html($base_name)) : esc_html($base_name), $item, $is_visible));

			if ($product && $product->is_type('variation')) {
				$variation_list = wc_get_formatted_variation($product->get_variation_attributes(), true);
				echo '<span class="variation-meta">' . esc_html($variation_list) . '</span>';
			}

			do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);
			do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);

			$qty          = $item->get_quantity();
			$refunded_qty = $order->get_qty_refunded_for_item($item_id);

			if ($refunded_qty) {
				$qty_display = '<del>' . esc_html($qty) . '</del> <ins>' . esc_html($qty - ($refunded_qty * -1)) . '</ins>';
			} else {
				$qty_display = esc_html($qty);
			}

			echo apply_filters('woocommerce_order_item_quantity_html', '<div class="shopchop-item-qty">' . sprintf('&times; %s', $qty_display) . '</div>', $item);
			?>
		</div>
	</td>

	<td class="woocommerce-table__product-total product-total">
		<?php echo $order->get_formatted_line_subtotal($item); ?>
	</td>

</tr>

<?php if ($show_purchase_note && $purchase_note) : ?>

	<tr class="woocommerce-table__product-purchase-note product-purchase-note">

		<td colspan="2"><?php echo wpautop(do_shortcode(wp_kses_post($purchase_note))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						?></td>

	</tr>

<?php endif; ?>