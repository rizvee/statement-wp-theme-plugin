# Current State

Updated: 2026-08-14

## Repository

- M0 repository foundation is complete.
- The M0 foundation baseline exists on `main`; the local PHP tooling checkpoint follows it.
- `origin` is configured as `https://github.com/rizvee/statement-wp-theme-plugin.git`; it has not been pushed.
- The initial audit found no existing source, instructions, secrets, generated artifacts, unrelated files, or prior scaffolding to preserve.
- M1 is complete: the standalone theme and core plugin skeletons are both version `0.1.0`.
- M2 is complete: `theme.json` v3 defines the palette, typography, spacing, and content-width primitives; focused base/layout CSS and a frontend asset boundary are active.
- M3 is complete: the sticky responsive header uses a centered custom-logo/site-name fallback, `primary` and `footer` menu locations, native mobile-navigation and search dialogs, conditional WooCommerce Account/Bag links, and interaction-only vanilla JavaScript.
- The restrained footer uses the approved brand message and configured WordPress menu; no contact, legal, newsletter, or social data is invented.
- Account and Bag omit themselves when WooCommerce or their configured pages are unavailable. No mini-cart, cart count, cart fragments, or theme-owned product behavior exists.
- M4 is complete and integrity-hardened in the core plugin: public non-hierarchical `statement_drop` terms attach to products through a controlled single-select admin field.
- Product metadata owns the canonical `UPCOMING`, `PRIVATE_ACCESS`, `LIVE`, `SOLD_OUT`, and `ARCHIVED` lifecycle plus an optional 80-character edition label.
- Release-state saves are same/forward-only. `SOLD_OUT` and `ARCHIVED` override WooCommerce purchasability even when stock is positive; variation purchasability inherits the canonical parent Statement release state, and the implementation never mutates stock or duplicates state on variations.
- Established Drop history is editable while `UPCOMING` and immutable through normal saves from `PRIVATE_ACCESS` onward; one first valid recovery assignment remains possible when a released product has no valid Drop.
- M5 is complete: the editorial `front-page.php` uses the static page featured image for its hero, retains an intentional no-image surface, and exposes native page content as the optional editorial zone.
- Statement Core now exposes a minimal read-only presentation API. Homepage Drop/product data is restricted to `LIVE`; `PRIVATE_ACCESS`, `UPCOMING`, `SOLD_OUT`, and `ARCHIVED` are excluded before rendering, including through canonical parent state for variations.
- One deterministic active Drop comes from the first eligible product in a bounded 24-product WooCommerce query; selected homepage pieces are bounded to four products from that Drop.
- The homepage adds no JavaScript, quick-add, cart interaction, fabricated catalog content, or archive product query.
- M6 is complete: the native WooCommerce Shop archive and native `statement_drop` taxonomy archives are constrained to canonical `LIVE` products at the main-query boundary before result counts and pagination are calculated.
- Existing WooCommerce meta constraints are preserved. Missing state plus `UPCOMING`, `PRIVATE_ACCESS`, `SOLD_OUT`, and `ARCHIVED` fail closed on normal public Shop/Drop queries.
- Home, Shop, and Drop reuse one presentation-only product card for image, name, canonical Drop name, and price. Catalog UI removes result count, ordering, and sidebar while retaining notices, loop semantics, and native pagination.
- No quick-add, catalog JavaScript, private-access enforcement, or terminal archive presentation was added.
- M7 is complete: ordinary direct product requests are available only for canonical `LIVE` products; other lifecycle states receive a real uncached 404, while a capable editor's explicit preview remains available.
- Crafted Add-to-Cart requests are independently validated against canonical parent release state. Only `LIVE` preserves WooCommerce's prior validation result; all other or unresolved states fail closed with one lifecycle-neutral notice.
- The focused product page delegates image gallery, price, excerpt, and simple/variable purchase mechanics to native WooCommerce APIs. It exposes only actual Drop and optional edition-label provenance through the read-only core API.
- Product CSS is conditional and responsive, with a mobile scroll-snap gallery and desktop gallery/purchase layout. No product JavaScript, custom variation UI, cart drawer, review/related-product UI, or stock mutation was added.
- M8 is complete: Bag remains a direct WooCommerce Cart link and shows a safe server-rendered quantity only when positive. No cart drawer, fragments, AJAX, subtotal preview, or new JavaScript exists.
- Restored and current cart lines are revalidated against canonical `LIVE`; variations inherit the parent release lifecycle. Stale non-LIVE or unresolved lines are removed or rejected with one generic lifecycle-neutral notice and without stock or metadata mutation.
- The classic WooCommerce Cart presentation retains native product data, quantity controls, removal URLs, form nonce, totals, and checkout route. Statement Cart omits coupon-entry and cross-sell presentation without changing stored coupon/cross-sell data.
- The Cart page uses conditional responsive CSS, a desktop-only sticky order summary, and restrained native empty-cart hooks.
- M9 is complete: one focused `checkout/form-checkout.php` override composes the normal classic WooCommerce Checkout into a responsive customer/order-summary layout while preserving native billing, shipping, order notes, order review, payment, terms/privacy, validation, nonce, and order-processing hooks.
- M8 Cart Integrity is the final checkout lifecycle gate. WooCommerce invokes it through `woocommerce_check_cart_items`; stale non-`LIVE` lines, including variations resolved through their canonical parent, are removed with one blocking lifecycle-neutral error before native order creation may proceed.
- Checkout coupon-entry presentation is omitted only on the normal cart-backed Checkout. The coupon engine, applied discounts, native login, express/pay hook compatibility, payment gateways, and shipping methods remain untouched.
- Checkout styling excludes order-pay and order-received contexts. No custom checkout fields, shipping/payment integration, order creation, or first-party checkout JavaScript was added.
- M11 is complete: terminal release lifecycle (`LIVE` -> `SOLD_OUT` -> `ARCHIVED`) is strictly forward-only and irreversible. Permanent commerce lock overrides positive WooCommerce stock; direct permalinks for `SOLD_OUT` and `ARCHIVED` remain permanently public (HTTP 200); active catalog queries include `LIVE` and `SOLD_OUT` with `LIVE` sorted first while excluding `ARCHIVED` items; fully historical Drops (`is_past_drop`) render `ARCHIVED` items; and structured data availability is filtered to `OutOfStock`. Core scarcity model (**Crafted. Limited. Never Restocked.**) is strictly preserved with zero waitlist or restock logic.
- M12 is complete: purchase provenance (`_statement_provenance_version`, `_statement_product_id_at_purchase`, etc.) is captured write-once at order line item creation (`woocommerce_checkout_create_order_line_item`). Historical provenance remains frozen across subsequent product edits, Drop name changes, or source deletion. An explicit boundary separates purchase provenance from certified collector ownership and certificate generation. Private access audit metadata coexists cleanly; a commercial completion helper (`Completion::is_commercially_completed`) evaluates `processing`/`completed` statuses; WooCommerce admin order screens display read-only Statement Provenance; customer order details and Thank You pages show status-aware confirmation banners and "Continue Exploring" navigation; and transactional emails render frozen provenance independently of marketing consent.
- Internal milestones do not trigger package-version changes before an approved release task.
- The next approved milestone is M13 — Authenticity Certificates & Collector Registry (or next milestone approved by user).

