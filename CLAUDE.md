# CLAUDE.md — TheSplashShop Theme (ShopChop)

## Project Overview

WordPress WooCommerce theme for **The Splash Shop (TSS)** — a Malaysian e-commerce store selling pool supplies. Built on the `_tw` (Underscores + Tailwind) starter theme. Theme slug/prefix: **`shopchop`**.

Running locally via **Local by Flywheel** at `c:\Users\usoppii\Local Sites\webshop\app\public`.

---

## Tech Stack

| Layer       | Tool                                                           |
| ----------- | -------------------------------------------------------------- |
| CSS         | Tailwind CSS v4 via PostCSS                                    |
| JS bundler  | esbuild                                                        |
| PHP linting | PHPCS                                                          |
| JS linting  | ESLint + Prettier                                              |
| Fonts       | Bunny Fonts — DM Sans 300–800 + 400/500 italic (headings + body)|
| Carousel    | Swiper v12 (CDN)                                               |
| WC gallery  | WC core zoom + lightbox (`wc-product-gallery-*` supports); Swiper replaces the slider |
| Animation   | `@lottiefiles/dotlottie-web` is in package.json but not imported anywhere |

---

## Build Commands

```bash
npm run dev        # build once (Tailwind + esbuild)
npm run watch      # watch mode
npm run prod       # minified production build
npm run bundle     # prod + zip
npm run lint       # ESLint + Prettier check
npm run lint-fix   # auto-fix lint issues
```

**CSS source:** `tailwind.css` → compiled to `theme/style.css` (frontend) and `theme/style-editor.css` (block editor)
**JS source:** `javascript/script.js` + `javascript/block-editor.js` → `theme/js/*.min.js`

---

## File Structure

```
TheSplashShop-Tailwind-Woocommerce/
├── tailwind.css              # CSS entry point
├── tailwind/                 # Tailwind partials/config
├── javascript/               # JS source files
├── theme/                    # WordPress theme root (loaded by WP)
│   ├── style.css             # compiled CSS + theme header
│   ├── functions.php         # theme setup, enqueue, nav menus
│   ├── inc/                  # hooks/filters, one file per feature (load order in functions.php)
│   │   ├── core.php, login.php, whatsapp.php, pool-profile.php
│   │   ├── woocommerce/      # layout, product-display, checkout, reviews, orders, my-account,
│   │   │                     # search, mini-cart, stock-status, recently-viewed
│   │   └── template-tags.php # reusable template helpers
│   ├── assets/css/login.css  # wp-login.php styling (plain CSS, not Tailwind)
│   ├── template-parts/
│   │   ├── content/          # post/page content partials
│   │   ├── layout/           # header-content.php, footer-content.php
│   │   └── woocommerce/      # wishlist partials, homepage content
│   └── woocommerce/          # WooCommerce template overrides
│       ├── cart/
│       ├── checkout/
│       ├── myaccount/
│       ├── emails/
│       ├── loop/
│       ├── single-product/
│       └── ...
├── .claude/
│   └── settings.local.json   # Claude Code permissions (git add *, npm run *)
└── node_modules/
```

---

## WordPress Config

**Registered nav menus:**

- `menu-1` — Main Navigation Menu (used in header: desktop + mobile)
- `footer-1-menu` — Footer Menu #1 (via widget sidebar)
- `footer-2-menu` — Footer Menu #2 (via widget sidebar)
- `footer-3-menu` — Footer Menu #3 (via widget sidebar)

**Registered widget sidebars:**

- `footer-content-0` through `footer-content-3`

**WooCommerce theme supports:**

- `woocommerce`, `wc-product-gallery-zoom`, `wc-product-gallery-lightbox`
- Slider intentionally omitted (Swiper handles it)

---

## inc/ — Feature Map

Hooks and filters live in `theme/inc/`, one file per feature. The § numbers are the original section numbers, kept in the file banners.

