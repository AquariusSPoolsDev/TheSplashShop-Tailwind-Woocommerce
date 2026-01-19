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

jQuery(function ($) {
    // Function to center/slide the thumbnail row
    const slideThumbnails = function () {
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
    $(document).on('click', '.flex-control-nav img', function () {
        setTimeout(slideThumbnails, 50);
    });

    // 2. Run when the main slider arrows are clicked
    // WooCommerce triggers 'flexslider' events on the gallery wrapper
    $('.woocommerce-product-gallery').on('after', function () {
        slideThumbnails();
    });

    // 3. Initial check on load
    $(window).on('load', function () {
        slideThumbnails();
    });
});


jQuery(document).ready(function ($) {
    $(document.body).on('adding_to_cart', function (e, $button) {
        // Show loading state
        $button.addClass('loading')
            .text('Adding...')
            .prop('disabled', true);
    });

    $(document.body).on('added_to_cart', function (e, fragments, cart_hash, $button) {
        // Show success state briefly
        $button.removeClass('loading')
            .addClass('added')
            .text('Added!')
            .prop('disabled', false);

        // Optional: Auto-show View cart after brief delay
        setTimeout(function () {
            $button.next('.added_to_cart').fadeIn(200);
        }, 300);
    });
});


// WC Comments AJAX
jQuery(function($) {
    $(document).on('click', '#reviews .woocommerce-pagination a', function(e) {
        e.preventDefault();

        const $container = $('#reviews');
        const $commentList = $('#comments');
        const url = $(this).attr('href');

        $commentList.css('opacity', '0.5');
        
        $commentList.load(url + ' #comments > *', function(response, status, xhr) {
            if (status === "error") {
                return;
            }

            $commentList.css('opacity', '1');

            $('html, body').animate({
                scrollTop: $container.offset().top - 100
            }, 300);
            
            $(document.body).trigger('init_reviews');
        });
    });
});

// Product Page AJAX
jQuery(function($) {
    $(document).on('click', '.woocommerce-pagination a', function(e) {
        // Only run if we are on a shop/product list page
        if ($('.products').length === 0) return;

        e.preventDefault();

        const $container = $('#primary'); // Your main wrapper
        const url = $(this).attr('href');

        // Visual feedback
        $container.css('opacity', '0.5');

        // Load the new content
        // This fetches the target page and replaces everything inside #primary
        $container.load(url + ' #primary > *', function(response, status, xhr) {
            if (status === "error") return;

            $container.css('opacity', '1');

            // Scroll to top of the shop grid
            $('html, body').animate({
                scrollTop: $container.offset().top - 50
            }, 300);

            // Re-trigger WooCommerce scripts (like fragments or tooltips if used)
            $(document.body).trigger('post-load');
        });
    });
});

// Variable price update at the price
jQuery(function($) {
    $('.variations_form').on('show_variation', function(event, variation) {
        // 1. Find your main price container
        // Based on your previous code, it's likely inside .product-details-wrapper or similar
        const $mainPrice = $('.summary .price');

        // 2. If a price exists for the variation, swap it
        if (variation.price_html) {
            $mainPrice.html(variation.price_html);
        }
    });

    // Optional: Reset to the original price range if selection is cleared
    $('.variations_form').on('reset_data', function() {
        const $mainPrice = $('.summary .price');
        const originalPrice = $('.variations_form').data('price_hold');
        
        if (originalPrice) {
            $mainPrice.html(originalPrice);
        }
    });

    // Store the original price on page load so we can revert to it
    $(document).ready(function() {
        const $mainPrice = $('.summary .price');
        $('.variations_form').data('price_hold', $mainPrice.html());
    });
});


// variation radio button
jQuery(document).ready(function($) {
    var $form = $('.variations_form');
    
    $(document).on('click', '.pill-swatch', function(e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.hasClass('disabled')) return;

        var val = $btn.data('value');
        var $container = $btn.closest('.pill-swatches-container');
        
        // Update the real (hidden) select
        $container.prev('div').find('select').val(val).trigger('change');
        
        $btn.addClass('active').siblings().removeClass('active');
        updatePillAvailability();
    });

    function updatePillAvailability() {
        var allVariations = $form.data('product_variations');
        var selectedData = {};

        // Get currently selected attributes
        $form.find('.pill-swatches-container').each(function() {
            var attr = $(this).data('attribute_name');
            var val = $(this).find('.pill-swatch.active').data('value');
            if (val) selectedData[attr] = val;
        });

        // Loop through all pills to check if they should be enabled
        $form.find('.pill-swatch').each(function() {
            var $pill = $(this);
            var pillAttr = $pill.closest('.pill-swatches-container').data('attribute_name');
            var pillVal = $pill.data('value');
            
            // Create a test "selection" including this pill
            var testSelection = $.extend({}, selectedData);
            testSelection[pillAttr] = pillVal;

            // Check if ANY variation matches this potential selection
            var isPossible = allVariations.some(function(variation) {
                var match = true;
                for (var attr in testSelection) {
                    // Check if variation matches selected attribute (or is "any")
                    if (variation.attributes[attr] && variation.attributes[attr] !== "" && variation.attributes[attr] !== testSelection[attr]) {
                        match = false;
                    }
                }
                return match && variation.is_purchasable;
            });

            $pill.toggleClass('disabled', !isPossible);

        });
    }

    $form.on('reset_data', function() {
        $('.pill-swatch').removeClass('active disabled out-of-stock');
    });
});


// Quantity Button
jQuery(document).ready(function($) {
    $(document).on('click', '.qty-btn', function() {
        var $button = $(this);
        var $input = $button.closest('.quantity-nav').find('input.qty');
        var currentVal = parseFloat($input.val());
        var max = parseFloat($input.attr('max'));
        var min = parseFloat($input.attr('min'));
        var step = parseFloat($input.attr('step'));

        if ($button.hasClass('plus')) {
            if (!max || currentVal < max) {
                $input.val(currentVal + step).trigger('change');
            }
        } else {
            if (currentVal > min) {
                $input.val(currentVal - step).trigger('change');
            }
        }
    });
});


// My Account Scroll Behabviour
document.addEventListener("DOMContentLoaded", function() {
    const activeLink = document.querySelector('.is-active');
    if (activeLink) {
        // This will scroll the horizontal menu to the active item automatically
        activeLink.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'center'
        });
    }
});