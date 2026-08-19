# Current Priority — Production Convergence & Release Candidates

## SPRINT 09.1 — POST-DEPLOYMENT LIVE VISUAL ACCEPTANCE + CLIENT-READY PERFECTION PASS — COMPLETE (2026-08-19)

Status: complete (Theme Candidate 0.13.0-rc.19 + Core Plugin Candidate 0.13.0-rc.15 + Client Demo 0.2.7 + Child Theme 0.1.0 packaged and verified; ZERO live mutations executed)

- [x] Live Visual QA & Root Cause Analysis on WordPress.com Atomic (`https://mystatement.store/`): Inspected deployed Theme `0.13.0-rc.18` across desktop (1440px), tablet (768px), and mobile (375px) viewports using Chrome DevTools MCP.
- [x] Drop Page Colon Separator Parsing Fix (`taxonomy-statement_drop.php`): Updated regex to `/^Drop\s*(\d+)\s*[-—–:]\s*(.+)$/i` so that terms named with colons like `Drop 001: Monogram Study` correctly extract `001` and monolithic title `MONOGRAM STUDY` without redundant prefixes.
- [x] Single-Piece Luxury Form Cleanliness (`assets/css/product.css`): Enforced `display: none !important;` on `.statement-product form.cart .quantity`, `.statement-product .quantity`, `.statement-product .stock`, `.statement-product .woocommerce-variation-availability`, and `.statement-product .reset_variations` across both simple and variable product forms for quiet single-piece purchase flow.
- [x] Gallery Flexslider Overflow & Numbering Repair (`assets/css/product.css`): Styled `ol.flex-control-nav.flex-control-thumbs` with `list-style: none !important;` to eliminate `1. 2. 3. 4.` browser list markers; added `overflow: hidden; max-inline-size: 100%;` to `.statement-product__gallery`, `.woocommerce-product-gallery`, and `.flex-viewport` to eliminate horizontal document blowout (`scrollWidth: 4158px`) on mobile and tablet viewports; hid zoom trigger icon (`.woocommerce-product-gallery__trigger`).
- [x] Copy & Compliance Corrections (`page-contact.php`, `template-parts/product/details.php`): Removed unverified claims ("carbon-offset priority freight", "signature collector packaging with tracked express freight"); restricted Contact to verified communication channels (`info@mystatement.store`, `@statement.au`, generic enquiry copy); verified Body Size Guide framing and general fit disclaimer.
- [x] Verification Suite & Version Promotion: Updated test suites to Theme `0.13.0-rc.19`. All 120 PHP files linted clean (100%), all unit and contract tests passed, `verify-foundation.mjs` and `verify-git-runtime-tracking.mjs` passed 100%, `git diff --check` passed with 0 errors. Rebuilt deterministic packages in `dist/` with SHA-256 manifest.

## SPRINT 09 — LUXURY ART-DIRECTION REBUILD + PDP EXPERIENCE + TYPOGRAPHIC PAGES + GLOBAL PREMIUMNESS CONVERGENCE — COMPLETE (2026-08-19)

Status: complete (Theme Candidate 0.13.0-rc.18 + Core Plugin Candidate 0.13.0-rc.15 + Client Demo 0.2.7 + Child Theme 0.1.0 packaged and verified; ZERO live mutations executed)

- [x] Drop Page Complete Re-Art-Direction (`taxonomy-statement_drop.php` & `assets/css/catalog.css`): Re-architected into a printed luxury Release Document and Collection Register. Header metadata bar (`DROP / 001`, `CURRENT RELEASE`), monolithic display title (`MONOGRAM STUDY`), thin hairline rule, two-column overview (concise editorial narrative + specification sheet with `STATUS`, `EDITION`, `PIECES 02`), and numbered collection register (`01 PANELLED HOOD JACKET $275.00`, `02 MONOGRAM JACQUARD JACKET $310.00`) with hover row interactions and desktop thumbnail preview. Zero decorative hero images or generic WooCommerce product loops.
- [x] About Page Typographic Art Direction (`page-about.php` & `assets/css/layout.css`): Re-architected as a 100% text-only typographic narrative (zero images). Monolithic opening pull quote ("Clothing should be more than something you wear..."), asymmetric 12-column sections with section rails (`01 / PHILOSOPHY`, `02 / CRAFT`, `03 / EXCLUSIVITY`, `04 / RESPONSIBILITY`), comfortable line measures, natural punctuation, and concluding brand signature ("CRAFTED. LIMITED. NEVER RESTOCKED.").
- [x] Contact Page Luxury Concierge Rebuild (`page-contact.php` & `assets/css/layout.css`): Re-architected as a 100% text-only luxury concierge interface (zero images). Large correspondence header, oversized clickable email (`info@mystatement.store`), digital channels (`@statement.au`), and carbon-offset dispatch note with measured whitespace and fine hairlines.
- [x] Single Product Pages (PDP) High-Fashion Re-Art-Direction (`woocommerce/content-single-product.php`, `summary.php`, `details.php`, `product.css`, `product.js`): 65/35 editorial layout on desktop with seamless gallery sequence and sticky purchase column; horizontal swipe/snap gallery on mobile. Luxury S/M/L/XL size selector buttons synchronized with native selects, prominent `BODY SIZE GUIDE ↗` trigger, full-width 54px Add to Bag CTA, and hairline disclosure accordions (`PRODUCT DETAILS +`, `SIZE & FIT +`, `DISPATCH & CARE +`).
- [x] Drops Index Refinement (`page-drops.php` & `assets/css/home.css`): Elevated text-first release directory with sharp UI typography, numbered indices (`01`, `02`, `03`), clean `UPCOMING` badges, and row hover transitions.
- [x] Design Tokens & Typography Scale (`assets/css/base.css`, `assets/css/layout.css`): Restrained palette (`#faf9f6` warm white, `#0d0f12` near black, `#55575c` soft graphite, `#d8d4cc` border grey), modern grotesque type hierarchy, micro-metadata uppercase tracking (`.statement-meta-code`), and hairline dividers.
- [x] Automated Test Battery: Created `tests/visual-sprint-09.test.mjs`. All 120 PHP files linted clean (100%), all unit and contract test suites passed, `verify-foundation.mjs` and `verify-git-runtime-tracking.mjs` passed 100%, `git diff --check` passed with zero errors.
- [x] Release Packaging: Promoted Theme candidate to `0.13.0-rc.18`. Built `dist/statement-collector-theme-0.13.0-rc.18.zip` (SHA-256: `741aa561f361564c9b7bacdbce5ea5f6951bb4f8f4b6b96313226424be304706`) and verified manifest in `dist/manifest.json`.

## SPRINT 08.1 — ATOMIC RUNTIME ACCEPTANCE + ADMIN FREEDOM + DROP/PDP/ACCOUNT LIVE QA — COMPLETE (2026-08-19)

Status: complete (Theme Candidate 0.13.0-rc.17 + Core Plugin Candidate 0.13.0-rc.15 + Client Demo 0.2.7 + Child Theme 0.1.0 packaged and verified; ZERO destructive live mutations executed)

- [x] Live Component Version Verification: Verified `https://mystatement.store/` live versions; confirmed Theme `0.13.0-rc.16`, Core `0.13.0-rc.14`, WooCommerce active, currency AUD (`$`).
- [x] Product Edit Admin Freedom Live Verification: Tested in real authenticated `wp-admin` on `post.php` (Post 271 Panelled Hood Jacket and Post 213 Monogram Jacquard Jacket); confirmed `#post` has 0 nested forms, override fields have 0 required attributes, `document.getElementById('post').checkValidity()` returns `true`, clicking "Update" produces "Product updated. View Product" with zero validation blocking or override prompt popups, and release state remains intact (`LIVE`).
- [x] Drop Page Live Audit: Audited `/drops/` (H1 `DROPS`, `01`, `02`, `03` editorial list, `UPCOMING` badges, `VIEW RELEASE →` link, 0 overflow across 11 viewports 320px–1920px). Audited canonical Drop 001 taxonomy URL (`/drop/drop-001-monogram-study/`); detected PHP 8.4 TypeError on live (`PublicApi::is_past_drop( int $term_id )` receiving `WP_Term` object).
- [x] Proven Defect Remediations:
  - Polymorphic `PublicApi::is_past_drop( $term_or_id )` in Core (`src/PublicApi.php`) accepting `int|\WP_Term|object|mixed` term ID or object.
  - Safe term ID resolution in Theme (`taxonomy-statement_drop.php`) using `$drop->term_id ?? 0`.
  - Mobile Sticky Bar lifecycle in Theme (`assets/js/product.js`): removed initial width constraint on creation to allow responsive CSS `@media (max-width: 48rem)` and `IntersectionObserver` to govern visibility seamlessly across device rotation and resizing.
- [x] Monogram PDP & Panelled Hood PDP Live Visual QA: Audited both PDPs on live; verified 60/40 desktop layout, gallery images, price (`$310.00` and `$275.00`), provenance line, S/M/L/XL luxury variation buttons with two-way native select synchronization, and `addBtn` states.
- [x] Body Size Guide Live QA: Verified `BODY SIZE GUIDE` accessible dialog with CM table (S, M, L, XL), disclaimer, open/close button interactions, backdrop dismiss, and zero viewport overflow at 320px.
- [x] Mobile Sticky Add to Bag Bar Live QA: Verified `<div class="statement-mobile-sticky-bar">` appears when native CTA is scrolled out of viewport, shows correct piece price, and triggers WooCommerce Add to Bag.
- [x] Account / Login / Lost Password Live QA: Audited `/my-account/` and `/my-account/lost-password/`; confirmed `account.css` styling applied (3.25rem inputs, 52px computed height, full-width CTA buttons, monochrome notices, dashboard navigation tabs, orders empty state).
- [x] Cart + Checkout Regression: Verified Add to Bag for Panelled Hood XL, Cart line item rendering, subtotal/total calculation (`$275.00` + `$10.00` flat rate = `$285.00 AUD`), Proceed to Checkout navigation, guest checkout contact/shipping form presence.
- [x] Header / Nav / Footer Regression: Verified authentic transparent Statement logo, desktop nav, mobile drawer, Account link, Bag count sync, search modal, footer channels (`@statement.au`), and absence of Journal from public menus.
- [x] 16-Route Live Acceptance Matrix: Executed automated live storefront auditor (`node scripts/audit-live-storefront.mjs`); 15/16 routes passed HTTP 200/404; single defect on Drop 001 taxonomy resolved in local candidate.
- [x] Candidate Bump & Deterministic Packaging: Promoted Theme to `0.13.0-rc.17` and Core to `0.13.0-rc.15`. Built single-root ZIP distributions in `dist/` with SHA-256 manifest. All 120 PHP files linted clean (100%), all 43 PHP unit tests passed (100%), all Node test suites passed, `verify-foundation.mjs` and `verify-git-runtime-tracking.mjs` passed 100%.

## SPRINT 08 — ADMIN FREEDOM + CORE LIFECYCLE UX REPAIR + DROP PAGE + PDP + ACCOUNT/LOGIN PERFECTION — COMPLETE (2026-08-19)

