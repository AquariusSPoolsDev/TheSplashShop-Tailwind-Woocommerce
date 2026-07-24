# CLAUDE.md — TheSplashShop Theme (ShopChop)

## Project Overview

WordPress WooCommerce theme for **The Splash Shop (TSS)** — a Malaysian e-commerce store selling pool supplies. Built on the `_tw` (Underscores + Tailwind) starter theme. Theme slug/prefix: **`shopchop`**.

Running locally via **Local by Flywheel** at `c:\Users\muham\Local Sites\lcl26\app\public`.

---

## Tech Stack

| Layer       | Tool                                                           |
| ----------- | -------------------------------------------------------------- |
| CSS         | Tailwind CSS v4 via PostCSS                                    |
| JS bundler  | esbuild                                                        |
| PHP linting | PHPCS                                                          |
| JS linting  | ESLint + Prettier                                              |
| Fonts       | Bunny Fonts — Manrope 300–800 (single font, headings + body)  |
| Carousel    | Swiper v12 (CDN)                                               |
| Lightbox    | GLightbox (CDN)                                                |
| Zoom        | medium-zoom (CDN, product page only)                           |
| Animation   | @lottiefiles/dotlottie-web                                     |
| WC gallery  | GLightbox replaces WC default lightbox; Swiper replaces slider |

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
│   ├── inc/
│   │   ├── template-functions.php   # ALL hooks/filters (16 sections)
│   │   └── template-tags.php        # reusable template helpers
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

## template-functions.php — Section Map

| § | Topic                     | Key behavior                                                                                                                                              |
| -- | ------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1  | Core WP Hooks             | pingback, comments, archive titles, excerpt                                                                                                               |
| 2  | WooCommerce Layout        | removes WC default stylesheet, replaces `<div>` wrappers with `<main>`, utility bar wrapper                                                           |
| 3  | Product Display           | custom price HTML with discount %, category link wrapper, product card structure (image/details/actions wrappers)                                         |
| 4  | Variation Swatches        | replaces `<select>` with pill buttons; hidden select kept for WC JS compat; AJAX threshold = 100                                                        |
| 5  | Checkout & Address Fields | removes Select2, Malaysian address fields (no last_name, no address_2), reordered field priorities                                                        |
| 6  | Reviews & Ratings         | custom SVG star row + numeric score (e.g. "4.0 / 5"), custom author/verified meta, date below body                                                        |
| 7  | Orders                    | order number format `TSS-YYMMDD-NNNNN`, auto customer notes on cancel/complete                                                                          |
| 8  | My Account                | contextual `<h1>` titles, auth wrapper div, redirect `/login` + `/register` → WC myaccount                                                         |
| 9  | Demo Store & Checkout     | moves demo banner to `shopchop_demo_store_wrapper` hook; moves payment block to `shopchop_checkout_payment` hook                                      |
| 10 | Authentication            | generic error message to prevent username enumeration                                                                                                     |
| 11 | Thank-You Page            | "What to do Next?" section with WhatsApp + email links                                                                                                    |
| 12 | AJAX Search               | `wc_search_products` — 2-pass search (title/content/excerpt + meta/SKU), deduped, max 10 results; `wc_get_categories` — top-level cats for dropdown |
| 13 | Search Bar Shortcode      | `[shopchop_search_bar context="default                                                                                                                    |
| 14 | Mini Cart AJAX            | `shopchop_get_mini_cart`, `shopchop_remove_cart_item`, cart fragments for `.cart-count-badge` + `.cart-items-count`                               |
| 15 | Mini Cart Shortcodes      | `[shopchop_mini_cart]`, `[shopchop_mobile_cart_icon_display]`, `[shopchop_mobile_cart_details_display]`                                             |
| 16 | Custom Stock Statuses     | adds `pre_order` + `coming_soon`; removes `onbackorder`; blocks purchase for coming_soon; admin list badges                                         |

---

## Plugins Installed

| Plugin                    | Purpose                                                            |
| ------------------------- | ------------------------------------------------------------------ |
| WooCommerce               | Core shop engine                                                   |
| YITH WooCommerce Wishlist | Wishlist — theme has override templates in `theme/woocommerce/` |

**Upcoming:** Custom Elementor widgets plugin (separate repo, separate Git). Will integrate with this theme via hooks/CSS. Plan to use multi-root VSCode workspace when plugin is ready.

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
- ~16 commits, active development as of July 2026
- `.claude/settings.local.json` allows `git add *` and `npm run *` without prompt
- Two remotes: `origin` (`usoppii/TheSplashShop-Tailwind-Woocommerce`, fork) and `upstream` (`AquariusSPoolsDev/TheSplashShop-Tailwind-Woocommerce`, canonical). Local `main` tracks `upstream`.
- Commits to `main` auto-push to `upstream` (confirmed via reflog, no manual `git push` needed). `origin` fork does NOT get this — needs manual authenticated push to stay in sync; no cached HTTPS credential or `gh auth` in this dev environment as of 2026-07-24.
- Current version: **1.0.2** (bumped from 1.0.1 in `9a33c89`). Version lives in `tailwind/custom/file-header.css` (source), synced into `theme/style.css` + `theme/style-editor.css` on build — bump the source file, not the compiled ones.
