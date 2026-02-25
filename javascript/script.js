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
jQuery(function ($) {
    $(document).on('click', '#reviews .woocommerce-pagination a', function (e) {
        e.preventDefault();

        const $container = $('#reviews');
        const $dynamic = $('#reviews-dynamic');
        const url = $(this).attr('href');

        $dynamic.addClass('is-loading');

        $dynamic.load(url + ' #reviews-dynamic > *', function (response, status) {
            if (status !== 'success') return;

            $dynamic.removeClass('is-loading');

            $('html, body').animate({
                scrollTop: $container.offset().top - 100
            }, 300);

            $(document.body).trigger('init_reviews');
        });
    });
});


// Product Page AJAX
jQuery(function ($) {
    $(document).on('click', '.woocommerce-pagination a', function (e) {
        // Only run if we are on a shop/product list page
        if ($('.products').length === 0) return;

        e.preventDefault();

        const $container = $('#primary'); // Your main wrapper
        const url = $(this).attr('href');

        // Visual feedback
        $container.css('opacity', '0.5');

        // Load the new content
        // This fetches the target page and replaces everything inside #primary
        $container.load(url + ' #primary > *', function (response, status, xhr) {
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
jQuery(function ($) {
    $('.variations_form').on('show_variation', function (event, variation) {
        // 1. Find your main price container
        // Based on your previous code, it's likely inside .product-details-wrapper or similar
        const $mainPrice = $('.summary .price');

        // 2. If a price exists for the variation, swap it
        if (variation.price_html) {
            $mainPrice.html(variation.price_html);
        }
    });

    // Optional: Reset to the original price range if selection is cleared
    $('.variations_form').on('reset_data', function () {
        const $mainPrice = $('.summary .price');
        const originalPrice = $('.variations_form').data('price_hold');

        if (originalPrice) {
            $mainPrice.html(originalPrice);
        }
    });

    // Store the original price on page load so we can revert to it
    $(document).ready(function () {
        const $mainPrice = $('.summary .price');
        $('.variations_form').data('price_hold', $mainPrice.html());
    });
});



// Enable/Disable Add to Cart button based on variation availability
jQuery(function ($) {
    var $form = $('.variations_form');

    // Explicitly enable the button when a valid variation is found
    $form.on('show_variation', function (event, variation) {
        var $btn = $('.single_add_to_cart_button');

        if (variation.is_purchasable && variation.is_in_stock) {
            $btn
                .removeAttr('disabled')
                .removeClass('disabled wc-variation-is-unavailable');
        } else {
            $btn
                .attr('disabled', 'disabled')
                .addClass('disabled wc-variation-is-unavailable');
        }
    });

    // Disable it again when the selection is cleared
    $form.on('hide_variation reset_data', function () {
        $('.single_add_to_cart_button')
            .attr('disabled', 'disabled')
            .removeClass('wc-variation-is-unavailable');
    });
});



// variation radio button
jQuery(document).ready(function ($) {
    var $form = $('.variations_form');

    $(document).on('click', '.pill-swatch', function (e) {
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
        $form.find('.pill-swatches-container').each(function () {
            var attr = $(this).data('attribute_name');
            var val = $(this).find('.pill-swatch.active').data('value');
            if (val) selectedData[attr] = val;
        });

        // Loop through all pills to check if they should be enabled
        $form.find('.pill-swatch').each(function () {
            var $pill = $(this);
            var pillAttr = $pill.closest('.pill-swatches-container').data('attribute_name');
            var pillVal = $pill.data('value');

            // Create a test "selection" including this pill
            var testSelection = $.extend({}, selectedData);
            testSelection[pillAttr] = pillVal;

            // Check if ANY variation matches this potential selection
            var isPossible = allVariations.some(function (variation) {
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

    $form.on('reset_data', function () {
        $('.pill-swatch').removeClass('active disabled out-of-stock');
    });
});


// Quantity Button
jQuery(document).ready(function ($) {
    $(document).on('click', '.qty-btn', function () {
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
document.addEventListener("DOMContentLoaded", function () {
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



// Update Cart based on timeout
jQuery(function ($) {
    let timeout;
    $(document.body).on('updated_cart_totals', function () {
        attachQtyListener();
    });

    function attachQtyListener() {
        $('div.woocommerce').on('change', 'input.qty', function () {
            if (timeout !== undefined) {
                clearTimeout(timeout);
            }
            timeout = setTimeout(function () {
                $('[name="update_cart"]').trigger('click');
            }, 500);
        });
    }

    attachQtyListener();
});



// Add class for the peyment methods.
jQuery(function ($) {
    'use strict';

    var updatePaymentSelection = function () {
        var $paymentMethods = $('.wc_payment_method');
        $paymentMethods.removeClass('is-selected');

        var $checkedRadio = $('input[name="payment_method"]:checked');
        if ($checkedRadio.length) {
            $checkedRadio.closest('.wc_payment_method').addClass('is-selected');
        }
    };

    // NEW: Card Click Logic
    $(document.body).on('click', '.wc_payment_method', function (e) {
        // Prevent trigger if clicking directly on the radio or an interactive element (like an input or link)
        if ($(e.target).is('input, label, a, button, select, textarea')) {
            return;
        }

        var $radio = $(this).find('input[name="payment_method"]');
        if (!$radio.is(':checked')) {
            $radio.prop('checked', true).trigger('change');
        }
    });

    $(document).ready(updatePaymentSelection);
    $(document.body).on('change', 'input[name="payment_method"]', updatePaymentSelection);
    $(document.body).on('updated_checkout', updatePaymentSelection);
});



// AJAX Product Search Bar Functionality
jQuery(document).ready(function ($) {
    let searchTimeout;
    const searchInput = $('#shopchop-search-input');
    const categorySelect = $('#shopchop-cat-select');
    const resultsContainer = $('.shopchop-search-results');

    // Load categories on page load
    loadCategories();

    function loadCategories() {
        $.ajax({
            url: shopchopDynamicSearch.ajax_url,
            type: 'POST',
            data: {
                action: 'wc_get_categories',
                nonce: shopchopDynamicSearch.nonce
            },
            success: function (response) {
                if (response.success && response.data.categories.length > 0) {
                    response.data.categories.forEach(function (cat) {
                        categorySelect.append(`<option value="${cat.slug}">${cat.name}</option>`);
                    });
                }
            }
        });
    }

    // Search on input with delay
    searchInput.on('keyup', function () {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val().trim();

        if (searchTerm.length === 0) {
            resultsContainer.hide().html('');
            $(this).removeClass('results-active');
            resultsContainer.removeClass('results-active');
            return;
        }

        // Delay search by 300ms after user stops typing
        searchTimeout = setTimeout(function () {
            performSearch(searchTerm);
        }, 300);
    });

    // Search on category change
    categorySelect.on('change', function () {
        const searchTerm = searchInput.val().trim();
        if (searchTerm.length > 0) {
            performSearch(searchTerm);
        }
    });

    function performSearch(searchTerm) {
        const category = categorySelect.val();

        $.ajax({
            url: shopchopDynamicSearch.ajax_url,
            type: 'POST',
            data: {
                action: 'wc_search_products',
                search_term: searchTerm,
                category: category,
                nonce: shopchopDynamicSearch.nonce
            },
            beforeSend: function () {
                resultsContainer.html('<div class="search-loading">Searching...</div>').show();
                searchInput.addClass('results-active');
                resultsContainer.addClass('results-active');
            },
            success: function (response) {
                if (response.success) {
                    displayResults(response.data.products);
                }
            },
            error: function () {
                resultsContainer.html('<div class="search-error">Error loading results</div>');
            }
        });
    }

    function displayResults(products) {
        if (products.length === 0) {
            resultsContainer.html('<div class="no-results">No results found</div>').show();
            searchInput.addClass('results-active');
            resultsContainer.addClass('results-active');
            return;
        }

        let html = '';
        products.forEach(function (product) {
            const image = product.image ? `<img src="${product.image}" alt="${product.title}">` : '[IMAGE]';
            html += `
            <div class="search-result-item">
                <a href="${product.url}">
                    <div class="result-image">${image}</div>
                    <div class="result-details">
                        <span class="result-title">${product.title}</span>
                    </div>
                </a>
            </div>
        `;
        });

        resultsContainer.html(html).show();
        searchInput.addClass('results-active');
        resultsContainer.addClass('results-active');
    }

    // Hide results when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.shopchop-search-wrapper').length) {
            resultsContainer.hide();
            searchInput.removeClass('results-active');
            resultsContainer.removeClass('results-active');
        }
    });

    // Show results when clicking on input if there are results
    searchInput.on('focus', function () {
        if ($(this).val().trim().length > 0 && resultsContainer.html().length > 0) {
            resultsContainer.show();
            searchInput.addClass('results-active');
            resultsContainer.addClass('results-active');
        }
    });
});



// 
jQuery(document).ready(function ($) {
    const accountWrapper = $('.shopchop-account-wrapper');
    const accountTrigger = $('.shopchop-account-trigger');
    const accountDropdown = $('.shopchop-account-dropdown');
    
    let hoverTimeout;
    let isHovering = false;

    // Hover to show dropdown
    accountWrapper.on('mouseenter', function () {
        isHovering = true;
        clearTimeout(hoverTimeout);
        
        // Delay showing dropdown by 200ms
        hoverTimeout = setTimeout(function () {
            if (isHovering) {
                showDropdown();
            }
        }, 200);
    });

    // Hide dropdown when mouse leaves
    accountWrapper.on('mouseleave', function () {
        isHovering = false;
        clearTimeout(hoverTimeout);
        
        // Delay hiding by 300ms to allow moving to dropdown
        hoverTimeout = setTimeout(function () {
            if (!isHovering) {
                hideDropdown();
            }
        }, 300);
    });

    function showDropdown() {
        accountDropdown.fadeIn(200);
        accountTrigger.attr('aria-expanded', 'true');
        accountWrapper.addClass('dropdown-active');
    }

    function hideDropdown() {
        accountDropdown.fadeOut(200);
        accountTrigger.attr('aria-expanded', 'false');
        accountWrapper.removeClass('dropdown-active');
    }

    // Click outside to close
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.shopchop-account-wrapper').length) {
            hideDropdown();
        }
    });

    // Keyboard navigation - ESC to close
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            hideDropdown();
        }
    });

    // Click trigger to toggle (for touch devices)
    accountTrigger.on('click', function (e) {
        e.preventDefault();
        if (accountDropdown.is(':visible')) {
            hideDropdown();
        } else {
            showDropdown();
        }
    });
});



// 
jQuery(document).ready(function ($) {
    const cartWrapper = $('.shopchop-cart-wrapper');
    const cartTrigger = $('.shopchop-cart-trigger');
    const cartDropdown = $('.shopchop-cart-dropdown');
    const cartContent = $('.cart-dropdown-content');
    
    let hoverTimeout;
    let isLoaded = false;
    let isHovering = false;

    // Hover to show dropdown
    cartWrapper.on('mouseenter', function () {
        isHovering = true;
        clearTimeout(hoverTimeout);
        
        hoverTimeout = setTimeout(function () {
            if (isHovering) {
                showCart();
            }
        }, 200);
    });

    // Hide dropdown when mouse leaves
    cartWrapper.on('mouseleave', function () {
        isHovering = false;
        clearTimeout(hoverTimeout);
        
        hoverTimeout = setTimeout(function () {
            if (!isHovering) {
                hideCart();
            }
        }, 300);
    });

    function showCart() {
        if (!isLoaded) {
            loadMiniCart();
        } else {
            cartDropdown.fadeIn(200);
            cartTrigger.attr('aria-expanded', 'true');
            cartWrapper.addClass('dropdown-active');
        }
    }

    function hideCart() {
        cartDropdown.fadeOut(200);
        cartTrigger.attr('aria-expanded', 'false');
        cartWrapper.removeClass('dropdown-active');
    }

    function loadMiniCart() {
        $.ajax({
            url: shopchopDynamicSearch.ajax_url,
            type: 'POST',
            data: {
                action: 'shopchop_get_mini_cart',
                nonce: shopchopDynamicSearch.nonce
            },
            beforeSend: function () {
                cartContent.html('<div class="cart-loading">Loading cart...</div>');
                cartDropdown.fadeIn(200);
                cartTrigger.attr('aria-expanded', 'true');
                cartWrapper.addClass('dropdown-active');
            },
            success: function (response) {
                if (response.success) {
                    cartContent.html(response.data.cart_html);
                    updateCartCount(response.data.cart_count);
                    isLoaded = true;
                    
                    // Attach remove handlers after content loaded
                    attachWooCommerceRemoveHandlers();
                }
            },
            error: function () {
                cartContent.html('<div class="cart-error">Error loading cart</div>');
            }
        });
    }

    function updateCartCount(count) {
        $('.cart-count-badge').text(count >= 0 ? count : '');
        $('.count-number').text(count);
        
        const itemsText = count === 1 ? 'item' : 'items';
        $('.cart-items-count').html(`<span class="count-number">${count}</span> ${itemsText}`);
    }

    // Attach handlers to WooCommerce's remove buttons
    function attachWooCommerceRemoveHandlers() {
        // WooCommerce uses class 'remove' or 'remove_from_cart_button'
        cartContent.find('.remove, .remove_from_cart_button').off('click').on('click', function (e) {
            e.preventDefault();
            
            const $link = $(this);
            const cartItemKey = $link.data('cart_item_key') || $link.attr('data-cart_item_key');
            
            if (cartItemKey) {
                removeCartItem(cartItemKey, $link.closest('.woocommerce-mini-cart-item, .mini_cart_item'));
            }
        });
    }

    function removeCartItem(cartItemKey, cartItemElement) {
        $.ajax({
            url: shopchopDynamicSearch.ajax_url,
            type: 'POST',
            data: {
                action: 'shopchop_remove_cart_item',
                cart_item_key: cartItemKey,
                nonce: shopchopDynamicSearch.nonce
            },
            beforeSend: function () {
                cartItemElement.addClass('removing').css('opacity', '0.5');
            },
            success: function (response) {
                if (response.success) {
                    // Update cart content
                    cartContent.html(response.data.cart_html);
                    updateCartCount(response.data.cart_count);
                    
                    // Re-attach handlers
                    attachWooCommerceRemoveHandlers();
                    
                    // Trigger WooCommerce cart update event
                    $(document.body).trigger('wc_fragment_refresh');
                    $(document.body).trigger('removed_from_cart');
                }
            },
            error: function () {
                cartItemElement.removeClass('removing').css('opacity', '1');
                alert('Failed to remove item. Please try again.');
            }
        });
    }

    // Listen to WooCommerce add to cart events
    $(document.body).on('added_to_cart', function () {
        isLoaded = false;
        
        // Show cart dropdown briefly to indicate item was added
        if (!cartDropdown.is(':visible')) {
            showCart();
            setTimeout(function() {
                if (!isHovering) {
                    hideCart();
                }
            }, 2000);
        } else {
            loadMiniCart();
        }
    });

    // Listen to WooCommerce cart update events
    $(document.body).on('wc_fragment_refresh updated_cart_totals', function () {
        isLoaded = false;
    });

    // Click outside to close
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.shopchop-cart-wrapper').length) {
            hideCart();
        }
    });

    // Keyboard navigation - ESC to close
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && cartDropdown.is(':visible')) {
            hideCart();
        }
    });

    // Click trigger to toggle (for touch devices)
    cartTrigger.on('click', function (e) {
        e.preventDefault();
        
        if (cartDropdown.is(':visible')) {
            hideCart();
        } else {
            showCart();
        }
    });
});