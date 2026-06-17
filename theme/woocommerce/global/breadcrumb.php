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
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" class="w-4 h-4 me-1.5"><path d="M219.31,108.68l-80-80a16,16,0,0,0-22.62,0l-80,80A15.87,15.87,0,0,0,32,120v96a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V160h32v56a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V120A15.87,15.87,0,0,0,219.31,108.68ZM208,208H160V152a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8v56H48V120l80-80,80,80Z"></path></svg>
			<?php }

			echo esc_html($crumb[0]);
			echo '</a>';
		} else {
			echo '<span class="text-neutral-700">' . esc_html($crumb[0]) . '</span>';
		}

		echo $after;

		if (sizeof($breadcrumb) !== $key + 1) { ?>
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" class="w-4 h-4 mx-1.5"><path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path></svg>
<?php
		}
	}

	echo $wrap_after;
}