Status: complete (Theme Candidate 0.13.0-rc.16 + Core Plugin Candidate 0.13.0-rc.14 + Client Demo 0.2.7 + Child Theme 0.1.0 packaged and verified; ZERO live mutations executed)

- [x] Product Editing Interference Decoupling: Repaired `LifecycleV2Admin.php` to remove nested `<form>` tag and eliminated HTML5 `required` attributes from override fields. Normal WooCommerce product saving (title, price, stock, description, attributes, variations) is 100% decoupled from lifecycle overrides.
- [x] Detached Override Submissions: Implemented dynamic detached DOM form builder in JavaScript to submit privileged overrides directly to `admin-post.php` without interacting with `#post`.
- [x] Drop Assignment Admin UX: Repaired `$drop_id` resolution and streamlined General Product Data fields in `Product/Admin.php`.
- [x] Drop 001 Page Rework (`taxonomy-statement_drop.php`): Luxury editorial release index, 2-piece side-by-side spread, `CURRENT RELEASE` eyebrow, `DROP 001` / `MONOGRAM STUDY` display typography, zero breadcrumbs, no fake dates or totals, responsive mobile stack.
- [x] Drops Index (`page-drops.php`): Formatted editorial numerals (`01`, `02`, `03`), `VIEW RELEASE →` CTA, `UPCOMING` badges for Drop 002 and Drop 003.
- [x] Single Product Page (PDP) & Variations: 60/40 desktop layout ratio (`1.5fr` / `1fr`), S/M/L/XL luxury variation buttons bidirectional-synced with WooCommerce native selects, Panelled Hood XL variation support (`STMT-CD-D001-PHJ-XL`).
- [x] Body Size Guide: "BODY SIZE GUIDE" dialog with CM table (S, M, L, XL) and verified disclaimer ("Body measurements are provided as a general fit guide. Garment measurements may vary by piece. Measurements in centimeters (CM).").
- [x] Mobile Sticky Add to Bag: IntersectionObserver-driven sticky Add to Bag bar on mobile viewports (< 768px) with price, CTA button, and safe-area inset padding.
- [x] Account & Login Flow: Created dedicated `assets/css/account.css` with centered authentication card, 3.25rem inputs, 3.5rem full-width CTA buttons, lost password styling, logged-in dashboard tabs, monochrome notices, and mobile stacked orders table.
- [x] Comprehensive Test Battery: Created `tests/php/test-admin-product-edit-lifecycle.php`, `tests/drop-pdp-ux.test.mjs`, `tests/account-ui.test.mjs`. All 120 PHP files linted clean, all 43 PHP test suites passed (100%), all Node test suites passed, `verify-foundation.mjs` and `verify-git-runtime-tracking.mjs` passed 100%.
- [x] Deterministic Packaging & Verification: Generated verified ZIP distributions in `dist/` with SHA-256 manifest: Theme `0.13.0-rc.16`, Core `0.13.0-rc.14`, Demo `0.2.7`, Child `0.1.0`.

## FINAL CONVERGENCE SPRINT — LIVE STOREFRONT ACCEPTANCE + MOBILE 2-PRODUCT GRID + BODY SIZE GUIDE + FOOTER SOCIALS + RELEASE PACKAGING — COMPLETE (2026-08-19)

Status: complete (Theme Candidate 0.13.0-rc.15 + Core Plugin Candidate 0.13.0-rc.13 + Client Demo 0.2.7 + Child Theme 0.1.0 packaged and verified; ZERO live mutations executed)

- [x] Live Frontend 16-Route Audit: Executed automated 16-route live storefront auditor against `https://mystatement.store/` (`16 PASSED, 0 DEFECTS OUT OF 16 ROUTES`) verifying HTTP 200, semantic titles, zero PHP fatals/notices, clean QA fixture isolation, and brand slogan consistency.
- [x] WordPress Admin Bar Sticky Header Offset: Added top offsets in `assets/css/header.css` (`32px` on desktop, `46px` on mobile) when `.admin-bar` is present on `body`, eliminating layout jump and header overlap for logged-in administrators.
- [x] Mobile 2-Column Side-by-Side Product Grid Polish: Updated `assets/css/product-card.css` with stacked title and price typography below 32rem (`@media (max-width: 32rem)`), enabling generous margins and zero text collision across mobile viewports (320px–430px). Updated `assets/css/home.css` and `assets/css/catalog.css` to render 2 side-by-side columns on mobile devices.
- [x] Size Guide Nomenclature & Disclaimers: Updated `template-parts/product/size-guide.php` with title `BODY SIZE GUIDE`, explicit explanatory disclaimer ("Body measurements are provided as a general fit guide. Garment measurements may vary by piece. Measurements in centimeters (CM)."), and standardized body measurement ranges for S, M, L, XL (`CHEST (CM)`, `WAIST (CM)`, `HEIGHT (CM)`).
- [x] Footer Social Channel Links: Added `Instagram @statement.au` (`https://instagram.com/statement.au`) and configurable `Facebook` channel links to `template-parts/footer/site-footer.php` with uppercase styling in `assets/css/footer.css`.
- [x] Version Synchronization & Hardening: Promoted Theme to `0.13.0-rc.15` and Client Demo to `0.2.7`. Verified Core `0.13.0-rc.13` and Child Theme `0.1.0`.
- [x] Comprehensive Test Battery: 120/120 PHP files linted clean (100%), 42/42 PHP unit test suites passed (100%), 22/22 Node test suites passed (149/149 subtests, 100%), `verify-foundation.mjs` and `verify-git-runtime-tracking.mjs` passed 100%.
- [x] Deterministic Packaging & Verification: Built single-root ZIP distribution packages in `dist/` with SHA-256 checksums and verified manifest: Theme `0.13.0-rc.15` (`statement-collector-theme-0.13.0-rc.15.zip`), Client Demo `0.2.7` (`statement-client-demo-0.2.7.zip`), Core `0.13.0-rc.13` (`statement-collector-core-0.13.0-rc.13.zip`), Child `0.1.0` (`statement-collector-child-0.1.0.zip`).

## VISUAL SPRINT 07 — NEW ASSET INGESTION + HERO REBUILD + IA SIMPLIFICATION + PRODUCT MEDIA CORRECTION + COMPLETE QA — COMPLETE (2026-08-19)

Status: complete (Theme Candidate 0.13.0-rc.14 + Core Plugin Candidate 0.13.0-rc.13 + Client Demo 0.2.6 + Child Theme 0.1.0 packaged and verified; ZERO live mutations executed)

- [x] Client New Asset Ingestion & Forensic Classification: Ingested all raw client assets from `new assets/`, extracted authentic transparent brand logo (`statement-logo.png`), normalized 3 wide 1920×1080 desktop campaign photographs, normalized 720×1280 mobile portrait video (`statement-hero-mobile-monogram.mp4`), normalized 5 Monogram Jacket WebP assets, and corrected 6 Panelled Hood Jacket WebP assets. Synchronized to both theme and demo plugin asset trees.
- [x] Authentic Statement Brand Logo & Header Integration: Upgraded `render_site_brand()` in `inc/navigation.php` to render `statement-logo.png` with graceful fallback and `custom_logo` support. Styled `.statement-brand-logo` in `assets/css/header.css` with responsive clamps across 320px–1920px viewports. Omitted Journal from desktop, mobile drawer, and footer navigation menus.
- [x] Cinematic Hero Slider Rebuild: Rebuilt `template-parts/home/hero.php` with wide 1920×1080 high-fashion campaign photography and 720×1280 mobile portrait video. Set viewport-aware hero height (`clamp(34rem, 78svh, 54rem)`) and composition-aware focal alignments. Upgraded `hero-slider.js` with full HTML5 video lifecycle management, `prefers-reduced-motion` compliance, and visibility change handling.
- [x] Strict 5-Part Homepage Hierarchy: Streamlined `front-page.php` into a strict 5-part luxury hierarchy (Site Header -> Hero Slider -> Drop 001 with 2 side-by-side product cards -> Editorial Drops Directory -> Minimal Footer). Eliminated separate "Current Pieces" heading and redundant product grids.
- [x] Editorial Drops Directory: Created `template-parts/home/drops-list.php` and upgraded `page-drops.php` with text-first editorial directory format listing Drop 001 (active link) and upcoming drops (Drop 002, Drop 003 with `UPCOMING` badges).
- [x] Text-Only About & Contact Pages: Rebuilt `page-about.php` as pure typography layout without images, with em-dashes removed and natural punctuation restored. Rebuilt `page-contact.php` as minimal text-only layout with confirmed email (`info@mystatement.store`), Instagram (`@statement.au`), and configurable Facebook channel.
- [x] Product Media & Metadata Remediation: Completely eradicated "black-monogram-jacket" defect from Panelled Hood Jacket metadata. Ingested normalized WebP product photography suites. Added size **XL** (S, M, L, XL) with SKU `STMT-CD-D001-PHJ-XL` across seeder and size guide.
- [x] PDP Conversion Polish & Noticeable Size Guide: Prominent `SIZE GUIDE ↗` trigger button in `template-parts/product/size-guide.php` with CM table (S, M, L, XL) and touch target >= 44px on mobile.
- [x] Automated Test Suites & Packaging: Created `tests/visual-sprint-07.test.mjs` (5/5 passed), verified all 120 PHP files with PHP lint, passed PHP unit tests, passed all Node test suites, packaged single-root ZIPs in `dist/` with SHA-256 manifest: Theme `0.13.0-rc.14`, Demo `0.2.6`, Core `0.13.0-rc.13`, Child `0.1.0`.
- [x] Documentation: Authored `docs/visual-sprint-07-release-notes.md`, `docs/statement-new-assets-sprint-07.md`, `docs/client-feedback-2026-08-18.md`, and updated `docs/full-site-acceptance-matrix.md`.

## VISUAL SPRINT 06.3 — POST-DEPLOYMENT LIVE ACCEPTANCE + FULL SITE VISUAL REPAIR + CONTENT/OWNERSHIP CONSISTENCY + COMMERCE QA — COMPLETE (2026-08-18)

Status: complete (Theme Candidate 0.13.0-rc.13 + Core Plugin Candidate 0.13.0-rc.13 + Client Demo 0.2.5 + Child Theme 0.1.0 packaged and verified; ZERO live mutations executed)

