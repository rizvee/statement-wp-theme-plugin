# Visual Sprint 04 / Client Delivery Mega Build Release Notes

**Candidate Versions:**
- **Statement Collector Theme**: `0.13.0-rc.8`
- **Statement Collector Core Plugin**: `0.13.0-rc.11` (Stable, untouched live candidate)
- **Statement Client Demo Plugin**: `0.2.1`
- **Statement Integration Fixtures**: `0.3.3` (Stable live candidate)

---

## 1. Executive Summary

Visual Sprint 04 directly addresses and completes all client feedback, luxury brand visual alignment, demo recovery, and storefront delivery requirements while strictly maintaining the foundational architectural and commerce boundaries.

**All automated checks, PHP unit tests, Node regression suites, and packaging integrity validations are 100% PASS.**

---

## 2. Client Feedback & Visual Upgrades

### A. Contemporary Luxury Typography System
- **Previous treatment eliminated**: The previous font styling that was rejected has been fully replaced.
- **Modern Sans Hierarchy**: The primary UI, navigation, product titles, metadata, buttons, body copy, and cart/checkout now use a clean, modern sans-serif typography stack (`Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`) with crisp, negative letter spacing (`-0.02em` to `-0.01em`) and balanced line heights.
- **Editorial Accent**: Traditional editorial serif (`"Instrument Serif", "Iowan Old Style", "Times New Roman", serif`) is preserved exclusively for subtle campaign accents and quotes (`.statement-editorial-serif`).
- **Zero External CDNs**: All typography is self-contained without runtime Google Fonts or external CDN requests.

### B. Typographical Wordmark & Header Brand Isolation
- **No White Raster Rectangles**: Opaque raster logo images with white backgrounds have been completely banned from the global header.
- **Clean Typographical Wordmark**: The global header features a letter-spaced typographical `STATEMENT` wordmark centered between left and right navigation menus, linking directly to the homepage.
- **Header Grid**:
  - **Left**: `SHOP`, `DROPS`, `ARCHIVE`
  - **Center**: `STATEMENT` (Home)
  - **Right**: `ABOUT`, `SEARCH`, `ACCOUNT`, `BAG` (with dynamic quantity badge)
- **Mobile Drawer Navigation**: Slide-out navigation drawer with accessible dialog attributes, body scroll locking, and links to `SHOP`, `DROPS`, `ARCHIVE`, `ABOUT`, `CONTACT`, `JOURNAL`, and `ACCOUNT`.

### C. First-Class Homepage Hero Slider
- **Vanilla JS/CSS Implementation**: Created `assets/js/hero-slider.js`, `assets/css/home.css`, and `template-parts/home/hero.php`. Zero external JS dependencies (no Swiper, Slick, or external carousel libraries).
- **Curated 4-Slide Editorial Campaign**:
  1. *Drop 001 Monogram Study* (`statement-panelled-hood-jacket-front.jpg`)
  2. *Monogram Jacquard Jacket* (`statement-monogram-jacket-front.jpg`)
  3. *Panelled Hood Jacket* (`statement-panelled-hood-jacket-cathedral-front.jpg`)
  4. *Edition Provenance / Dust Bag* (`statement-collector-dust-bag.jpg`)
- **Native Customizer Controls**: Registered native WordPress Customizer section `statement_hero_slider` with image, mobile image, eyebrow, heading, link, CTA text, and focal position controls for all 4 slides.
- **Full Accessibility & Touch**: Touch swipe detection (horizontal vs vertical thresholding), keyboard navigation (`ArrowLeft` / `ArrowRight`), pause on hover, pause on focus, pause when tab is hidden (`visibilitychange`), slide counter (`01 / 04`), dot indicators, and `prefers-reduced-motion` compliance.

### D. Dedicated About & Contact Pages
- **About Page (`page-about.php`)**: Editorial layout highlighting the 4 core pillars of the brand:
  - *Pillar 01: Form & Silhouette*
  - *Pillar 02: Material Provenance*
  - *Pillar 03: The Insignia*
  - *Pillar 04: The Object & Canvas Dust Bag*
  - Dedicated brand poster section: **"CRAFTED. NOT MASS MADE."**
- **Contact Page (`page-contact.php`)**: Luxury client inquiries layout featuring direct email contact, Instagram `@statement.au`, operating hours, and private acquisition support.

### E. Multi-Angle Product Gallery Hierarchy
- Standardized product gallery visual order:
  1. Primary Model Front
  2. Model Rear Architectural
  3. Concrete Stone Flat Lay
  4. Macro Collar / Weave / Embroidery Detail
  5. Dark Slate Material Study
  6. Craftsmanship & Provenance

---

## 3. Client Demo 0.2.1 Upgrades & Hardening

- **Core API Drift Correction**: Fixed `DemoSeederService.php` referencing nonexistent `Metadata::EDITION_KEY` -> updated to use canonical `Metadata::set_edition_label()` and `Metadata::set_release_state()`.
- **5-Point Deterministic Ownership Verification**:
  1. `_statement_client_demo = 1`
  2. SKU starts with `STMT-CD-`
  3. `_statement_fixture != 1` (never touch QA fixtures)
  4. SKU does NOT start with `TEST-`
  5. Title does NOT start with `TEST —`
- **Hero Slider Theme Mod Seeding**: Client Demo seeder automatically provisions the 4-slide hero configuration into theme mods upon seeding.
- **Automated Test Coverage**:
  - `tests/php/test-client-demo-contracts.php` (30/30 assertions pass)
  - `tests/php/test-client-demo-runtime.php` (35/35 assertions pass)
  - `tests/php/test-client-demo-collision.php` (13/13 assertions pass)

---

## 4. Verification & Validation Evidence

- **PHP Linting**: 101/101 PHP files linted cleanly (`node scripts/php-lint.mjs`) -> **PASS**
- **Node.js Automated Test Suites**: 136/136 tests pass across 18 test files (`node --test --test-concurrency=1 tests/*.test.mjs`) -> **PASS**
- **PHP Unit & Domain Test Suites**: 38/38 PHP test files pass (`tests/php/*.php`) -> **PASS**
- **Foundation Verification**: 73 required files, 7 required directories, zero secrets, zero unwanted zips (`node scripts/verify-foundation.mjs`) -> **PASS**
- **Git Runtime Tracking**: 100% of runtime source files tracked in Git (`node scripts/verify-git-runtime-tracking.mjs`) -> **PASS**
- **Package Verification**: All 3 ZIP artifacts verified against manifest (`node scripts/package-all.mjs`) -> **PASS**

---

## 5. Deployment Candidates & SHA-256 Hashes

| Artifact | Version | File Size | SHA-256 Checksum |
| :--- | :--- | :--- | :--- |
| **Theme** | `0.13.0-rc.8` | 4,105,097 bytes | `1ed203cd1eef37b77446bb8991b6c9e77540686c126dfb838d9c8602e60d9acc` |
| **Core Plugin** | `0.13.0-rc.11` | 76,933 bytes | `0d227bb96221e8c31c38fd05b785dd69b77ed84d0482dbe726bc33142a37cb72` |
| **Client Demo** | `0.2.1` | 4,053,008 bytes | `0f3996503646738825ca4148e3332a04dc00183087cacbfdef68a53077a091bf` |
| **Fixture** | `0.3.3` | 27,311 bytes | `97fbb481613fc619434e87b5d81fb3815ab7b690bd2d1e80e94dbf547ec70850` |

*Note: In accordance with repository rules, no authenticated mutations, live deployments, or ZIP uploads have been performed on WordPress.com Atomic.*
