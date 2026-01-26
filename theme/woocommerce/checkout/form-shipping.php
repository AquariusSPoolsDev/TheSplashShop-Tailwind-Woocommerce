<?php

/**
 * Checkout shipping information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-shipping.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined('ABSPATH') || exit;
?>
<div class="woocommerce-shipping-fields">
	<?php if (true === WC()->cart->needs_shipping_address()) : ?>

		<h3 id="ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input id="ship-to-different-address-checkbox" class="sr-only peer woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked(apply_filters('woocommerce_ship_to_different_address_checked', 'shipping' === get_option('woocommerce_ship_to_destination') ? 1 : 0), 1); ?> type="checkbox" name="ship_to_different_address" value="1" />
				<span class="shrink-0 relative w-13 h-7 bg-grey-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-grey-200 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-background after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-400"></span>
				<span class="select-none ms-4 block">
					<span class="block"><?php esc_html_e('Ship to a different address?', 'woocommerce'); ?></span>
					<span class="block text-sm font-body text-grey-300 font-normal mt-0.5"><?php esc_html_e('We\'ll deliver your order to this address.', 'woocommerce'); ?></span>
				</span>
			</label>
		</h3>

		<div class="shipping_address">

			<?php do_action('woocommerce_before_checkout_shipping_form', $checkout); ?>

			<div class="woocommerce-shipping-fields__field-wrapper">
				<?php
				$fields = $checkout->get_checkout_fields('shipping');

				foreach ($fields as $key => $field) {
					woocommerce_form_field($key, $field, $checkout->get_value($key));
				}
				?>
			</div>

			<?php do_action('woocommerce_after_checkout_shipping_form', $checkout); ?>

		</div>

	<?php endif; ?>
</div>
<div class="woocommerce-additional-fields">
	<?php do_action('woocommerce_before_order_notes', $checkout); ?>

	<?php if (apply_filters('woocommerce_enable_order_notes_field', 'yes' === get_option('woocommerce_enable_order_comments', 'yes'))) : ?>

		<?php if (! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only()) : ?>

			<h3><?php esc_html_e('Additional information', 'woocommerce'); ?></h3>

		<?php endif; ?>

		<div class="woocommerce-additional-fields__field-wrapper">
			<?php foreach ($checkout->get_checkout_fields('order') as $key => $field) : ?>
				<?php woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>
			<?php endforeach; ?>
		</div>

	<?php endif; ?>

	<?php do_action('woocommerce_after_order_notes', $checkout); ?>
</div>