- [x] Live Frontend Acceptance Audit: Executed automated 16-route live storefront auditor against `https://mystatement.store/` (`16 PASSED, 0 DEFECTS OUT OF 16 ROUTES`). Captured Chrome DevTools visual snapshots across desktop and mobile viewports for Home, Shop, Drops, Drop 001, Monogram PDP, Panelled Hood PDP, Archive, About, Contact, Journal, Cart, Checkout, My Account, and Search.
- [x] Shop 2-Product Centered Editorial Composition: Implemented `:has(> :nth-child(2):last-child)` and `.columns-2` rules in `catalog.css` ensuring 2-product catalogs format as a centered, high-impact fashion editorial spread (`max-width: 58rem; margin: 0 auto;`) rather than stretching or clinging to the left edge of a 3/4-column track.
- [x] Breadcrumb Suppression by Default: Suppressed raw WooCommerce breadcrumbs by default on Shop and PDP pages across both PHP wrapper hooks (`remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 )` in `inc/woocommerce.php`) and theme CSS (`display: none;`).
- [x] PDP Image Zoom Refinement: Upgraded `.woocommerce-product-gallery__trigger` with minimal circular glass badge styling on desktop and suppressed it on mobile touch devices (`@media (max-width: 48rem)`) to protect native swipe interactions.
- [x] Product Card Framing: Set `object-position: center 20%` on fashion product cards to ensure proper framing of full-body studio and campaign photography.
- [x] Notice Token Standardization: Replaced raw hex codes in `woo-blocks.css` with canonical theme design tokens (`--wp--preset--color--warm-white`, `--wp--preset--color--near-black`, `--wp--preset--color--border-grey`).
- [x] Journal Seeder Cleanup & Archive Routing: Cleaned up Journal page seeding in `DemoSeederService.php` to prevent assigning placeholder logo thumbnails, and ensured `page_for_posts` directs `/journal/` to the canonical `index.php` editorial grid.
- [x] Full Verification Regression: 119 PHP files linted clean (0 errors), 42/42 PHP test suites passed (100%), 20/20 Node test suites passed (144/144 subtests), `verify-foundation.mjs` and `verify-git-runtime-tracking.mjs` passed 100%.
- [x] Release Candidate Packaging: Packaged Theme `0.13.0-rc.13` (SHA-256: `a24560514cefeefcd3abd7cb2fa140be28444897bfd2211df37d040b3a24e444`), Core `0.13.0-rc.13`, Demo `0.2.5` (SHA-256: `d159166cdb35c6906b6a46dc14b3f5ed89528725fabbe382f19a9ca1d01a025d`), Child `0.1.0` in `dist/`.
- [x] Documentation: Authored `docs/visual-sprint-06-3-post-deployment-acceptance.md` and updated `docs/full-site-acceptance-matrix.md`.

## VISUAL SPRINT 06.2 — FULL-SITE ACCEPTANCE QA + EVERY PAGE / EVERY DEVICE AUDIT + VISUAL PERFECTION + WOO FLOW HARDENING — COMPLETE (2026-08-18)

Status: complete (Theme Candidate 0.13.0-rc.12 + Core Plugin Candidate 0.13.0-rc.13 + Client Demo 0.2.4 + Child Theme 0.1.0 packaged and verified; ZERO live mutations executed)

- [x] Full-Site Acceptance Audit Harness: Built `scripts/audit-live-storefront.mjs` verifying all 16 canonical storefront routes across HTTP status, document title, primary H1, fatal/notice absence, QA fixture isolation, and metadata duplication.
- [x] Shop Layout & Card Proportions Root Cause Fix: Resolved root causes in `catalog.css`, `product-card.css`, and `inc/woocommerce.php`. Styled `.statement-piece` luxury cards with 4:5 image ratio, uppercase titles, and typography tokens. Fixed `.woocommerce ul.products` and `li.statement-catalog__item` grid layout to eliminate browser list bullets and single-column collapse.
- [x] PDP Duplicate Metadata Elimination: Fixed `template-parts/product/summary.php` provenance logic to display only the edition label when present (fallback to Drop name only when no edition label exists), eliminating duplicated Drop titles.
- [x] Search Results & Journal Template Alignment: Upgraded `index.php` to detect `is_search()`, dynamic search query titles (`RESULTS FOR "[QUERY]"`), and integrated fixture isolation via `Visibility::is_fixture_product()`. Added design token rules in `assets/css/layout.css`.
- [x] WooCommerce Wrapper Action Safety: Guarded action removals in `inc/woocommerce.php` inside `setup_woocommerce()` on `after_setup_theme` with `function_exists( 'remove_action' )`.
- [x] Full Device Viewport & Responsive Verification: Verified all 16 routes at Desktop (1440px/1920px), Laptop (1024px), Tablet (768px), and Mobile (375px/320px) without horizontal overflow.
- [x] Full Verification Regression: 119 PHP files linted clean (0 errors), 42/42 PHP test suites passed (100%), 20/20 Node test suites passed (144/144 subtests), `verify-foundation.mjs` and `verify-git-runtime-tracking.mjs` passed 100%.
- [x] Release Candidate Packaging: Packaged and verified Theme `0.13.0-rc.12` (SHA-256: `05edcbd3700b827c7c56af9a37578354cd6d316ddf34c5d82ca61624b65ff1e7`), Core `0.13.0-rc.13`, Demo `0.2.4`, Child `0.1.0` in `dist/`.
- [x] Documentation: Authored `docs/full-site-acceptance-matrix.md` and `docs/visual-sprint-06-2-report.md`.

## VISUAL SPRINT 06.1 — CLIENT-ASSET REPLACEMENT + IMAGE-FIRST HOMEPAGE + STOREFRONT PERFECTION — COMPLETE (2026-08-18)

Status: complete (Theme Candidate 0.13.0-rc.11 + Core Plugin Candidate 0.13.0-rc.13 + Client Demo 0.2.4 + Child Theme 0.1.0 packaged and verified; ZERO live mutations executed)

- [x] Client Drive Asset Ingestion: Ingested and optimized 18 high-resolution client drive photographs (`.local-assets/client-drive/`), synchronized to `tools/statement-client-demo/assets/images/` and `wp-content/themes/statement-collector-theme/assets/images/`.
- [x] Client Drive Documentation & Script: Authored `docs/client-drive-asset-map.md`, `docs/client-drive-asset-download-blocker.md`, and asset download/optimization scripts.
- [x] Client Demo Seeder Upgraded (0.2.4): Registered new asset keys (`monogram_side`, `hood_side`), updated product gallery sequences, implemented `apply_new_client_media_set()` in `DemoSeederService.php`, and added "Apply New Client Media Set" admin button in `tools/statement-client-demo/src/AdminPage.php`.
- [x] Image-First Homepage Hero Slider: Redesigned hero slider defaults in `template-parts/home/hero.php` and Customizer to 5 curated image-first client slides (`statement-hero-slide-monogram-01.jpg`, `statement-panelled-hood-jacket-front.jpg`, `statement-monogram-jacket-front.jpg`, `statement-hero-slide-hood-01.jpg`, `statement-hero-slide-monogram-02.jpg`) without prominent overlay copy by default. Added `.statement-hero-slider--image-first` subtle gradient overlay in `assets/css/home.css`.
- [x] Default Disabled Email Capture: Updated `front-page.php` and `inc/customizer.php` (`statement_enable_email_capture`, `statement_home_show_access_capture`) to default to disabled (`false`) while strictly preserving M10 Private Access architecture.
- [x] Drops Index Polish: Updated `page-drops.php` hero to feature client jacket photography (`statement-monogram-jacket-front.jpg`).
- [x] Product Gallery & Mobile UX: Refined WooCommerce native gallery styling in `assets/css/product.css` with tokenized variables and mobile swipe responsiveness.
- [x] Verification Suite: All 119 PHP files linted clean (0 errors), all 42 PHP tests passed (100%), all Node milestone suites (M1–M19, M13 packaging) passed, `verify-foundation.mjs` and `verify-git-runtime-tracking.mjs` passed 100%.
- [x] Release Candidate Packaging: Generated and verified single-root ZIPs in `dist/` with SHA-256 manifest: Theme `0.13.0-rc.11`, Demo `0.2.4`, Core `0.13.0-rc.13`, Child `0.1.0`.
- [x] Documentation: Authored `docs/visual-sprint-06-1-client-convergence.md`.

## SPRINT 05.1 ACCEPTANCE HARDENING & SPRINT 06 PRODUCTION CONVERGENCE — COMPLETE (2026-08-18)

Status: complete (Theme Candidate 0.13.0-rc.10 + Core Plugin Candidate 0.13.0-rc.13 + Client Demo 0.2.3 + Child Theme 0.1.0 ready for manual deployment; ZERO live mutations executed)

- [x] Critical QA Fixture Precedence: Hardened `Visibility::is_fixture_product()` so `_statement_fixture = '1'`, `TEST-*` SKUs, and `TEST —` titles strictly override `_statement_client_demo = 1`.
- [x] Public Fixture Isolation: Excluded QA fixtures from `PublicApi::get_drop_state()` so drop lifecycle resolution is never contaminated by test products.
- [x] Product 213 Deterministic Ownership Classifier: Implemented `OwnershipClassifier::classify()` returning `QA_FIXTURE`, `CLIENT_DEMO`, `PRODUCTION`, `CONFLICT`, or `UNKNOWN`. Added "Ownership Diagnostics" table and safe repair action in `tools/statement-client-demo/src/AdminPage.php`.
- [x] Core HPOS Compatibility Declaration: Declared `custom_order_tables` compatibility exclusively in Core plugin (`statement-collector-core.php`) on `before_woocommerce_init` via `FeaturesUtil::declare_compatibility()`. Audited order storage to confirm zero `shop_order` postmeta bypasses.
- [x] Remove Theme HPOS Declaration & Streamline Boot: Removed theme-side HPOS declaration in `inc/compatibility/woocommerce.php`. Streamlined `inc/setup.php` so WooCommerce presentation support initializes directly.
- [x] Elementor Compatibility & Homepage Render Modes: Replaced hard front page template lock with `statement_front_page_renderer` Customizer switch (`statement` default vs `content` for page builders). Added `statement_theme_register_elementor_locations` filter and helper methods in `inc/compatibility/elementor.php`.
- [x] Page Builder Safe Templates: Created `template-canvas.php` and `template-full-width.php`.
- [x] Options Import & Meta Security: Audited `inc/admin/options-export.php` and `inc/page-meta.php` for strict nonces, capability checks, and allow-list sanitization.
- [x] Full Verification Suite: Authored `tests/m19-acceptance-hardening.test.mjs`, `tests/php/test-hpos-compatibility.php`, `tests/php/test-theme-security-and-extensibility.php`. All 42 PHP tests and 19 Node milestone test suites pass 100% clean.
- [x] Production Candidate Packaging: Packaged Theme `0.13.0-rc.10`, Core `0.13.0-rc.13`, Demo `0.2.3`, and Child `0.1.0` with SHA-256 manifest in `dist/`.
- [x] Documentation & Runbooks: Authored `docs/sprint-05-acceptance-audit.md` and `docs/sprint-06-deployment-runbook.md`.

## VISUAL SPRINT 05 — EXTENSIBILITY & PREMIUM FINISH MEGA SPRINT — COMPLETE (2026-08-18)

Status: complete (Theme Framework + Plugin Compatibility + Design Perfection + Setup Experience + Woo Hardening + QA Isolation + Responsive Polish + Child-Theme Support + Full Release Prep)

