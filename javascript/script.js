/**
 * Front-end JavaScript
 *
 * The JavaScript code you place here will be processed by esbuild. The output
 * file will be created at `../theme/js/script.min.js` and enqueued in
 * `../theme/functions.php`.
 *
 * For esbuild documentation, please see:
 * https://esbuild.github.io/
 */

jQuery(function($) {
    // Function to center/slide the thumbnail row
    const slideThumbnails = function() {
        const $container = $('.flex-control-nav.flex-control-thumbs');
        const $activeThumb = $container.find('img.flex-active').parent('li');
        
        if ($activeThumb.length) {
            // Calculate the scroll position to keep active thumb in the "6-pack" window
            const containerWidth = $container.width();
            const thumbOffset = $activeThumb.position().left + $container.scrollLeft();
            const targetScroll = thumbOffset - (containerWidth / 2) + ($activeThumb.width() / 2);

            $container.animate({
                scrollLeft: targetScroll
            }, 300);
        }
    };

    // 1. Run when a thumbnail is clicked
    $(document).on('click', '.flex-control-nav img', function() {
        setTimeout(slideThumbnails, 50);
    });

    // 2. Run when the main slider arrows are clicked
    // WooCommerce triggers 'flexslider' events on the gallery wrapper
    $('.woocommerce-product-gallery').on('after', function() {
        slideThumbnails();
    });

    // 3. Initial check on load
    $(window).on('load', function() {
        slideThumbnails();
    });
});