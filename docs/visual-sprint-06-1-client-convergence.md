# Visual Sprint 06.1 — Client-Asset Replacement & Final Convergence Report

Date: 2026-08-18
Status: Verified & Packaged (Theme 0.13.0-rc.11, Client Demo 0.2.4, Core 0.13.0-rc.13, Child Theme 0.1.0)
Remote: `https://github.com/rizvee/statement-wp-theme-plugin.git`
Branch: `main`

---

## 1. Executive Summary

Visual Sprint 06.1 directly executes client visual feedback and convergence directives for the upcoming launch:
1. **Client Asset Ingestion & Synchronization**: Integrated 18 client-provided studio and editorial jacket photographs into the demo seeder and theme assets.
2. **Image-First Homepage Editorial Slideshow**: Converted the homepage hero slider from heavy textual/campaign copy to a minimalist, image-first editorial carousel showcasing the physical craftsmanship of the jackets.
3. **Default-Disabled Private Access Email Capture**: Disabled email access capture on the front page and in default Customizer settings per client instruction (*"Ekhon dorkar nai apatoto baad dao we can add later. Just normal site banao."*), while strictly preserving all underlying M10 Private Access architecture and security invariants.
4. **Storefront & Product Presentation Polish**: Updated `/drops/` directory imagery, optimized WooCommerce native product gallery styling, and refined mobile touch navigation.
5. **Client Demo Seeder 0.2.4**: Added deterministic media update actions (`apply_new_client_media_set()`) to replace product gallery imagery on existing seeded stores without manual intervention or fixture contamination.

---

## 2. Changes by Component

### A. Client Assets & Media Registry
- Ingested 18 high-resolution JPEG assets into `.local-assets/client-drive/` and synchronized to:
  - `tools/statement-client-demo/assets/images/`
  - `wp-content/themes/statement-collector-theme/assets/images/`
- Added new media descriptors to `tools/statement-client-demo/src/AssetRegistry.php`:
  - `monogram_side`: Monogram Jacquard Jacket side profile view.
  - `hood_side`: Panelled Hood Jacket side profile view.
- Documented full mapping in `docs/client-drive-asset-map.md`.

### B. Statement Client Demo Tool (v0.2.4)
- **Version Bump**: `tools/statement-client-demo/statement-client-demo.php` and `AdminPage.php` bumped to `0.2.4`.
- **New Product Gallery Sequences**:
  - *Monogram Jacquard Jacket (`STMT-CD-D001-MJ`)*: Front, Back, Side, Collar Detail, Slate Flatlay, Concrete Flatlay.
  - *Panelled Hood Jacket (`STMT-CD-D001-PHJ`)*: Front, Back, Side, Embroidery Detail, Night 3/4, Cathedral Front.
- **Safe Media Replacement Engine**: Implemented `DemoSeederService::apply_new_client_media_set()` which safely updates attachment metadata, featured thumbnails, and product gallery IDs only on verified owned `CLIENT_DEMO` products.
- **Admin Controls**: Added "Apply New Client Media Set" button and POST action (`apply_media`) to the Demo Admin page.

### C. Statement Collector Theme (v0.13.0-rc.11)
- **Version Bump**: `style.css` and `functions.php` bumped to `0.13.0-rc.11`.
- **Image-First Hero Slider**:
  - Default slides in `template-parts/home/hero.php` now point to curated client jacket imagery.
  - Text overlay containers (`.statement-hero-slide__overlay`) are only rendered if a custom heading or eyebrow is explicitly provided in the Customizer.
  - Added `.statement-hero-slider--image-first` subtle gradient overlay in `assets/css/home.css` to protect photograph luminosity.
- **Homepage Email Capture**:
  - `front-page.php` section guard updated to `statement_home_show_access_capture` (defaults to `false`).
  - `inc/customizer.php` defaults `statement_enable_email_capture` and `statement_home_show_access_capture` to `false`.
- **Drops Directory Hero**: Replaced raster wordmark with high-resolution client jacket photography in `page-drops.php`.
- **Product Detail Styling**: Added scoped `.woocommerce-product-gallery` tokenized CSS rules for mobile thumbnails and desktop layout in `assets/css/product.css`.

---

## 3. Verification & Quality Assurance

- **PHP Linting**: 119/119 PHP files across Theme, Core, Fixtures, Child Theme, and Demo passed clean syntax check with PHP 8.3.33.
- **Node Test Suites**: All 20 milestone test suites passed 100% (144 subtests).
- **PHP Behavioral Tests**: All 42 standalone PHP tests in `tests/php/` passed 100% clean.
- **Git Tracking Verification**: `verify-git-runtime-tracking.mjs` passed 100% (all runtime files tracked, zero secret leaks).
- **Foundation Verification**: `verify-foundation.mjs` passed 100% (93 required files and 8 required directories found).

---

## 4. Release Artifacts & SHA-256 Checksums

All packages generated in `dist/` as single-root ZIP files:

| Artifact | Version | Size (Bytes) | SHA-256 |
|---|---|---|---|
| `statement-collector-theme-0.13.0-rc.11.zip` | 0.13.0-rc.11 | 20,858,283 | `129202c8f3ae13d5b227024a43a8f64984f673e032bc287d0c11f69427fc0cfe` |
| `statement-collector-core-0.13.0-rc.13.zip` | 0.13.0-rc.13 | 78,247 | `2580a5a83c3315a63716468407dbdd794e487812b9f8d061f0cee34f7478dfe7` |
| `statement-client-demo-0.2.4.zip` | 0.2.4 | 20,783,969 | `22bb696eae8d873ace89fe29ed80e45e6718267ed7f8b35885cb10086225c02d` |
| `statement-collector-child-0.1.0.zip` | 0.1.0 | 1,444 | `1a870412128c8cc51cedaa3906672c1d50dcf2f6d244446f95bc1b46a2a61f44` |