- [x] Phase 0: Baseline state & clean git checkout
- [x] Phase 1: Research premium theme architecture (`docs/premium-theme-architecture-study.md`)
- [x] Phase 2: Critical Public QA Leak Fix in Core PublicApi & Catalog Visibility (`0.13.0-rc.12` candidate)
- [x] Phase 3: Product 213 deterministic ownership verification & test
- [x] Phase 4: Theme architecture modular refactor (`inc/setup.php`, `assets.php`, `hooks.php`, `compatibility/`, `admin/`)
- [x] Phase 5: Public extension API (`statement_theme_*` hooks/filters) & `docs/theme-hooks-reference.md`
- [x] Phase 6: Child theme starter package (`tools/statement-collector-child/`) & `docs/child-theme-customization-guide.md`
- [x] Phase 7 & 8: Authoritative Design Tokens & Global Customizer Design Options System
- [x] Phase 9: Design Settings Export / Import JSON with schema & nonce validation
- [x] Phase 10: Lightweight Page-Level Design Meta Overrides (layout, header, footer, title)
- [x] Phase 11: Elementor Theme Location compatibility (`header`, `footer`, `single`, `archive`) & page templates
- [x] Phase 12: Gutenberg / Block Editor supports, theme.json palette, and editorial block patterns
- [x] Phase 13, 14, 15: WooCommerce 11.0.1 template audit, Woo Blocks styling, and HPOS audit
- [x] Phase 16, 17, 18, 19, 20: SEO, Forms, Cache layers, CSS & JS scoping/namespacing audit
- [x] Phase 21 & 22: Statement Admin Setup / Health Screen (`Appearance -> Statement`) with safe setup actions
- [x] Phase 23-42: Visual Perfection across all 14 screenshot issues (Mobile shop titles, card density, drop typography, desktop spacing, PDP gallery & summary, Add-to-Bag black CTA, Woo notices, breadcrumbs, shop title, journal editorial cards, archive historical presentation, mobile drawer typography, footer refinement, checkout polish)
- [x] Phase 43-50: Responsive Matrix (320px–1920px), Tablet QA, Admin Bar fix, Accessibility, Performance & Image optimization
- [x] Phase 51-55: Admin control, Core/Theme boundary, `docs/plugin-compatibility-matrix.md`, `docs/theme-customization-guide.md`, `docs/theme-setup-guide.md`
- [x] Phase 56-60: Visual preview inspection, zero public test content leak automated tests
- [x] Phase 61-68: Version bumps (Theme `0.13.0-rc.10`, Core `0.13.0-rc.13`, Demo `0.2.3`, Child `0.1.0`), testing, packaging, multi-step commits & push
- [x] Phase 69: Atomic mutation verification (strictly zero live mutations)

## VISUAL SPRINT 04 — COMPLETE (2026-08-17)

Status: complete (Theme Candidate 0.13.0-rc.8 + Core Plugin Candidate 0.13.0-rc.11 + Client Demo 0.2.1 + Fixtures 0.3.3 ready for deployment; ZERO live mutations executed)

- [x] Record & Treat Client Demo 0.2.0 as Failed Runtime Candidate:
  - Runtime Fatal identified on operator run: `Undefined constant Statement\Collector\Core\Product\Metadata::EDITION_KEY` in `DemoSeederService.php`.
  - Addressed suspected Product 213 duplicate ID collision risk with strict 5-point isolation.
- [x] Phase 1 — Client Demo Forensic Root Cause & API Contract Sweep:
  - Audited all Core API/constant references in `tools/statement-client-demo/`.
  - Fixed `Metadata::EDITION_KEY` -> used canonical `Metadata::set_edition_label()` and `Metadata::EDITION_LABEL_KEY`.
  - Created and passed reusable Core API contract test (`test-client-demo-contracts.php`, 30 assertions).
- [x] Phase 2 & 3 — Strict Demo Ownership & ID 213 Collision Recovery:
  - Enforced 5-point ownership requirement: `_statement_client_demo = 1` AND SKU in `STMT-CD-` namespace AND `_statement_fixture != 1` AND SKU not `TEST-` AND Name not `TEST —`.
  - QA ownership strictly supersedes Demo ownership; reject adoption by slug alone, title alone, or old numeric ID.
  - Implemented deterministic collision recovery ensuring demo product 1 != demo product 2 and zero QA fixture collision.
- [x] Phase 4 & 5 & 6 — Pre-Mutation Safety, Idempotent Seeding & Version Bump (0.2.1):
  - Added preflight check reporting target drop, SKU, existing ID, ownership, collision state, and media status; aborts before mutation on unsafe states with `Throwable` handling.
  - Re-seeded Monogram Jacquard Jacket and Panelled Hood Jacket idempotently with canonical edition labels and AUD pricing.
  - Bumped Client Demo to `0.2.1`, packaged `dist/statement-client-demo-0.2.1.zip`, and updated manifest.
- [x] Phase 7 & 8 — WhatsApp Asset Audit & Product Image Hierarchy Rebuild:
  - Audited real WhatsApp image archive (`Photo downloaded from whatsapp`) and generated `docs/statement-asset-map.md`.
  - Rebuilt strict product image hierarchy (Primary Front -> Alternate/Silhouette -> Back -> Macro Weave -> Embroidery/Detail -> Lifestyle -> Packaging) with 4:5 cards.
- [x] Phase 9 & 10 & 11 — Typography Redesign, Wordmark Fix & Navigation Rebuild:
  - Replaced rejected font treatment with clean contemporary fashion sans (`Inter`, `-apple-system`, `sans-serif`) for UI/products/body and restrained editorial serif for accents; self-contained, zero external CDN requests.
  - Fixed header logo: eliminated white rectangle raster; defaulted to centered letter-spaced typographical `STATEMENT` wordmark.
  - Rebuilt desktop header (Left: SHOP, DROPS, ARCHIVE; Center: STATEMENT; Right: ABOUT, SEARCH, ACCOUNT, BAG) and mobile drawer.
- [x] Phase 12, 13, 14, 15, 16, 17 — Minimal Homepage Contract, Hero Slider, Drop, Featured Products, Email:
  - Strictly enforced 6-part homepage layout: Header, Hero Slider, Current Drop, Featured Products (max 2), Email Capture, Minimal Footer.
  - Implemented zero-dependency Vanilla JS/CSS hero slider with Customizer admin controls (4 slides, touch/swipe, keyboard, pause on hover/hidden, reduced motion, static fallback).
  - Kept off-home sections (Lookbook, Brand Object, Principle) on dedicated pages.
- [x] Phase 18, 19, 20, 21, 22 — About, Contact, Journal, Drops, Shop Pages:
  - Created `/about/` page template (`page-about.php`) with safe brand language ("Crafted. Not Mass Made.", Form, Material, Insignia, Object) using real assets.
  - Created `/contact/` page template (`page-contact.php`) with dynamic admin email, Instagram `@statement.au`, `mystatement.store`.
  - Ensured `/drops/` resolves active Drop 001, and `/shop/` renders fashion collection grid excluding QA fixtures.
- [x] Phase 23, 24, 25, 26, 27, 28, 29, 30 — PDP Experience, Size Guide, Light-First System, Microinteractions, Responsive:
  - Refined PDP desktop gallery / sticky summary and mobile swipe.
  - Dynamic Size Guide modal without fabricated measurements.
  - Light-first visual palette (gallery ivory / warm white `#FBFBFA`, ink typography `#111111`).
  - Responsive verification across 320px–1600px+ viewports.
- [x] Phase 39, 40, 41, 42, 43, 44, 45, 46 — Theme Bump (0.13.0-rc.8), Full Test Suite & Packaging:
  - Bumped Theme to `0.13.0-rc.8` (Core remains `0.13.0-rc.11`).
  - Ran all 18 Node test files (136/136 tests pass), 38 PHP test files (100% pass), verify-foundation, verify-git-runtime-tracking, security scans.
  - Packaged `dist/statement-collector-theme-0.13.0-rc.8.zip`, `dist/statement-collector-core-0.13.0-rc.11.zip`, and `dist/statement-client-demo-0.2.1.zip`.
- [x] Phase 47, 48, 49 — Final Documentation, Logical Commits & GitHub Push:
  - Authored `docs/visual-sprint-04-release-notes.md`, updated `current-state.md` and `TASKS.md`.
  - Pushed commits to `origin/main` (local SHA == remote SHA). Zero Atomic mutations.

## VISUAL SPRINT 03.1 — CORE RC.11 RUNTIME HARDENING & PRIVILEGED LIFECYCLE REPAIR (2026-08-17)

Status: complete (Theme Candidate 0.13.0-rc.7 + Core Plugin Candidate 0.13.0-rc.11 + Client Demo 0.2.0 + Fixtures 0.3.3 ready for deployment; ZERO live mutations executed)

- [x] Candidate RC.10 Defect Resolution & Formal Rejection: Treated Core `0.13.0-rc.10` as a failed release candidate due to RateLimiter signature mismatch, plaintext email fallback, and uncontrolled lifecycle setters.
- [x] RateLimiter & Crypto API Alignment: Adapted `SignupService.php` to canonical `RateLimiter::is_allowed()` / `RateLimiter::record_attempt()` (5 parameters). Replaced generic salt with dedicated Statement identity and rate limit HMAC keys (`Crypto::hash_email()`, `Crypto::hash_ip()`).
- [x] Fail-Closed Security & Storage Hardening: Removed giant plaintext option storage; replaced with durable `statement_consent_events` table supporting Mode A (Private Access session grant), Mode B (Live Drop encrypted consent), and Mode C (Generic VIP encrypted consent). Configured fail-closed behavior on uninitialized vault / crypto unavailable.
- [x] Privileged Lifecycle Override Service: Created `LifecycleOverrideService.php` enforcing mandatory preconditions (stock > 0 for LIVE/PRIVATE_ACCESS, future `DropConfig` with valid close timestamp, mandatory non-empty reason, canonicalization of variations to parent owner). Public `Metadata::set_release_state()` remains strictly forward-only. Verified database persistence before logging structured audit events.
- [x] Context-Aware Lifecycle Admin Meta Box: Hardened `LifecycleV2Admin.php` with state-specific allowed actions, warning banners for archived items, and secure delegation to `LifecycleOverrideService`.
- [x] Public Catalog QA Isolation: Hardened `Visibility::filter_public_catalog_posts_clauses` to query `_statement_fixture = '1'` and `_sku LIKE 'TEST-%'` with title/slug fallbacks.
- [x] Client Demo Collision Prevention Behavior Test: Created `tests/php/test-client-demo-collision.php` (13 assertions) proving QA product 213 is protected from adoption or mutation.
- [x] Comprehensive PHP Behavioral Suites: Authored and executed `test-marketing-signup.php` (41 assertions), `test-lifecycle-override.php` (34 assertions), and `test-client-demo-collision.php` (13 assertions).
- [x] Version Bump & Master Packaging: Promoted Core to `0.13.0-rc.11`, generated verified ZIP packages and master `dist/manifest.json`.

## CLIENT VISUAL DELIVERY — VISUAL SPRINT 03 / OVERNIGHT MEGA BUILD (2026-08-17)