| § | File | Topic | Key behavior |
| -- | ---- | ----- | ------------ |
| 1 | `core.php` | Core WP Hooks | pingback, comments, archive titles, excerpt |
| 2 | `woocommerce/layout.php` | WooCommerce Layout | removes WC default stylesheet, replaces `<div>` wrappers with `<main>`, utility bar wrapper |
| 3 | `woocommerce/product-display.php` | Product Display | custom price HTML with discount %, category link wrapper, product card structure (image/details/actions wrappers) |
| 4 | `woocommerce/product-display.php` | Variation Swatches | replaces `<select>` with pill buttons; hidden select kept for WC JS compat; AJAX threshold = 100 |
| 5 | `woocommerce/checkout.php` | Checkout & Address Fields | removes Select2, Malaysian address fields (no last_name, no address_2), reordered field priorities |
| 6 | `woocommerce/reviews.php` | Reviews & Ratings | custom SVG star row + numeric score (e.g. "4.0 / 5"), custom author/verified meta, date below body |
| 7 | `woocommerce/orders.php` | Orders | order number format `TSS-YYMMDD-NNNNN`, auto customer notes on cancel/complete |
| 8 | `woocommerce/my-account.php` | My Account | contextual `<h1>` titles, auth wrapper div, redirect `/login` + `/register` → WC myaccount |
| 9 | `woocommerce/layout.php` | Demo Store & Checkout | moves demo banner to `shopchop_demo_store_wrapper` hook; moves payment block to `shopchop_checkout_payment` hook |
| 10 | `login.php` | Authentication | generic error message to prevent username enumeration |
| 11 | `woocommerce/checkout.php` | Thank-You Page | "Next steps" section with WhatsApp + email links |
| 12 | `woocommerce/search.php` | AJAX Search | `wc_search_products` — 2-pass search (title/content/excerpt + meta/SKU), deduped, max 10 results; `wc_get_categories` — top-level cats for dropdown |
| 13 | `woocommerce/search.php` | Search Bar Shortcode | `[shopchop_search_bar context="default |
| 14 | `woocommerce/mini-cart.php` | Mini Cart AJAX | `shopchop_get_mini_cart`, `shopchop_remove_cart_item`, cart fragments for `.cart-count-badge` + `.cart-items-count` |
| 15 | `woocommerce/mini-cart.php` | Mini Cart Shortcodes | `[shopchop_mini_cart]`, `[shopchop_mobile_cart_icon_display]`, `[shopchop_mobile_cart_details_display]` |
| 16 | `woocommerce/stock-status.php` | Custom Stock Statuses | adds `pre_order` + `coming_soon`; removes `onbackorder`; blocks purchase for coming_soon; admin list badges |
| 17 | `woocommerce/recently-viewed.php` | Recently Viewed | cookie-based list on single product pages |
| 18 | `whatsapp.php` | WhatsApp Button | floating chat button on single product pages, `shopchop_get_whatsapp_number()` |
| 19 | `login.php` | Login Page Styling | enqueues `assets/css/login.css` on wp-login.php |

---

## Plugins Installed

| Plugin                    | Purpose                                                            |
| ------------------------- | ------------------------------------------------------------------ |
| WooCommerce               | Core shop engine                                                   |
| YITH WooCommerce Wishlist | Wishlist — theme has override templates in `theme/woocommerce/` |
| Elementor (free)          | Page builder for the homepage and content pages                    |
| The Splash Shop Settings  | Companion plugin, own repo: `wp-content/plugins/SplashShop-WC-Theme-Settings` (keep this folder name). Settings page, admin columns, pool profile admin tab, and 18 ShopChop Elementor widgets |

**Elementor widgets:** markup lives in the plugin (`widgets/*.php`), styling lives here (`tailwind/custom/components/_06-homepage.css`, `_00-shared.css`). Full-width homepage widgets share a "Section Style" control (background + top/bottom borders) via the plugin's `includes/trait-section-style.php`, styled by `.shopchop-section` modifiers. Static widgets are served from Elementor's element cache: after changing widget markup, clear it (Elementor → Tools → Clear Files & Data).

---

## Key Conventions

- **Prefix:** all functions, hooks, constants use `shopchop_` or `SHOPCHOP_`
- **Text domain:** `shopchop`
- **No Select2** — removed; native `<select>` styled via Tailwind
- **No WC default stylesheet** — theme ships its own via Tailwind
- **No WC product gallery slider** — Swiper handles it
- **Heading levels** restricted to H2/H3/H4 in block editor (Tailwind Typography compat)
- **Admin bar** suppressed site-wide (`show_admin_bar` → false)
- **Malaysia-specific** address field labels and placeholders (Jalan, postcode 5-digit, +60 phone)
- **Order prefix:** `TSS`

---

## AJAX Endpoints

| Action                        | Auth                           | Handler                         |
| ----------------------------- | ------------------------------ | ------------------------------- |
| `wc_search_products`        | nonce `wc_ajax_search_nonce` | `shopchop_search_products()`  |
| `wc_get_categories`         | nonce `wc_ajax_search_nonce` | `shopchop_search_get_cat()`   |
| `shopchop_get_mini_cart`    | nonce `wc_ajax_search_nonce` | `shopchop_get_mini_cart()`    |
| `shopchop_remove_cart_item` | nonce `wc_ajax_search_nonce` | `shopchop_remove_cart_item()` |

Nonce localized via `shopchopDynamicSearch.nonce` (JS object).

---

## Git

- Repo tracked at theme root
- Two contributors: `muhdyusuffrosli` (previous username owner), `usoppii` (current username owner)
- `.claude/settings.local.json` allows `git add *` and `npm run *` without prompt
- Two remotes: `origin` (`usoppii/TheSplashShop-Tailwind-Woocommerce`, fork) and `upstream` (`AquariusSPoolsDev/TheSplashShop-Tailwind-Woocommerce`, canonical). Local `main` tracks `upstream`.
- Commits do **not** reliably auto-push. This shell has no GitHub credentials, so push from VS Code's Source Control panel; check with `git fetch --all` and `git log upstream/main..main`. `upstream` is the source of truth; the `origin` fork may lag.
- Current version: **1.6.1**. Version lives in `tailwind/custom/file-header.css` (source), synced into `theme/style.css` + `theme/style-editor.css` on build — bump the source file, not the compiled ones.
