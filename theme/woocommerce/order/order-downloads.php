<?php
/**
 * Order Downloads.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-downloads.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="woocommerce-order-downloads">
	<?php if ( isset( $show_title ) ) : ?>
		<h2 class="woocommerce-order-downloads__title"><?php esc_html_e( 'Downloads', 'woocommerce' ); ?></h2>
	<?php endif; ?>

	<table class="flex w-full woocommerce-table woocommerce-table--order-downloads shop_table shop_table_responsive order_details">
		<?php /*
		<thead>
			<tr>
				<?php foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) : ?>
				<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		*/ ?>
		<tbody class="flex flex-col w-full flex-wrap gap-4 lg:gap-6">
			<?php foreach ( $downloads as $download ) : ?>
				<tr class="border border-grey-200 p-4 rounded-xl">
					<td class="w-full flex gap-3" data-title="download-row">
						<div class="download-file-product-image wc-product-img shrink-0">
							<?php 
							if ( wc_get_product( $download['product_id'] ) ) {
								echo wc_get_product( $download['product_id'] )->get_image( array( 72, 72 ) ); 
							}
							?>
						</div>
						<div class="download-file-meta wc-file-meta grow lg:relative">
							<div class="wc-product-name">
								<?php 
									if ( $download['product_url'] ) {
										echo '<a href="' . esc_url( $download['product_url'] ) . '">' . esc_html( $download['product_name'] ) . '</a>';
									} else {
										echo esc_html( $download['product_name'] );
									}
								?>
							</div>

							<div class="wc-product-download-remain">
								<strong>Downloads Remaining: </strong>
								<?php echo is_numeric( $download['downloads_remaining'] ) ? esc_html( $download['downloads_remaining'] ) : esc_html__( '&infin;', 'woocommerce' ); ?>
							</div>

							<div class="wc-product-download-expire">
								<strong>Expires: </strong>
								<?php 
								if ( ! empty( $download['access_expires'] ) ) {
								echo '<time datetime="' . esc_attr( date( 'Y-m-d', strtotime( $download['access_expires'] ) ) ) . '" title="' . esc_attr( strtotime( $download['access_expires'] ) ) . '">' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $download['access_expires'] ) ) ) . '</time>';
							} else {
								esc_html_e( 'Never', 'woocommerce' );
							} ?>
							</div>
							<div class="wc-product-download-btn text-end lg:absolute lg:right-0 lg:bottom-0">
								<?php echo '<a href="' . esc_url( $download['download_url'] ) . '" class="woocommerce-MyAccount-downloads-file button alt px-2.5 py-1 inline-flex items-center normal-case! text-base! border-secondary border-2 bg-transparent! text-secondary! hover:bg-secondary-100! cursor-pointer h-max!">' . esc_html( $download['download_name'] ) . '</a>'; ?>
							</div>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</section>
