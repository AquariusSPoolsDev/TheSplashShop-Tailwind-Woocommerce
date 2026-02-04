<?php

/**
 * "Order received" message.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/order-received.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.8.0
 *
 * @var WC_Order|false $order
 */

defined('ABSPATH') || exit;
?>
<div class="thank-you-title-wrapper">
	<div class="">
		<h2 class="woocommerce-order-received-title"><?php esc_html_e('Thank you!', 'woocommerce'); ?></h2>

		<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
			<?php
			// Define the base message
			$base_text = __('Your order is being processed. A confirmation email has been sent to:', 'woocommerce');

			// Apply the standard WooCommerce filter
			$message = apply_filters('woocommerce_thankyou_order_received_text', $base_text, $order);

			// Display the filtered message
			echo esc_html($message);

			// Logic to append the email if available
			// We check if $order exists and has a billing email
			if ($order && $order->get_billing_email()) : ?>
				<strong class="block">
					<?php echo esc_html($order->get_billing_email()); ?>
				</strong>
			<?php endif; ?>
		</p>
	</div>
</div>