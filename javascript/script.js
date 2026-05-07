/**
 * ShopChop – Main JavaScript
 *
 * Refactored & optimised for WooCommerce.
 *
 * Key improvements:
 *  - Single IIFE wrapping the entire file → one jQuery alias, no global leaks
 *  - `ShopChop` namespace keeps every module discoverable & tree-shakeable
 *  - `Utils`   – shared debounce + labels helper
 *  - `CartAPI` – single source of truth for all mini-cart AJAX calls
 *  - `createDropdown` factory – eliminates duplicated account / cart dropdown code
 *  - `createMiniCart`  factory – eliminates duplicated desktop / mobile cart logic
 *  - `const` / `let` throughout; no `var`
 *  - All 16 modules boot from a single `$(function () { … })` call
 */

(function ($) {
	'use strict';

	/* =========================================================================
        Namespace
    ========================================================================= */
	window.ShopChop = window.ShopChop || {};

	/* =========================================================================
        Utils
    ========================================================================= */
	const Utils = {
		/**
		 * Delay `fn` until `delay` ms after the last call.
		 * @param {Function} fn
		 * @param {number}   delay  ms
		 * @returns {Function}
		 */
		debounce(fn, delay) {
			let timer;
			return function (...args) {
				clearTimeout(timer);
				timer = setTimeout(() => fn.apply(this, args), delay);
			};
		},

		/**
		 * Build the "N item(s)" label used in cart headers.
		 * @param {number} count
		 * @returns {string}
		 */
		itemsLabel(count) {
			const word = count === 1 ? 'item' : 'items';
			return `<span class="count-number">${count}</span> ${word}`;
		},
	};

	/* =========================================================================
        CartAPI – shared AJAX helpers
    ========================================================================= */
	const CartAPI = {
		/**
		 * Fetch current mini-cart HTML + count.
		 * @param {object} callbacks  jQuery AJAX callbacks (beforeSend, success, error)
		 */
		getMiniCart(callbacks = {}) {
			return $.ajax({
				url: shopchopDynamicSearch.ajax_url,
				type: 'POST',
				data: {
					action: 'shopchop_get_mini_cart',
					nonce: shopchopDynamicSearch.nonce,
				},
				...callbacks,
			});
		},

		/**
		 * Remove a single item from the cart.
		 * @param {string} cartItemKey
		 * @param {object} callbacks
		 */
		removeItem(cartItemKey, callbacks = {}) {
			return $.ajax({
				url: shopchopDynamicSearch.ajax_url,
				type: 'POST',
				data: {
					action: 'shopchop_remove_cart_item',
					cart_item_key: cartItemKey,
					nonce: shopchopDynamicSearch.nonce,
				},
				...callbacks,
			});
		},
	};

	/* =========================================================================
        createDropdown – factory
        Shared hover / click / keyboard / outside-click logic used by both the
        Account dropdown and the Cart dropdown.

        @param {object} opts
        .wrapper   {jQuery}    outermost element (hover target)
        .trigger   {jQuery}    button that toggles the dropdown
        .dropdown  {jQuery}    panel to show/hide
        .onShow    {Function?} called before the panel fades in (optional)
    ========================================================================= */
	function createDropdown({ wrapper, trigger, dropdown, onShow }) {
		const DELAY_IN = 200; // ms before opening on hover
		const DELAY_OUT = 300; // ms before closing after mouse leaves

		let hoverTimer;
		let isHovering = false;

		function show() {
			if (onShow) onShow();
			dropdown.fadeIn(200);
			trigger.attr('aria-expanded', 'true');
			wrapper.addClass('dropdown-active');
		}

		function hide() {
			dropdown.fadeOut(200);
			trigger.attr('aria-expanded', 'false');
			wrapper.removeClass('dropdown-active');
		}

		function toggle() {
			dropdown.is(':visible') ? hide() : show();
		}

		// ── Hover (desktop) ───────────────────────────────────────────────
		wrapper.on('mouseenter', () => {
			isHovering = true;
			clearTimeout(hoverTimer);
			hoverTimer = setTimeout(() => {
				if (isHovering) show();
			}, DELAY_IN);
		});

		wrapper.on('mouseleave', () => {
			isHovering = false;
			clearTimeout(hoverTimer);
			hoverTimer = setTimeout(() => {
				if (!isHovering) hide();
			}, DELAY_OUT);
		});

		// ── Click → navigate to href (hover already handles open/close) ──
		// Do nothing — let the <a> href fire naturally.

		// ── Close when clicking outside ───────────────────────────────────
		$(document).on('click', (e) => {
			if (!$(e.target).closest(wrapper).length) hide();
		});

		// ── Close on Escape ───────────────────────────────────────────────
		$(document).on('keydown', (e) => {
			if (e.key === 'Escape') hide();
		});

		return { show, hide, toggle, isHovering: () => isHovering };
	}

	/* =========================================================================
        createMiniCart – factory
        Shared AJAX load + remove logic used by both the desktop dropdown cart
        and the mobile drawer cart.

        @param {object} opts
        .contentEl      {jQuery}    element that receives the cart HTML
        .onCountUpdate  {Function}  called with (count) after every refresh
    ========================================================================= */
	function createMiniCart({ contentEl, onCountUpdate }) {
		let isLoaded = false;

		function updateCount(count) {
			onCountUpdate(count);
		}

		function attachRemoveHandlers() {
			contentEl
				.find('.remove, .remove_from_cart_button')
				.off('click.shopchop')
				.on('click.shopchop', function (e) {
					e.preventDefault();
					const $link = $(this);
					const key =
						$link.data('cart_item_key') ||
						$link.attr('data-cart_item_key');
					if (key)
						removeItem(
							key,
							$link.closest(
								'.woocommerce-mini-cart-item, .mini_cart_item'
							)
						);
				});
		}

		function load() {
			CartAPI.getMiniCart({
				beforeSend() {
					contentEl.html(
						'<div class="cart-loading">Loading cart…</div>'
					);
				},
				success(response) {
					if (!response.success) return;
					contentEl.html(response.data.cart_html);
					updateCount(response.data.cart_count);
					attachRemoveHandlers();
					isLoaded = true;
				},
				error() {
					contentEl.html(
						'<div class="cart-error">Error loading cart. Please refresh.</div>'
					);
				},
			});
		}

		function removeItem(key, $el) {
			CartAPI.removeItem(key, {
				beforeSend() {
					$el.addClass('removing').css('opacity', '0.5');
				},
				success(response) {
					if (!response.success) return;
					contentEl.html(response.data.cart_html);
					updateCount(response.data.cart_count);
					attachRemoveHandlers();
					// Keep WooCommerce fragment system in sync
					$(document.body)
						.trigger('wc_fragment_refresh')
						.trigger('removed_from_cart');
				},
				error() {
					$el.removeClass('removing').css('opacity', '1');
					ShopChop.Toast.show('Failed to remove item. Please try again.', 'error');
				},
			});
		}

		return {
			load,
			markStale() {
				isLoaded = false;
			},
			getIsLoaded() {
				return isLoaded;
			},
		};
	}

	/* =========================================================================
        1. Product Slider  (Elementor + SwiperJS)
    ========================================================================= */
	ShopChop.ProductSlider = {
		init($scope) {
			$scope.find('.shopchop-product-slider').each(function () {
				new Swiper(this, {
					slidesPerView: 2,
					spaceBetween: 20,
					breakpoints: {
						768: { slidesPerView: 3 },
						1024: { slidesPerView: 4 },
					},
					scrollbar: {
						el: this.querySelector('.swiper-scrollbar'),
						draggable: true,
					},
				});
			});
		},
	};

	// Register with Elementor outside the main ready block
	$(window).on('elementor/frontend/init', () => {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/shopchop_products_list.default',
			ShopChop.ProductSlider.init
		);
	});

	/* =========================================================================
        2A. Hero Carousel  (SwiperJS)
    ========================================================================= */
	ShopChop.HeroCarousel = {
		init() {
			document.querySelectorAll('.shopchop-hero-swiper[data-swiper]').forEach((el) => {
				try {
					const config = JSON.parse(el.dataset.swiper);
					new Swiper(el, config);
				} catch (e) {
					console.warn('ShopChop HeroCarousel: invalid config', e);
				}
			});
		},
	};

	/* =========================================================================
        2B. Products Carousel  (SwiperJS)
    ========================================================================= */
	ShopChop.ProductsCarousel = {
		init() {
			document.querySelectorAll('.shopchop-products-swiper[data-swiper]').forEach((el) => {
				try {
					const config = JSON.parse(el.dataset.swiper);
					new Swiper(el, config);
				} catch (e) {
					console.warn('ShopChop ProductsCarousel: invalid config', e);
				}
			});
		},
	};

	/* =========================================================================
        2C. Testimonials Carousel  (SwiperJS)
    ========================================================================= */
	ShopChop.TestimonialsCarousel = {
		init() {
			document.querySelectorAll('.shopchop-testimonials-swiper[data-swiper]').forEach((el) => {
				try {
					const config = JSON.parse(el.dataset.swiper);
					new Swiper(el, config);
				} catch (e) {
					console.warn('ShopChop TestimonialsCarousel: invalid config', e);
				}
			});
		},
	};

	/* =========================================================================
        2D. Product Gallery  (SwiperJS)
    ========================================================================= */
	ShopChop.ProductGallery = {
		init() {
			const galleryEl = document.querySelector(
				'.woocommerce-product-gallery'
			);
			if (!galleryEl) return;

			const mainEl = galleryEl.querySelector('.splashshop-gallery-main');
			const thumbsEl = galleryEl.querySelector(
				'.splashshop-gallery-thumbs'
			);
			if (!mainEl) return;

			let thumbsSwiper = null;
			if (thumbsEl) {
				thumbsSwiper = new Swiper(thumbsEl, {
					slidesPerView: 'auto',
					spaceBetween: 8,
					watchSlidesProgress: true,
					freeMode: true,
				});
			}

			new Swiper(mainEl, {
				spaceBetween: 0,
				navigation: thumbsEl
					? {
							nextEl: mainEl.querySelector(
								'.swiper-button-next'
							),
							prevEl: mainEl.querySelector(
								'.swiper-button-prev'
							),
						}
					: false,
				thumbs: thumbsSwiper ? { swiper: thumbsSwiper } : undefined,
				on: {
					afterInit() {
						galleryEl.style.opacity = '1';
					},
				},
			});
		},
	};

	/* =========================================================================
        3. Add-to-Cart Button States
    ========================================================================= */
	ShopChop.CartButton = {
		init() {
			$(document.body)
				.on('adding_to_cart', (e, $btn) => {
					$btn.addClass('loading')
						.text('Adding…')
						.prop('disabled', true);
				})
				.on('added_to_cart', (e, fragments, hash, $btn) => {
					$btn.removeClass('loading')
						.addClass('added')
						.text('Added!')
						.prop('disabled', false);
					setTimeout(
						() => $btn.next('.added_to_cart').fadeIn(200),
						300
					);
				});
		},
	};

	/* =========================================================================
        4. Reviews AJAX Pagination
    ========================================================================= */
	ShopChop.ReviewsPagination = {
		init() {
			$(document).on(
				'click',
				'#reviews .woocommerce-pagination a',
				function (e) {
					e.preventDefault();

					const $dynamic = $('#reviews-dynamic');
					const url = $(this).attr('href');

					$dynamic
						.addClass('is-loading')
						.load(
							`${url} #reviews-dynamic > *`,
							(response, status) => {
								if (status !== 'success') return;
								$dynamic.removeClass('is-loading');
								$('html, body').animate(
									{
										scrollTop:
											$('#reviews').offset().top - 100,
									},
									300
								);
								$(document.body).trigger('init_reviews');
							}
						);
				}
			);
		},
	};

	/* =========================================================================
        5. Shop / Archive AJAX Pagination
    ========================================================================= */
	ShopChop.ShopPagination = {
		init() {
			$(document).on('click', '.woocommerce-pagination a', function (e) {
				// Only intercept on pages that have a product grid
				if (!$('.products').length) return;
				e.preventDefault();

				const $container = $('#primary');
				const url = $(this).attr('href');

				$container
					.css('opacity', '0.5')
					.load(`${url} #primary > *`, (response, status) => {
						if (status === 'error') return;
						$container.css('opacity', '1');
						$('html, body').animate(
							{ scrollTop: $container.offset().top - 50 },
							300
						);
						$(document.body).trigger('post-load');
					});
			});
		},
	};

	/* =========================================================================
        6. Variable Product – Price display & Add-to-Cart availability
        (Previously two separate event-binding blocks; merged into one.)
    ========================================================================= */
	ShopChop.VariableProduct = {
		init() {
			const $form = $('.variations_form');
			if (!$form.length) return;

			const $mainPrice = $('.summary .price');
			const $addBtn = $('.single_add_to_cart_button');

			// Snapshot the default price so we can restore it on reset
			$form.data('price_hold', $mainPrice.html());

			$form
				.on('show_variation', (e, variation) => {
					// Update displayed price
					if (variation.price_html)
						$mainPrice.html(variation.price_html);

					// Update button state
					const purchasable =
						variation.is_purchasable && variation.is_in_stock;
					$addBtn
						.prop('disabled', !purchasable)
						.toggleClass(
							'disabled wc-variation-is-unavailable',
							!purchasable
						);
				})
				.on('reset_data', () => {
					// Restore original price
					const originalPrice = $form.data('price_hold');
					if (originalPrice) $mainPrice.html(originalPrice);

					// Disable button until a valid variation is chosen again
					$addBtn
						.prop('disabled', true)
						.removeClass('wc-variation-is-unavailable');
				})
				.on('hide_variation', () => {
					$addBtn
						.prop('disabled', true)
						.removeClass('wc-variation-is-unavailable');
				});
		},
	};

	/* =========================================================================
        7. Pill / Swatch Variation Selector
    ========================================================================= */
	ShopChop.PillSwatches = {
		init() {
			const $form = $('.variations_form');
			if (!$form.length) return;

			$(document).on('click', '.pill-swatch', function (e) {
				e.preventDefault();
				const $btn = $(this);
				if ($btn.hasClass('disabled')) return;

				// Sync to the hidden <select>
				$btn.closest('.pill-swatches-container')
					.prev('div')
					.find('select')
					.val($btn.data('value'))
					.trigger('change');

				$btn.addClass('active').siblings().removeClass('active');
				ShopChop.PillSwatches.updateAvailability($form);
			});

			$form.on('reset_data', () =>
				$('.pill-swatch').removeClass('active disabled out-of-stock')
			);
		},

		/**
		 * Enable / disable each pill based on whether it can form a valid variation
		 * with the currently selected attributes.
		 */
		updateAvailability($form) {
			const allVariations = $form.data('product_variations');
			const selected = {};

			$form.find('.pill-swatches-container').each(function () {
				const attr = $(this).data('attribute_name');
				const val = $(this).find('.pill-swatch.active').data('value');
				if (val) selected[attr] = val;
			});

			$form.find('.pill-swatch').each(function () {
				const $pill = $(this);
				const attr = $pill
					.closest('.pill-swatches-container')
					.data('attribute_name');
				const testSel = { ...selected, [attr]: $pill.data('value') };

				const isPossible = allVariations.some((variation) => {
					if (!variation.is_purchasable) return false;
					return Object.entries(testSel).every(([key, val]) => {
						const varAttr = variation.attributes[key];
						// Empty string means "any value" in WooCommerce
						return !varAttr || varAttr === '' || varAttr === val;
					});
				});

				$pill.toggleClass('disabled', !isPossible);
			});
		},
	};

	/* =========================================================================
        8. Quantity +/− Buttons
    ========================================================================= */
	ShopChop.QuantityButtons = {
		init() {
			$(document).on('click', '.qty-btn', function () {
				const $input = $(this)
					.closest('.quantity-nav')
					.find('input.qty');
				const current = parseFloat($input.val());
				const max = parseFloat($input.attr('max'));
				const min = parseFloat($input.attr('min'));
				const step = parseFloat($input.attr('step'));

				if ($(this).hasClass('plus')) {
					if (!max || current < max)
						$input.val(current + step).trigger('change');
				} else {
					if (current > min)
						$input.val(current - step).trigger('change');
				}
			});
		},
	};

	/* =========================================================================
        9. My Account – Horizontal Menu Scroll to Active Item
    ========================================================================= */
	ShopChop.AccountMenu = {
		init() {
			const activeLink = document.querySelector('.is-active');
			if (activeLink) {
				activeLink.scrollIntoView({
					behavior: 'smooth',
					block: 'nearest',
					inline: 'center',
				});
			}
		},
	};

	/* =========================================================================
        10. Cart – Auto-update Totals on Quantity Change
    ========================================================================= */
	ShopChop.CartAutoUpdate = {
		init() {
			const triggerUpdate = Utils.debounce(
				() => $('[name="update_cart"]').trigger('click'),
				500
			);

			const attachListener = () => {
				$('div.woocommerce').on('change', 'input.qty', triggerUpdate);
			};

			attachListener();
			// Re-attach after WooCommerce rebuilds the cart HTML
			$(document.body).on('updated_cart_totals', attachListener);
		},
	};

	/* =========================================================================
        11. Payment Method – Highlight Selected Method Card
    ========================================================================= */
	ShopChop.PaymentMethods = {
		update() {
			$('.wc_payment_method').removeClass('is-selected');
			$('input[name="payment_method"]:checked')
				.closest('.wc_payment_method')
				.addClass('is-selected');
		},

		init() {
			const { update } = ShopChop.PaymentMethods;

			// Clicking anywhere on the card (not an interactive child) selects it
			$(document.body).on('click', '.wc_payment_method', function (e) {
				if ($(e.target).is('input, label, a, button, select, textarea'))
					return;
				const $radio = $(this).find('input[name="payment_method"]');
				if (!$radio.is(':checked'))
					$radio.prop('checked', true).trigger('change');
			});

			$(document.body)
				.on('change', 'input[name="payment_method"]', update)
				.on('updated_checkout', update);

			update(); // Set initial state
		},
	};

	/* =========================================================================
        12. AJAX Product Search Bar
    ========================================================================= */
	ShopChop.Search = {
		init() {
			const $input = $('.shopchop-search-input');
			const $catSelect = $('.shopchop-cat-select');
			const $results = $('.shopchop-search-results');
			if (!$input.length) return;

			// ── Load categories ───────────────────────────────────────────
			$.ajax({
				url: shopchopDynamicSearch.ajax_url,
				type: 'POST',
				data: {
					action: 'wc_get_categories',
					nonce: shopchopDynamicSearch.nonce,
				},
				success(response) {
					if (response.success && response.data.categories.length) {
						response.data.categories.forEach((cat) => {
							$catSelect.append(
								`<option value="${cat.slug}">${cat.name}</option>`
							);
						});
					}
				},
			});

			// ── Helpers ───────────────────────────────────────────────────
			const setActive = (active) =>
				$input.add($results).toggleClass('results-active', active);

			/** Hide panel but keep HTML so re-focusing can re-show results. */
			const hideResults = () => {
				$results.hide();
				setActive(false);
			};

			/** Hide AND clear HTML (used when input is empty). */
			const clearResults = () => {
				$results.hide().html('');
				setActive(false);
			};

			// ── Perform search ────────────────────────────────────────────
			const search = Utils.debounce((term) => {
				$.ajax({
					url: shopchopDynamicSearch.ajax_url,
					type: 'POST',
					data: {
						action: 'wc_search_products',
						search_term: term,
						category: $catSelect.val(),
						nonce: shopchopDynamicSearch.nonce,
					},
					beforeSend() {
						$results
							.html(
								'<div class="search-loading">Searching…</div>'
							)
							.show();
						setActive(true);
					},
					success(response) {
						if (!response.success) return;
						const { products } = response.data;

						if (!products.length) {
							$results.html(
								'<div class="no-results">No results found</div>'
							);
							return;
						}

						const html = products
							.map((p) => {
								const img = p.image
									? `<img src="${p.image}" alt="${p.title}">`
									: '';
								return `
                                <div class="search-result-item" role="option" aria-label="${p.title}">
                                    <a href="${p.url}">
                                        <div class="result-image">${img}</div>
                                        <div class="result-details">
                                            <span class="result-title">${p.title}</span>
                                        </div>
                                    </a>
                                </div>`;
							})
							.join('');

						$results.html(html).show();
						setActive(true);
					},
					error() {
						$results.html(
							'<div class="search-error">Error loading results</div>'
						);
					},
				});
			}, 300);

			// ── Event listeners ───────────────────────────────────────────
			$input.on('keyup', function () {
				const term = $(this).val().trim();
				term.length ? search(term) : clearResults();
			});

			$input.on('focus', function () {
				if ($(this).val().trim() && $results.html().trim()) {
					$results.show();
					setActive(true);
				}
			});

			$catSelect.on('change', () => {
				const term = $input.val().trim();
				if (term.length) search(term);
			});

			$(document).on('click', (e) => {
				if (!$(e.target).closest('.shopchop-search-wrapper').length)
					hideResults();
			});
		},
	};

	/* =========================================================================
        13. Desktop Account Dropdown
    ========================================================================= */
	ShopChop.AccountDropdown = {
		init() {
			const wrapper = $('.shopchop-account-wrapper');
			if (!wrapper.length) return;

			createDropdown({
				wrapper,
				trigger: $('.shopchop-account-trigger'),
				dropdown: $('.shopchop-account-dropdown'),
			});
		},
	};

	/* =========================================================================
        14. Desktop Cart Dropdown
    ========================================================================= */
	ShopChop.CartDropdown = {
		init() {
			const wrapper = $('.shopchop-cart-wrapper');
			if (!wrapper.length) return;

			const contentEl = $('.cart-dropdown-content');
			const trigger = $('.shopchop-cart-trigger');
			const dropdownEl = $('.shopchop-cart-dropdown');

			const miniCart = createMiniCart({
				contentEl,
				onCountUpdate(count) {
					$('.cart-count-badge').text(count >= 0 ? count : '');
					$('.count-number').text(count);
					$('.cart-items-count').html(Utils.itemsLabel(count));
				},
			});

			const dropdown = createDropdown({
				wrapper,
				trigger,
				dropdown: dropdownEl,
				// Load cart content the first time the panel opens
				onShow() {
					if (!miniCart.getIsLoaded()) miniCart.load();
				},
			});

			// When an item is added show the cart briefly, then auto-close
			let isHoveringCart = false;
			wrapper.on('mouseenter', () => { isHoveringCart = true; });
			wrapper.on('mouseleave', () => { isHoveringCart = false; });

			$(document.body).on('added_to_cart', () => {
				miniCart.markStale();

				if (!dropdownEl.is(':visible')) {
					dropdown.show();
					setTimeout(() => {
						if (!isHoveringCart) dropdown.hide();
					}, 2000);
				} else {
					miniCart.load();
				}
			});

			$(document.body).on(
				'wc_fragment_refresh updated_cart_totals',
				() => {
					miniCart.markStale();
				}
			);
		},
	};

	/* =========================================================================
        15. Mobile Drawers – Search, Cart, Menu
    ========================================================================= */
	ShopChop.MobileDrawers = {
		init() {
			const byId = (id) => document.getElementById(id);

			const searchDrawer = byId('mobile-search');
			const cartDrawer = byId('mobile-mini-cart');
			const menuDrawer = byId('mobile-panel');
			const backdrop = byId('backdrop');
			const searchBtn = byId('mobile-search-toggle');
			const cartBtn = byId('mobile-cart-toggle');
			const menuBtn = byId('mobile-menu-toggle');
			const cartClose = byId('cart-close');
			const searchClose = byId('search-close');
			const menuClose = byId('menu-close');

			// Guard: elements only exist on pages with the mobile header
			if (!menuBtn) return;

			const iconOpen = menuBtn.querySelector('.toggle-open');
			const iconClose = menuBtn.querySelector('.toggle-close');

			// ── Generic open / close helpers ──────────────────────────────
			const lockScroll = () => {
				document.body.style.overflow = 'hidden';
			};
			const unlockScroll = () => {
				document.body.style.overflow = '';
			};

			const openDrawer = (el, classToRemove) => {
				el.classList.add('open');
				el.classList.remove(classToRemove);
				backdrop.classList.add('open');
				lockScroll();
			};

			const closeDrawer = (el, classToAdd) => {
				el.classList.remove('open');
				el.classList.add(classToAdd);
				backdrop.classList.remove('open');
				unlockScroll();
			};

			// ── Focus helpers ─────────────────────────────────────────────
			const focusFirst = (el) => {
				const target = el.querySelector('input, button, a, select, textarea, [tabindex]:not([tabindex="-1"])');
				if (target) target.focus();
			};

			const getFocusable = (el) =>
				[...el.querySelectorAll('input, button, a, select, textarea, [tabindex]:not([tabindex="-1"])')].filter(
					(n) => !n.disabled && n.offsetParent !== null
				);

			const trapFocus = (e, el) => {
				const nodes = getFocusable(el);
				if (!nodes.length) return;
				const first = nodes[0];
				const last = nodes[nodes.length - 1];
				if (e.shiftKey && document.activeElement === first) {
					e.preventDefault();
					last.focus();
				} else if (!e.shiftKey && document.activeElement === last) {
					e.preventDefault();
					first.focus();
				}
			};

			// ── Per-drawer wrappers ───────────────────────────────────────
			const openSearch = () => {
				openDrawer(searchDrawer, '-translate-y-full');
				searchBtn.setAttribute('aria-expanded', 'true');
				focusFirst(searchDrawer);
			};
			const closeSearch = () => {
				closeDrawer(searchDrawer, '-translate-y-full');
				searchBtn.setAttribute('aria-expanded', 'false');
				searchBtn.focus();
			};

			const openCart = () => {
				openDrawer(cartDrawer, 'translate-y-full');
				cartBtn.setAttribute('aria-expanded', 'true');
				focusFirst(cartDrawer);
			};
			const closeCart = () => {
				closeDrawer(cartDrawer, 'translate-y-full');
				cartBtn.setAttribute('aria-expanded', 'false');
				cartBtn.focus();
			};

			const openMenu = () => {
				openDrawer(menuDrawer, 'translate-x-full');
				menuBtn.setAttribute('aria-expanded', 'true');
				iconOpen.style.display = 'none';
				iconClose.style.display = '';
				focusFirst(menuDrawer);
			};
			const closeMenu = () => {
				closeDrawer(menuDrawer, 'translate-x-full');
				menuBtn.setAttribute('aria-expanded', 'false');
				iconOpen.style.display = '';
				iconClose.style.display = 'none';
				menuBtn.focus();
			};

			// ── Button triggers ───────────────────────────────────────────
			searchBtn.addEventListener('click', openSearch);
			cartBtn.addEventListener('click', openCart);
			menuBtn.addEventListener('click', () => {
				menuDrawer.classList.contains('open')
					? closeMenu()
					: openMenu();
			});

			// ── Close buttons ─────────────────────────────────────────────
			cartClose.addEventListener('click', closeCart);
			searchClose.addEventListener('click', closeSearch);
			menuClose.addEventListener('click', closeMenu);

			// ── Backdrop ──────────────────────────────────────────────────
			backdrop.addEventListener('click', () => {
				if (searchDrawer.classList.contains('open')) closeSearch();
				if (cartDrawer.classList.contains('open')) closeCart();
				if (menuDrawer.classList.contains('open')) closeMenu();
			});

			// ── Escape + Tab key handling ─────────────────────────────────
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape') {
					if (searchDrawer.classList.contains('open')) closeSearch();
					if (cartDrawer.classList.contains('open')) closeCart();
					if (menuDrawer.classList.contains('open')) closeMenu();
				}
				if (e.key === 'Tab') {
					if (searchDrawer.classList.contains('open')) trapFocus(e, searchDrawer);
					if (cartDrawer.classList.contains('open')) trapFocus(e, cartDrawer);
					if (menuDrawer.classList.contains('open')) trapFocus(e, menuDrawer);
				}
			});
		},
	};

	/* =========================================================================
        16. Mobile Cart Content
    ========================================================================= */
	ShopChop.MobileCart = {
		init() {
			const $content = $('.mobile-cart-content');
			const $header = $('.mobile-cart-header');
			if (!$content.length) return;

			const miniCart = createMiniCart({
				contentEl: $content,
				onCountUpdate(count) {
					$header.find('.count-number').text(count);
					$header
						.find('.cart-items-count')
						.html(Utils.itemsLabel(count));
					// Keep desktop badge in sync
					$('.cart-count-badge').text(count >= 0 ? count : '');
				},
			});

			// Reload on any cart mutation
			$(document.body).on(
				'added_to_cart wc_fragment_refresh updated_cart_totals',
				() => {
					miniCart.load();
				}
			);

			miniCart.load(); // Initial load
		},
	};

	/* =========================================================================
        17. Toast Notifications
    ========================================================================= */
	ShopChop.Toast = {
		show(message, type = 'error') {
			const existing = document.getElementById('shopchop-toast');
			if (existing) existing.remove();

			const toast = document.createElement('div');
			toast.id = 'shopchop-toast';
			toast.setAttribute('role', 'alert');
			toast.setAttribute('aria-live', 'assertive');
			toast.className = `shopchop-toast shopchop-toast--${type}`;

			const msg = document.createElement('span');
			msg.textContent = message;

			const closeBtn = document.createElement('button');
			closeBtn.className = 'shopchop-toast-close';
			closeBtn.setAttribute('aria-label', 'Dismiss notification');
			closeBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>`;

			toast.appendChild(msg);
			toast.appendChild(closeBtn);
			document.body.appendChild(toast);

			const dismiss = () => {
				toast.classList.remove('is-visible');
				toast.addEventListener('transitionend', () => toast.remove(), { once: true });
			};

			closeBtn.addEventListener('click', dismiss);

			// Trigger entrance
			requestAnimationFrame(() => toast.classList.add('is-visible'));

			// Auto-dismiss after 6s
			const timer = setTimeout(dismiss, 6000);

			// Escape key dismiss
			const onKeydown = (e) => {
				if (e.key === 'Escape') {
					clearTimeout(timer);
					dismiss();
					document.removeEventListener('keydown', onKeydown);
				}
			};
			document.addEventListener('keydown', onKeydown);
		},
	};

	/* =========================================================================
        18. Mobile Sub-Menu Accordion
    ========================================================================= */
	ShopChop.MobileSubMenu = {
		init() {
			const nav = document.getElementById('main-header-menu-mobile');
			if (!nav) return;

			nav.querySelectorAll('.menu-item-has-children').forEach((item) => {
				const link = item.querySelector(':scope > a');
				const subMenu = item.querySelector(':scope > .sub-menu');
				if (!link || !subMenu) return;

				// Inject toggle button next to the link
				const btn = document.createElement('button');
				btn.className = 'mobile-submenu-toggle';
				btn.setAttribute('aria-expanded', 'false');
				btn.setAttribute('aria-label', `Toggle ${link.textContent.trim()} sub-menu`);
				btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>`;
				item.appendChild(btn);

				btn.addEventListener('click', (e) => {
					e.preventDefault();
					const isOpen = subMenu.classList.contains('is-open');

					// Close sibling sub-menus only (not nested children of other branches)
					item.parentElement.querySelectorAll(':scope > .menu-item-has-children > .sub-menu.is-open').forEach((s) => {
						s.classList.remove('is-open');
					});
					item.parentElement.querySelectorAll(':scope > .menu-item-has-children > .mobile-submenu-toggle[aria-expanded="true"]').forEach((b) => {
						b.setAttribute('aria-expanded', 'false');
					});

					if (!isOpen) {
						subMenu.classList.add('is-open');
						btn.setAttribute('aria-expanded', 'true');
					}
				});
			});
		},
	};

	/* =========================================================================
        19. Login / Register Toggle
    ========================================================================= */
	ShopChop.AuthToggle = {
		init() {
			$(document).on('click', '.wc-toggle-heading', function () {
				const $target = $(`#${$(this).data('target')}`);
				const wasOpen = $target.hasClass('is-open');

				// Close all panels first
				$('.wc-toggle-form, .wc-toggle-heading').removeClass('is-open');

				// Re-open the clicked one if it was previously closed
				if (!wasOpen) {
					$target.addClass('is-open');
					$(this).addClass('is-open');
				}
			});
		},
	};

	/* =========================================================================
        Boot – initialise all modules on DOM ready
    ========================================================================= */
	$(function () {
		ShopChop.HeroCarousel.init();
		ShopChop.ProductsCarousel.init();
		ShopChop.TestimonialsCarousel.init();
		ShopChop.ProductGallery.init();
		ShopChop.CartButton.init();
		ShopChop.ReviewsPagination.init();
		ShopChop.ShopPagination.init();
		ShopChop.VariableProduct.init();
		ShopChop.PillSwatches.init();
		ShopChop.QuantityButtons.init();
		ShopChop.AccountMenu.init();
		ShopChop.CartAutoUpdate.init();
		ShopChop.PaymentMethods.init();
		ShopChop.Search.init();
		ShopChop.AccountDropdown.init();
		ShopChop.CartDropdown.init();
		ShopChop.MobileDrawers.init();
		ShopChop.MobileCart.init();
		ShopChop.MobileSubMenu.init();
		ShopChop.AuthToggle.init();
	});
})(jQuery);