Status: complete (Theme Candidate 0.13.0-rc.7 + Core Plugin 0.13.0-rc.10 [SUPERSEDED by rc.11] + Client Demo 0.2.0)

- [x] Homepage Release Contract Enforced: Strict 6-part layout locked (Header -> Current Offer/Hero -> Active Drop -> Featured 2 Pieces -> Email Capture -> Minimal Footer). Off-home content (Lookbook, Brand Object, Principle) migrated to dedicated journal templates.
- [x] Typographical Brand Wordmark: Replaced cramped logo markup with elegant, letter-spaced `STATEMENT` typography in both desktop and mobile headers.
- [x] Responsive Focal Positioning & Sizing: Replaced hardcoded hero crops with responsive focal alignment (`object-position: center top / 76svh`), eliminated mobile horizontal scrollbars, and enforced 320px responsive container boundaries.
- [x] Public Catalog Isolation: Excluded QA fixtures (`TEST-` products and `test-` drops) from all public storefront queries (`Visibility::filter_public_catalog_posts_clauses`) while preserving direct lookup capabilities.
- [x] Drops Page Resolution: Repaired `/drops/` (`page-drops.php`) with robust `PublicApi::get_current_drop()` queries and past drop history presentation.
- [x] Production Email Signup Engine (Mode A / B / C): Built `SignupService.php` with POST+303 PRG, CSRF nonces, IP rate limiting, SecretVault encryption, and automated Private Access session grant.
- [x] Admin Inventory Lifecycle v2 Controls: Implemented `LifecycleV2Admin.php` allowing privileged overrides (Reopen SOLD_OUT, Reopen ARCHIVED, Set PRIVATE_ACCESS) with mandatory confirmation notes and structured audit logs.
- [x] Client Demo Ownership & Deterministic SKUs: Built `statement-client-demo` v0.2.0 with strict `_statement_client_demo = 1` ownership checking, deterministic SKUs (`STMT-CD-D001-MJ`, `STMT-CD-D001-PHJ`), collision repair against Product 213, and admin UI.
- [x] Theme Asset Optimization: Removed duplicate product media from Theme package to reduce ZIP to ~548KB while delegating high-res demo product media injection to Client Demo tool.
- [x] Full Verification & Testing: 136/136 test suites passing (100% green), 97 PHP files linted cleanly, packaging verifier passing with zero errors.

## CLIENT VISUAL DELIVERY — VISUAL SPRINT 02 COMPLETE (2026-08-16)

Status: complete (Theme Candidate 0.13.0-rc.6 + Client Demo Plugin 0.1.0 ready for deployment)

- [x] Real WhatsApp Asset Audit & Optimization: Audited 22 incoming assets into `docs/statement-asset-manifest.md` and prepared 18 curated derivative images.
- [x] Visual Reference Analysis: Analyzed Maroo Clo and Mertra high-fashion visual patterns in `docs/visual-reference-analysis.md`.
- [x] Statement Client Demo Plugin v0.1.0: Built idempotent, manifest-driven demo content tool (`tools/statement-client-demo/`) creating Drop 001, 2 variable products (AUD 295 & AUD 275) with S/M/L variations, full media attachments, and safe rollback state.
- [x] Front-Page Template Protection: Added priority 9999 template_include hook in `inc/setup.php` to permanently secure `/` theme ownership against Elementor canvas hijacking.
- [x] Homepage Editorial Extensions: Added high-fashion Lookbook magazine grid (`template-parts/home/lookbook.php`) and Brand Object section (`template-parts/home/brand-object.php`).
- [x] Dedicated Drops Index Page: Created `page-drops.php` rendering `/drops/` with active drop feature card and past drop archives.
- [x] Accessible Size Guide Modal: Implemented `template-parts/product/size-guide.php` with CM measurements table (Chest, Length, Sleeve) and integrated into PDP summary.
- [x] Integration Fixture Isolation: Excluded `TEST-` products and `test-` drops from public homepage queries.
- [x] Brand Principle Standardization: Fully standardized all user-facing branding to "CRAFTED. NOT MASS MADE." (supporting line: "Produced with intention, not volume.").
- [x] Version Bump & Packaging: Promoted theme to `0.13.0-rc.6`, packaged `statement-collector-theme-0.13.0-rc.6.zip`, `statement-collector-core-0.13.0-rc.9.zip`, `statement-client-demo-0.1.0.zip`, and updated `dist/manifest.json`.
- [x] Inventory Lifecycle v2 Plan: Authored `docs/inventory-lifecycle-v2-plan.md` documenting transition from strict v1 terminal model to v2 demand-driven model.
- [x] Missing Assets Production Document: Authored `docs/missing-visual-assets.md` with dimensions and Midjourney prompts.
- [x] Comprehensive Testing: Added `tests/m15-client-demo.test.mjs`; all 18 test suites pass 100%.

## HIGH PRIORITY — BUSINESS RULE REVIEW (RECORDED FOR FUTURE RESOLUTION)

- [x] Inventory Lifecycle v2 Transition Plan Documented (`docs/inventory-lifecycle-v2-plan.md`):
  - Transition path from immutable v1 terminal model to flexible v2 demand-driven inventory model without compromising historical purchase provenance.

# Milestones

## M0 — Repository foundation

Status: complete (2026-08-13)

- [x] Audit the initially empty repository and local Git/tool state.
- [x] Initialize local Git without a remote or commit.
- [x] Establish concise AI context, repository hygiene, and deployment safeguards.
- [x] Create empty theme/plugin runtime roots without feature implementation.
- [x] Add and run lightweight structure, secret, scope, and PHP-lint discovery checks.

## M1 — Theme Skeleton + Core Plugin Skeleton

Status: complete (2026-08-14)

- [x] Add the minimum valid standalone theme skeleton at version `0.1.0`.
- [x] Add the minimum valid core plugin skeleton at version `0.1.0`.
- [x] Establish separated bootstraps, graceful WooCommerce absence behavior, structural tests, PHP lint, and a narrow bootstrap smoke test.
- [x] Keep storefront, Drop, private-access, archive, and inventory-domain features out of scope.

## M2 — Design System + Global Shell

Status: complete (2026-08-14)

- [x] Add `theme.json` v3 with the approved palette, restrained typography, spacing, and layout tokens.
- [x] Add accessible global base/layout CSS and an isolated frontend asset loader.
- [x] Add the minimum WordPress document shell without branded header, navigation, or footer components.
- [x] Preserve plugin and commerce/domain scope boundaries.

## M3 — Header + Mobile Navigation + Footer

Status: complete (2026-08-14)

- [x] Add the sticky desktop/mobile header with centered native site identity and WordPress-driven menus.
- [x] Add accessible native-dialog navigation and search interactions with lightweight vanilla JavaScript.
- [x] Add conditional WooCommerce Account/Bag links without cart behavior or hardcoded routes.
- [x] Add the restrained WordPress-driven footer and verify M1–M3 regressions.

## M4 — Drop Architecture + Product Metadata

Status: complete; integrity hardened (2026-08-14)

- [x] Register the `statement_drop` product taxonomy with a controlled one-Drop admin field.
- [x] Add canonical release-state and optional edition-label metadata through WooCommerce CRUD.
- [x] Enforce same/forward-only lifecycle transitions and terminal purchasability locks.
- [x] Verify malformed input, WooCommerce absence, and positive-stock terminal products.
- [x] Inherit terminal parent release state for variations without duplicating lifecycle metadata.
- [x] Lock established historical Drop relationships from `PRIVATE_ACCESS` onward while retaining bounded first-assignment recovery.

## M5 — Homepage

Status: complete (2026-08-14)

- [x] Add the bespoke editorial homepage with featured-image hero and native page-content zone.
- [x] Add a minimal read-only core presentation API and restrict normal public exposure to `LIVE`.
- [x] Select one deterministic LIVE Drop and up to four legitimate products through a bounded WooCommerce query.
- [x] Add omittable empty states, conditional homepage CSS, privacy tests, and dependency-absence coverage.

## M6 — Shop + Drop Storefront

Status: complete (2026-08-14)

- [x] Restrict public Shop and `statement_drop` main queries to canonical `LIVE` products before pagination.
- [x] Keep WooCommerce's native Shop query/pagination and the taxonomy relationship authoritative.
- [x] Add a shared Home/Shop/Drop product card, restrained catalog styling, and privacy-safe empty states.
- [x] Preserve WooCommerce absence safety and exclude Add to Cart, quick-commerce, and later access/archive behavior.

## M7 — Product Detail Page

Status: complete (2026-08-14)

- [x] Restrict ordinary direct product pages and crafted Add-to-Cart requests to canonical `LIVE` products.
- [x] Add a focused native WooCommerce product composition for gallery, provenance, price, description, and purchase form.
- [x] Preserve native simple/variable purchase mechanics without custom variation JavaScript or cart behavior.
- [x] Add conditional responsive product CSS, lifecycle/privacy regression tests, and WooCommerce-absence coverage.

## M8 — Cart + Bag Experience

Status: complete (2026-08-14)

- [x] Keep the direct Bag link and add a safe server-rendered WooCommerce quantity count without fragments or a drawer.
- [x] Revalidate restored and current cart lines against canonical `LIVE`, including variation parent ownership.
- [x] Add the restrained classic WooCommerce Cart presentation while retaining native quantity, removal, totals, and checkout routing.
- [x] Omit Cart coupon entry and cross-sell presentation without changing WooCommerce data or global settings.

## M9 — Checkout

Status: complete (2026-08-14)

- [x] Reuse M8 Cart Integrity as the final canonical `LIVE` checkout gate and emit a blocking lifecycle-neutral error when stale lines are removed during checkout processing.
- [x] Add one focused classic WooCommerce Checkout override with native customer fields, order review, payment, shipping, validation, and order processing intact.
- [x] Add responsive Statement Checkout CSS, omit only coupon-entry presentation, and introduce no first-party checkout JavaScript.
- [x] Preserve order-pay/order-received flows and keep payment gateways, shipping methods, account policy, and private access out of scope.

## M10 — Private Access

Status: complete (2026-08-14)

- [x] Add versioned schema migration manager with 5 operational tables.
- [x] Add secret configuration and authenticated encryption with keyring versioning.
- [x] Add HMAC email and IP rate-limit identity hashing.
- [x] Add immutable grant expiry logic and 5-active session cap per grant.
- [x] Add single-use access return and marketing unsubscribe tokens.
- [x] Add rolling IP and email rate limiting thresholds.
- [x] Add append-only marketing consent event tracking.
- [x] Add centralized commerce eligibility service reused across product detail, add-to-bag, cart integrity, checkout, and order audit.
- [x] Add POST/303 PRG private access gate with private cache control and noindex headers.
- [x] Add Make Drop Live admin action with read-only preflight and atomic transition.
- [x] Add WooCommerce Action Scheduler marketing reminders with Add-to-Bag auto-cancellation.
- [x] Add WooCommerce admin grant management UI with masked email reporting and re-grant support.
- [x] Add privacy data retention cleanup for expired tokens, rate limits, and inactive grants.

## M11 — Archive & Terminal Presentation

