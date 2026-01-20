<?php

/**
 * Shop breadcrumb
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/breadcrumb.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     2.3.0
 * @see         woocommerce_breadcrumb()
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! empty($breadcrumb)) {

	echo $wrap_before;

	foreach ($breadcrumb as $key => $crumb) {

		echo $before;

		if (! empty($crumb[1]) && sizeof($breadcrumb) !== $key + 1) {

			echo '<a href="' . esc_url($crumb[1]) . '" class="inline-flex items-center">';

			if ($key === 0) { ?>
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 me-1.5">
					<path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
					<path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
				</svg>
			<?php }

			echo esc_html($crumb[0]);
			echo '</a>';
		} else {
			echo '<span class="text-grey-300">' . esc_html($crumb[0]) . '</span>';
		}

		echo $after;

		if (sizeof($breadcrumb) !== $key + 1) { ?>
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mx-1.5">
				<path d="m9 18 6-6-6-6" />
			</svg>
<?php
		}
	}

	echo $wrap_after;
}