## Local environment

- Available: Git 2.45.1, Node.js 22.17.0, npm 11.15.0, ripgrep 15.1.0.
- Local lint runtime: PHP 8.3.33 CLI, x64 NTS, under ignored `.local-tools/php/`; this does not assert the production PHP selection.
- `scripts/php-lint.mjs` resolves `PHP_BIN`, then project-local PHP on Windows, then PHP on `PATH`.
- Unavailable: Composer, WP-CLI.
- WordPress, WooCommerce, PHP runtime, and hosting integration versions could not be discovered from this empty local repository.
- PHP syntax verification passes for all 44 first-party runtime PHP files.
- The narrow bootstrap smoke passes without WooCommerce present. Genuine WordPress/WooCommerce activation remains unverified because no local WordPress runtime exists.

## Deployment state

Per the project brief, `https://mystatement.store/` is the WordPress.com Atomic integration/production site and remains Coming Soon/private. This local task did not inspect or modify the live site.

## Verification

M1–M9 structural tests, release-domain, terminal and variable-product purchasability, historical Drop/admin integrity, public eligibility, homepage/catalog privacy, direct product access, Add-to-Cart validation, cart/final-checkout lifecycle reconciliation, Bag count, dependency-absence smoke, first-party PHP lint, and `node scripts/verify-foundation.mjs` pass with project-local PHP 8.3.33. Actual WordPress/WooCommerce product editing, taxonomy persistence/routing, populated catalog/product/cart/checkout behavior, Cart session restoration, Add-to-Cart requests, Woo checkout AJAX, gateways, shipping methods, and browser rendering remain unverified. The live WordPress Cart and Checkout pages' classic-flow configurations are also unverified. See `.ai/checks/m0-foundation.md`.