Status: complete (2026-08-15)

- [x] Enforce forward-only terminal transitions (`LIVE` -> `SOLD_OUT` -> `ARCHIVED`) and reject invalid backward mutations.
- [x] Establish permanent commerce lock override so positive WooCommerce stock cannot restore purchasability for `SOLD_OUT` or `ARCHIVED` products.
- [x] Maintain permanent direct product permalink viewability for `SOLD_OUT` and `ARCHIVED` items without returning 404s.
- [x] Update active catalog queries to include `LIVE` and `SOLD_OUT` items ordered with `LIVE` first, while excluding `ARCHIVED` items for dedicated Archive presentation.
- [x] Resolve fully historical Drop pages (`is_past_drop`) to render their `ARCHIVED` items instead of leaving them empty.
- [x] Omit Add to Bag forms and quantity selectors on terminal product pages, rendering restrained status badges (**SOLD OUT** or **ARCHIVED**).
- [x] Add dedicated `page-archive.php` template displaying archived product grids and historical past Drop terms.
- [x] Filter WooCommerce structured data offer availability to `OutOfStock` for terminal items.
- [x] Protect core brand scarcity model (**Crafted. Limited. Never Restocked.**) with zero waitlist or restock logic.

## M12 — Collector Provenance & Order Experience

Status: complete (2026-08-15)

- [x] Create write-once purchase provenance service capturing schema version, product ID, variation ID, Drop ID, Drop name, edition label, piece title, release state, and timestamp during order line item creation.
- [x] Enforce immutability so subsequent product edits, Drop name changes, or source deletions do not alter historical order item provenance.
- [x] Maintain explicit boundary separating order purchase provenance from certified collector ownership and authenticity certificate generation.
- [x] Coexist cleanly with M10 Private Access audit metadata without duplicating PII, session tokens, or payment secrets.
- [x] Provide future-facing commercial completion helper (`Completion::is_commercially_completed`) evaluating `processing` and `completed` order statuses.
- [x] Add read-only Statement Provenance presentation to WooCommerce admin order item screens with zero edit inputs.
- [x] Enhance customer order details and Order Received (Thank You) experience with status-aware banners and "Continue Exploring" navigation.
- [x] Enrich WooCommerce customer transactional emails with frozen provenance data independent of marketing unsubscribe flags.
- [x] Maintain strict scarcity model (**Crafted. Limited. Never Restocked.**) with zero collector numbers, production caps, or certificate generation.

## M13 — Runtime Integration Preparation & Verification

Status: Phase 1, Phase 2, Phase 3A, Phase 4A, Phase 5A, Phase 5B1, & Phase 5B2.1 complete (2026-08-15)

- [x] Create deterministic packaging scripts (`package-theme.mjs`, `package-plugin.mjs`, `package-all.mjs`) generating single-root ZIP artifacts in `dist/`.
- [x] Implement package content verification (`verify-package.mjs`) checking root directory, required runtime files, packaged PHP linting, and dev/secret exclusions.
- [x] Enforce package exclusion rules for `.git/`, `.github/`, `.ai/`, `tests/`, `scripts/`, `docs/`, `.local-tools/`, `node_modules/`, `dist/`, `coverage/`, `tmp/`, `logs/`, `php.ini`, `.env`, `package-lock.json`, and OS/IDE metadata.
- [x] Support candidate versioning (`0.13.0-rc.2`) and generate `dist/manifest.json` with SHA-256 checksums and `deployment_authorized = false`.
- [x] Create operator preflight checklist (`docs/atomic-integration-preflight.md`) for WordPress, WooCommerce, theme/plugin, and secret configuration.
- [x] Create emergency rollback runbook (`docs/atomic-rollback-runbook.md`) covering plugin, theme, checkout, and private edge cache failures.
- [x] Establish structured integration test checklist (`docs/runtime-integration-checklist.md`) with 19 test cases (`M13-PA-01` through `M13-RS-01`).
- [x] Create defect tracking log template (`docs/runtime-integration-log.md`) and release candidate record template (`docs/runtime-release-candidate.md`).
- [x] Build dedicated Node packaging test suite (`tests/m13-packaging.test.mjs`) validating zero remote deployment calls and manifest safety.
- [x] Phase 2: Verify `statement-collector-core` candidate `0.13.0-rc.2` bootstrap on Atomic.
- [x] Phase 3A: Build temporary integration fixture plugin `statement-integration-fixtures` (`0.1.1`) and seed/verify LIVE/SOLD_OUT dataset on Atomic.
- [x] Phase 4A: Perform local theme runtime-readiness audit and contract verification (`docs/theme-atomic-runtime-readiness.md` and `tests/m13-theme-runtime-readiness.test.mjs`).
- [x] Phase 5A: Execute Atomic Storefront + Commerce + Lifecycle Runtime Matrix across all 9 primary routes. Decouple WooCommerce stock from Statement release state. Verify forward-only `ARCHIVED` lifecycle transition. Publish verified local Git repository history to GitHub remote `https://github.com/rizvee/statement-wp-theme-plugin.git`.
- [x] Phase 5B1: Build Private Access Atomic Runtime Harness & Secret Preparation. Create local secret generator `scripts/generate-private-access-secrets.mjs` writing to ignored `.local-runtime/private-access-wp-config.php` without printing secret values. Upgrade fixture tool to `0.2.0` (`dist/statement-integration-fixtures-0.2.0.zip`) adding **PRIVATE ACCESS RUNTIME PREFLIGHT**, crypto backend diagnostic, and M10 database schema table existence checks (`M13-DB-01`). Build anonymous API test harness `scripts/test-private-access-api.mjs` and test plan `docs/private-access-atomic-test-plan.md`.
- [x] Phase 5B1.2: Fix `.gitignore` tracking for `src/Access/Secrets.php` runtime source and add `scripts/verify-git-runtime-tracking.mjs`.
- [x] Phase 5B2.1: Private Fixture API Contract & Partial Recovery Hotfix:
  - Added canonical `DropConfig::save_config( int $term_id, array $config ): bool` writer API with UTC conversion, duration validation, and transactional rollback.
  - Added `DropConfigAdmin` for WP Admin `statement_drop` taxonomy screen management.
  - Repaired `PrivateFixtureService` to operate on `WC_Product` objects for `Metadata::set_edition_label()` and `Metadata::set_release_state()`, with explicit `$product->save()`.
  - Implemented 4-state fixture lifecycle (`NOT_CREATED`, `PARTIAL`, `CREATED`, `RECOVERY_REQUIRED`) with idempotent adoption/recovery of existing test entities (`test-private-drop-01` / `TEST-PD01-PAJ`).
  - Packaged `statement-collector-core-0.13.0-rc.4.zip` and `statement-integration-fixtures-0.2.2.zip`.
  - Added 19-assertion PHP behavior test (`tests/php/test-drop-config-fixture-recovery.php`).
  - Full test suite green (116 Node subtests, 79 PHP files linted).
  - Pushed commit `3138927` to GitHub `main`.

- [x] Phase 5B2.2: Private Access Gate Detection & Metadata Contract Sweep:
  - Root cause confirmed: `PrivateAccessGate` passed integer product ID into object-based `Metadata::get_release_state()` API, which normalized state to `UPCOMING` and bypassed the gate.
  - Core `Metadata` call-site contract sweep completed across all Core source files.
  - Gate detection fixed: implemented `PrivateAccessGate::resolve_private_products( array $product_ids ): array` returning valid `WC_Product` objects, evaluated against `EligibilityService::is_commerce_eligible( $private_products[0] )`.
  - Audited `Product/Access.php` (confirmed true 404, nocache hardening, no product data leakage for unauthorized requests).
  - Fixed `MakeDropLive.php`, `Precheck.php`, and `ReminderService.php` product resolution and canonical `Metadata::set_release_state()` calls.
  - Added dedicated 14-assertion PHP contract test (`tests/php/test-private-access-gate-contract.php`).
  - Packaged `statement-collector-core-0.13.0-rc.5.zip` (SHA-256: `d6dfb666a0c7d6159ccf274e9624aae6ccf2053194a6f3356e5cdb9797074573`).
  - Full test suite green (116 Node subtests, 79 PHP files linted, foundation & tracking verifiers pass).

### Phase 5B2.2 Runtime Status & Next Steps

**STATUS**: CODE FIX COMPLETE / READY FOR ATOMIC RETEST

**Atomic Current**:
- Core `0.13.0-rc.4` currently deployed
- Fixture `0.2.2` deployed
- Private fixture `CREATED`
- Secret Vault `INITIALIZED`
- Frontend private Drop currently falls through to normal Drop template until `rc.5` deployment

**Next**:
- Deploy Core `0.13.0-rc.5`
- Verify vault remains initialized
- Open private Drop (`/drop/test-private-drop-01/`)
- Verify email gate renders (`PRIVATE ACCESS`, email input, `ENTER PRIVATE ACCESS`)
- Verify unauthorized private PDP (`/product/test-private-access-jacket/`) is true 404
- Resume Phase 5B2 runtime matrix

### Phase 5B2.3 Anonymous Boundary Runtime Result

**STATUS**: CODE FIX REQUIRED / GRANT TESTING STOPPED (2026-08-15)

- Cookie-free Atomic checks: private Drop gate passed (HTTP 200, no protected product facts); private PDP returned true HTTP 404 but leaked its title/slug into the 404 body; WooCommerce Store API returned the PRIVATE_ACCESS product. WP product REST and public search did not expose it.
- Added regressions for WooCommerce 11 Store API query enforcement and unauthorized PDP query-context scrubbing.
- Core `0.13.0-rc.6` fixes both proven anonymous leaks; Theme remains `0.13.0-rc.2`; Fixtures remain `0.2.2`.
- Gate post, cookie, authorized content, cache/session isolation, grant reuse, cart, and checkout were not run after the failed anonymous boundary. No email, order, payment, expiry, revocation, reminder, rate-limit exhaustion, vault reset, or fixture mutation occurred.
- Atomic state remains Core `0.13.0-rc.5` ACTIVE, Fixtures `0.2.2` ACTIVE, Vault `INITIALIZED`, private fixture `CREATED`.
- Remaining blockers: operator deployment of Core `0.13.0-rc.6`; ignored `.local-runtime/qa-email.txt` is absent and is required only after the anonymous retest passes.
- **NEXT**: Deploy `dist/statement-collector-core-0.13.0-rc.6.zip`, rerun the anonymous matrix, then create `.local-runtime/qa-email.txt` before the legitimate grant/session flow.

### Phase 5B2 Final Anonymous Retest

**STATUS**: CODE FIX REQUIRED / GRANT TESTING STOPPED (2026-08-15)

- **UNVERIFIED VERSION ATTRIBUTION**: this raw observation was originally labeled an rc.6 retest, but Atomic was later confirmed to still be serving rc.5. The observed private PDP title/slug and Store API exposure are retained without attributing them to rc.6.
- Root runtime behavior showed the 404 falling through to the theme index loop with the private post still present, while WooCommerce 11 bypassed the query-only Store API constraint.
- Core `0.13.0-rc.7` strengthens 404 query/loop scrubbing and adds a fail-closed Store API response boundary, with focused regressions.
- Grant/session, cookie flags, authorized access, cache isolation, grant reuse, cart, and checkout were not run after the anonymous failure. QA identity was not read. Email/reminder remained OFF.
- Atomic state: Theme `0.13.0-rc.2`, Core `0.13.0-rc.6` ACTIVE, Fixtures `0.2.2` ACTIVE, private fixture `CREATED`, Vault `INITIALIZED`.
- **NEXT**: Deploy `dist/statement-collector-core-0.13.0-rc.7.zip` and rerun the anonymous hard gate before any grant/session flow.

### Phase 5B2 rc.7 Atomic Retest

**STATUS**: CODE FIX REQUIRED / GRANT TESTING STOPPED (2026-08-15)

- **UNVERIFIED VERSION ATTRIBUTION**: the live Core version was not observable and Atomic was later shown to be on rc.5. The raw Drop/PDP/Store API observations are retained but are not confirmed rc.7 behavior.
- Core `0.13.0-rc.8` adds a dedicated non-looping generic 404 template and final Store API serialization filtering, retaining the earlier query and response boundaries.
- Grant/session, cookie, authorized access, cache isolation, reuse, cart, and checkout were not run. QA identity was not read. Email/reminder remained OFF.
- Atomic reported target state: Theme `0.13.0-rc.2`, Core `0.13.0-rc.7`, Fixtures `0.2.2`, Vault `INITIALIZED`, private fixture `CREATED`; live Core version could not be independently proven over HTTP.
- **NEXT**: Deploy `dist/statement-collector-core-0.13.0-rc.8.zip`, verify the active version in WP Admin, then rerun the anonymous hard gate.

### M13 FINAL ATOMIC PRIVATE ACCESS QA

**STATUS**: RUNTIME MATRIX VERIFIED ON ATOMIC / FIXTURE 0.3.2 READY (2026-08-16)

**Runtime Classification Matrix:**

| Gate / Feature | Status | Notes |
| --- | --- | --- |
| Anonymous Privacy Boundary | `RUNTIME_PASS` | Drop gate HTTP 200 without leaks; PDP true HTTP 404; Store API, REST, Search clean |
| Gate POST & Session Grant | `RUNTIME_PASS` | Nonce verified, IP rate limiter checked, PRG HTTP 303 redirect, session cookie issued |
| Secure Cookie Contract | `RUNTIME_PASS` | `statement_drop_access_1376` issued with HttpOnly, Secure, SameSite=Lax |
| Authorized Drop & PDP | `RUNTIME_PASS` | Private Drop catalog loads, single PDP loads with AUD 310, Add-to-Bag enabled, zero scarcity leaks |
| A/B Cache & Edge Isolation | `RUNTIME_PASS` | `Cache-Control: private, no-store, no-cache, max-age=0, must-revalidate` on authorized profile; anonymous requests never receive private cache |
| Authorized Cart & Bag Count | `RUNTIME_PASS` | Added `TEST-PD01-PAJ` (qty 1, AUD 310), survives Cart Integrity reconciliation across cart/checkout |
| Anonymous Add-to-Cart Bypass | `RUNTIME_PASS` | Direct cart submission without session rejected |
| Session Cap FIFO Enforcement | `RUNTIME_PASS` | Deterministic 6th session revoked oldest session; 5 active sessions maintained |
| Rate Limiter Enforcement | `RUNTIME_PASS` | Allowed 3 attempts, 4th blocked at threshold; test rows cleaned |
| Single-Use Return Token | `RUNTIME_PASS` | First consumption accepted; replay rejected; invalid rejected; expired rejected |
| Marketing Unsubscribe Boundary | `RUNTIME_PASS` | Marketing consent withdrawn while private access grant remains valid |
| Grant Revocation & Public Regrant Barrier | `RUNTIME_PASS` | Grant revoked, sessions invalidated, public self-regrant blocked |
| Admin Re-Grant Restoration | `RUNTIME_PASS` | Canonical admin re-grant created with `supersedes_grant_id`; new active session issued |
| Checkout Email Mismatch Rejection | `RUNTIME_PASS` | Non-matching checkout billing email rejected by Cart/Checkout Integrity |
| QA Payment Gateway Availability | `RUNTIME_PASS` | `TEST ONLY — NO PAYMENT (Statement QA)` available for exact test SKU `TEST-PD01-PAJ` |
| Expiry Normalization Runtime (0.3.3) | `RUNTIME_PASS` | Verified live on Atomic: Earlier close shortened effective expiry; later close kept grant expiry invariant; Drop config restored clean |
| Reminder Scheduler Runtime (0.3.3) | `RUNTIME_PASS` | Verified live on Atomic: Reminder action scheduled cleanly and auto-cancelled upon Add to Bag with valid session context |
| Access Email Dispatch (0.3.3) | `RUNTIME_PASS` | Verified live on Atomic: Return token created safely, mail trigger executed, and Drop config restored clean |
| Terminal Lifecycle Revalidation | `RUNTIME_PENDING` | `run_terminal_lifecycle_test()` updated in source with resilient multi-SKU (`TEST-LD01-TJ`/`TEST-TJ01-ARC`) and slug lookup |
| Controlled QA Order Execution | `RUNTIME_PENDING` | Ready for execution via `scripts/test-private-access-order.mjs` / WP checkout on active session |
| Exactly-Once Stock Reduction | `RUNTIME_PENDING` | Hardened in gateway `process_payment()` via WooCommerce standard `payment_complete()` |
| M10 Order Authorization Audit | `RUNTIME_PENDING` | Ready for verification via `verify_last_order()` upon order placement |
| M12 Frozen Provenance Snapshot | `RUNTIME_PENDING` | Ready for verification via `verify_last_order()` upon order placement |
| Provenance Immutability | `RUNTIME_PENDING` | Ready for verification via `test_provenance_immutability()` upon order placement |
| Order Received / My Account UI | `RUNTIME_PENDING` | Presentation metadata verified via server-side contract; visual confirmation pending order placement |

**Harness Diagnostics & Live Verifications:**
1. **Expiry Test Normalization**: Verified `RUNTIME_PASS` on Atomic: Both `closes_at` and `closes_at_ts` updated, ensuring deterministic persistence and reading from term meta.
2. **Reminder Scheduler**: Verified `RUNTIME_PASS` on Atomic: Scheduled action auto-cancels upon cart placement.
3. **Access Email QA Action**: Verified `RUNTIME_PASS` on Atomic: Branded `EmailAccessGranted` triggered, return token created in `wp_statement_access_tokens`, and temporary Drop config restored clean.
4. **Terminal Lifecycle Revalidation**: Fixed SKU lookup mismatch in `QaTestService.php` to resolve `TEST-LD01-TJ`, fallback to slug `test-terminal-jacket`, and fallback to `TEST-TJ01-ARC`.

**Authoritative Release Candidates & Verification:**
- Package: `dist/statement-integration-fixtures-0.3.3.zip` (27,311 bytes, SHA-256: `97fbb481613fc619434e87b5d81fb3815ab7b690bd2d1e80e94dbf547ec70850`).
- Package: `dist/statement-collector-theme-0.13.0-rc.4.zip` (44,858 bytes, SHA-256: `d3053c00d3674666cfd870b8557cfde90e70d47be2a6c0af25a0183c31d3e1bf`).
- Package: `dist/statement-collector-core-0.13.0-rc.9.zip` (66,782 bytes, SHA-256: `6e1ab1ea2571c757852c299631834f4aa5f59040e7afe021e18cb0d803e4c3d4`).
- 82 PHP files linted clean, 129 Node subtests across 17 suites pass (100% clean), 19 fixture bootstrap assertions pass, 9 QA contract assertions pass.

## M14 — Storefront Hardening & Luxury Polish

Status: complete (2026-08-16)

- [x] Design System Tokens & Typography: Locked palette tokens, Instrument Serif display typography, and Inter UI typography defined in `theme.json` v3.
- [x] Editorial Fallback Navigation: Added `get_shop_url()`, `get_archive_url()`, `render_primary_navigation()`, and `render_mobile_primary_navigation()` in `inc/navigation.php` to guarantee graceful SHOP and ARCHIVE fallback links when no WordPress menu is assigned.
- [x] Accessible Dialog Navigation: Hardened `assets/js/navigation.js` with backdrop click close, body scroll locking via `.statement-dialog-open`, and window resize listeners.
- [x] Search Privacy & Dialog Accessibility: Role search, autofocus, close button, and escape key management in `search-dialog.php`.
- [x] Homepage LIVE-only Contract: Bounded 4-product showcase loop, hero editorial layout, and principle statement in `front-page.php`.
- [x] Catalog & Shop Lifecycle Ordering: LIVE pieces first, SOLD OUT pieces second, 4:5 portrait card proportions, and missing image placeholders in `assets/css/catalog.css` and `product-card.css`.
- [x] Dedicated Archive Page: Historical drops and archived piece presentation in `page-archive.php` enforcing permanent record scarcity without waitlist or restock wording.
- [x] Product Detail Page Lifecycle UI: Simple and variable native WooCommerce variation support, commerce locks on terminal products, and responsive gallery layout in `assets/css/product.css`.
- [x] Cart & Bag Responsive Protection: Responsive table, mobile stacked items, and 320px viewport overflow protection in `assets/css/cart.css`.
- [x] Checkout & Order Presentation: Styled WooCommerce Checkout override, Order Received confirmation, My Account dashboard, and frozen provenance presentation in `assets/css/checkout.css`.
- [x] Theme Version & Constant Invariants: Promoted theme candidate from `0.13.0-rc.3` to `0.13.0-rc.4` in `style.css` and `functions.php`.
- [x] Comprehensive Test Suite: Expanded `tests/m14-theme-hardening.test.mjs` with 14 comprehensive behavioral, contract, accessibility, and override boundary assertions.
- [x] Single-Root Packaged Artifact: `dist/statement-collector-theme-0.13.0-rc.4.zip`.

## M15 — Production Launch Readiness

Status: started (2026-08-16)

- [x] Final Fixture Cleanup Plan: Created `docs/final-fixture-cleanup.md` detailing complete test entity inventory, database table purge sequence, and clean uninstallation procedures.
- [x] Technical SEO Launch Checklist: Created `docs/seo-launch-checklist.md` detailing semantic heading hierarchy, title tag format, canonical URLs, and private access indexing boundaries.
- [x] Shipping Launch Readiness: Created `docs/shipping-launch-readiness.md` detailing Australia Post / MyPost Business operational integration and shipping zone configuration.
- [x] Payment Launch Checklist: Created `docs/payment-launch-checklist.md` detailing WooPayments AUD live activation, test card verification, and QA gateway deactivation.
- [x] Transactional & Access Email Readiness: Created `docs/email-launch-readiness.md` detailing complete 9-email transactional matrix, deliverability guidelines, and unsubscribe separation.
- [x] Legal & Content Gap Inventory: Created `docs/legal-content-gap-inventory.md` classifying required statutory Australian Consumer Law policies and editorial content requirements.
- [x] Production Plugin Stack Audit: Created `docs/production-plugin-audit.md` classifying all plugins on Atomic into Keep, Remove After Migration (Elementor, Fixtures), and Review.
- [x] Production Product Import Plan: Created `docs/production-product-import-plan.md` defining input-driven schema, variations, AUD pricing, inventory, and scarcity rules for Drop 001.
- [x] Drop 001 Production Intake Spec: Created `docs/drop-001-production-input.md` fillable template for operator commercial and piece specifications.
- [x] Drop 001 Media Asset Map: Created `docs/drop-001-media-map.md` mapping role-based 4:5 portrait media assets.
- [x] Legal Content Input Pack: Created `docs/legal-content-input.md` for corporate entity, terms, returns, and privacy policies.
- [x] Shipping Configuration Input Pack: Created `docs/shipping-configuration-input.md` for Australia Post carrier options and flat rates.
- [x] Payment Configuration Input Pack: Created `docs/payment-configuration-input.md` for WooPayments Stripe onboarding and descriptors.
- [x] SEO Production Input Pack: Created `docs/seo-production-input.md` for global metadata, OpenGraph cards, and indexing launch timings.
- [x] Objective Launch Gate Matrix: Created `docs/launch-gate-matrix.md` detailing 12-gate assessment from Gate A to Gate L.
- [x] Production Verification Scripts: Created `scripts/verify-production-clean-state.mjs` and `scripts/test-production-readiness.mjs`.
- [x] Production Input Validation Tooling: Created `scripts/validate-drop-production-input.mjs`, `scripts/validate-production-readiness-configs.mjs`, and `config/drop-001.example.json`.
- [x] Production Product Importer Design: Created `docs/production-product-importer-design.md` specifying idempotent, dry-run-first import architecture.
- [x] Drop 001 Launch Runbook: Created `docs/drop-001-launch-runbook.md` defining operational lifecycle management from Private Access through SOLD OUT and permanent archive.
- [x] Final Release Runbook: Created `docs/final-release-runbook.md` detailing exact sequential cutover protocol with automatic backup verification.
- [x] Authoritative Release Candidate Manifest: Created `docs/final-rc-manifest.md` documenting verified checksums and deployment prerequisites.

## M16 — Production Content & Final Cutover Preparation

Status: started (2026-08-16)

- [x] Content Layer Architecture & Slots: Created `docs/m16-production-content-layer.md` defining slot mapping for root hero, showcase loop, brand manifesto, archive intro, craft PDP tabs, and footer link map.
- [ ] Production Cutover Execution: Awaiting operator execution of post-RC automatic backup verification, Jetpack scan, fixture cleanup, and Drop 001 product import.

## Visual Sprint 01–04 — Client Demo & Luxury Refinements

Status: complete (2026-08-17)

- [x] WhatsApp Brand Asset Audit & Packaging Section: Curated brand objects into luxury presentation.
- [x] Client Demo Tool v0.2.2: Deterministic demo seeder with collision prevention and manifest-driven rollback.
- [x] Core Marketing Signup Security & Hardening: 3-mode consent event capture, fail-closed rate limiting, encrypted storage (Core `0.13.0-rc.11`).
- [x] Privileged Lifecycle Override Service: Safe transition engine with stock checks, future DropConfig requirements, and audit log.
- [x] Contemporary Typography System: Modern sans (`Inter`) baseline with editorial serif accents.
- [x] Bespoke Hero Slider: Touch-swipe, autoplay, accessible Vanilla JS slider with Customizer integration.
- [x] Editorial About & Contact Pages: 4-pillar craft showcase and client support interfaces.

## Visual Sprint 05 — Extensibility & Premium Finish Mega Sprint

Status: complete (2026-08-18)

- [x] Phase 0 — Baseline & Git Checkout Verification: Zero uncommitted changes, synchronized with `origin main`.
- [x] Phase 1 — Premium WordPress Theme Architecture Study: Authored `docs/premium-theme-architecture-study.md`.
- [x] Phase 2 — Core QA Fixture Isolation Fix: Implemented `Visibility::is_fixture_product()` and patched `PublicApi::get_archive_products()`, `get_past_drops()`, `is_past_drop()`, `get_current_drop()`, `get_live_products_for_drop()` to never leak `_statement_fixture = 1`, `TEST-*` SKUs, or `TEST —*` titles into public catalog/archive while strictly protecting `_statement_client_demo = 1` products. Promoted Core to `0.13.0-rc.12`.
- [x] Phase 4 — Theme Modular Refactoring: Split theme into `inc/setup.php`, `inc/design-tokens.php`, `inc/hooks.php`, `inc/page-meta.php`, `inc/customizer.php`, `inc/compatibility/*`, `inc/admin/*`. Maintained minimal `functions.php` bootstrap under 25 lines with zero raw `add_action` calls. Promoted Theme to `0.13.0-rc.9`.
- [x] Phase 5 — Public Extension API Reference: Implemented canonical layout action hooks and filter hooks. Authored `docs/theme-hooks-reference.md`.
- [x] Phase 6 — Starter Child Theme Package: Built `tools/statement-collector-child/` with upgrade-safe parent stylesheet inheritance, custom functions template, and authored `docs/child-theme-customization-guide.md`.
- [x] Phase 7 & 8 — Design Tokens & Global Customizer: Connected CSS custom properties (`--statement-color-*`, `--statement-container-*`, etc.) to Customizer settings across Global, Header, Shop & Catalog, and Homepage Hero Slider with strict sanitization.
- [x] Phase 9 — Options Export / Import: Built `inc/admin/options-export.php` with nonce verification, `manage_options` capability check, and automated pre-import backup.
- [x] Phase 10 — Page-Level Meta Overrides: Added meta box controls for layout width (Default/Contained/Wide/Full Bleed), Header style (Default/Transparent/Hidden), Footer toggle, and Title toggle.
- [x] Phase 11 — Elementor Theme Locations: Registered Elementor Theme Location API (`header`, `footer`, `single`, `archive`) in `inc/compatibility/elementor.php`.
- [x] Phase 12 — Gutenberg Block Patterns: Registered curated luxury block patterns (Hero, Split Story, Editorial Grid, Newsletter) and `statement-luxury` pattern category in `inc/compatibility/gutenberg.php`.
- [x] Phase 13, 14, 15 — WooCommerce Compatibility, Blocks & HPOS: Declared HPOS compatibility in `inc/compatibility/woocommerce.php` and added scoped Cart/Checkout block styles in `assets/css/woo-blocks.css`.
- [x] Phase 16–22 — Plugin Compatibility, Admin Setup Screen & Diagnostics: Built adapters for `seo.php`, `jetpack.php`, `forms.php`, `caching.php`, `health.php`, and `setup-screen.php`. Authored `docs/plugin-compatibility-matrix.md` and `docs/theme-setup-guide.md`.
- [x] Phase 26, 35, 39 — Visual QA Refinements: Restyled buttons to pure Ink, scaled mobile navigation drawer typography, restyled WooCommerce notices and breadcrumbs, and converted Journal index to an editorial article grid.
- [x] Phase 56–68 — Automated Testing & Release Packaging:
  - 138 Node integration tests across 19 suites pass 100% green.
  - 40 PHP tests across all functional modules pass 100% green.
  - `verify-foundation.mjs` and `verify-git-runtime-tracking.mjs` pass 100% green.
  - Generated and verified release packages in `dist/`:
    - `statement-collector-theme-0.13.0-rc.9.zip` (SHA-256: `b8538b62479f66bdd354ef81bf528112013d9662ff0a7c7e44e540a307035fe8`)
    - `statement-collector-core-0.13.0-rc.12.zip` (SHA-256: `eca4187a22523ff1a6cead9811892dd6d88fbb1d289b528f93cd3d77ced0c562`)
    - `statement-client-demo-0.2.2.zip` (SHA-256: `c3bb867f88a9d4b2914451fcc6868e29f247b1f3c2ae530a1811ebcf14b087ca`)
    - `statement-collector-child-0.1.0.zip` (SHA-256: `a5c06bcd04fab5d994c740dbf4f71872c6639167b500de97a2e46af892dcf47d`)

## Sprint 05.1 — Acceptance Hardening & Integrity Sweep

Status: in-progress (2026-08-18)

- [ ] Critical QA Ownership Precedence Fix (`Visibility::is_fixture_product`): Ensure explicit `_statement_fixture = 1`, `TEST-*` SKU, and `TEST —` titles ALWAYS win before `_statement_client_demo = 1`. Mixed ownership MUST classify as QA fixture.
- [ ] Product 213 Forensic Ownership Classifier & Safe Repair: Formal classifier (`QA_FIXTURE`, `CLIENT_DEMO`, `PRODUCTION`, `CONFLICT`, `UNKNOWN`). Conflict blocks public presentation, blocks demo adoption, blocks mutation. Safe repair removes demo marker from fixture and seeds clean demo item.
- [ ] Public Fixture Isolation Sweep (`PublicApi`): Ensure `get_drop_state()` ignores QA fixtures so a fixture inside a Drop never alters Drop state or public resolution.
- [ ] Core HPOS Compatibility Ownership (`0.13.0-rc.13`): Audit order CRUD and declare `custom_order_tables` compatibility on `before_woocommerce_init` in Core plugin.
- [ ] Remove Theme HPOS Declaration: Clean `inc/compatibility/woocommerce.php` so theme only handles presentation and standard theme support.
- [ ] Theme WooCommerce Boot Timing & Lifecycle: Streamline `inc/setup.php` so theme supports execute deterministically without nested `after_setup_theme` registrations.
- [ ] Elementor Flexibility & Homepage Render Modes: Replace hard `protect_front_page_template` lock with Customizer switch `statement_front_page_renderer` (`statement` vs `content`). Support Elementor theme locations and add `statement_theme_register_elementor_locations` filter.
- [ ] Page-Builder Safe Templates: Add `template-canvas.php` and `template-full-width.php`.
- [ ] Options Import & Page Meta Security: Audit capability checks, nonce guards, and strict allow-list sanitization.
- [ ] Visual Polish & CSS Hardening: Ink button styling, mobile typography scaling, responsive overflow invariants (320px–1920px).
- [ ] Client Demo Candidate Bump (`0.2.3`): Add Ownership Diagnostics and safe repair tools in admin.
- [ ] Full Test Verification Suite: Extend PHP tests and create `tests/m19-acceptance-hardening.test.mjs`.

## Sprint 06 — Production Convergence & Live Readiness

Status: in-progress (2026-08-18)

- [ ] Package Core `0.13.0-rc.13`, Theme `0.13.0-rc.10`, Client Demo `0.2.3`, Child Theme `0.1.0`.
- [ ] Author `docs/sprint-05-acceptance-audit.md` and `docs/sprint-06-deployment-runbook.md`.
- [ ] Execute clean logical git commits and push to `origin main`.
- [ ] Deliver verified single manual deployment batch for Atomic operator execution.


