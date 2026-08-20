# Statement Collector — Sprint 10 Visual Defect Register & Resolution Matrix

## Overview
This register documents all visual, typographic, interaction, and architectural defects identified prior to and during Sprint 10, accompanied by their root cause analysis and permanent resolution in Theme `0.13.0-rc.20`, Core `0.13.0-rc.15`, and Fixtures `0.3.5`.

---

## Defect Resolution Inventory

### 1. Fixture Tool Version Header Anomaly (Severity: HIGH)
- **Defect**: Package `statement-integration-fixtures-0.3.4.zip` displayed `Statement Integration Fixtures (v0.3.3)` in the WordPress admin panel heading.
- **Root Cause**: `tools/statement-integration-fixtures/src/AdminPage.php:133` contained a hardcoded HTML string `<h1>Statement Integration Fixtures (v0.3.3)</h1>` instead of referencing `STATEMENT_INTEGRATION_FIXTURES_VERSION`. Additionally, `StatementQaGateway.php:23` held `public const VERSION = '0.3.3'`.
- **Resolution**:
  - Refactored `AdminPage.php` to render `sprintf( 'Statement Integration Fixtures (v%s)', esc_html( STATEMENT_INTEGRATION_FIXTURES_VERSION ) )`.
  - Bumped fixture plugin version to canonical `0.3.5`.
  - Synchronized `statement-integration-fixtures.php`, `StatementQaGateway.php`, `package-fixtures.mjs`, `verify-fixture-package.mjs`, and tests.

### 2. Desktop Hero Facial Crop & Composition Imbalance (Severity: HIGH)
- **Defect**: Desktop hero slider displayed awkward close-up cropping of models' eyes and forehead on wide monitors (1440px/1920px), obscuring garment silhouettes and background architecture.
- **Root Cause**: Hero images lacked explicit safe-area object focal points and were constrained by unadjusted CSS height rules.
- **Resolution**:
  - Rebuilt hero slide dataset to utilize wide 16:9 / 21:9 landscape campaign photography (`statement-hero-slide-monogram-arch.jpg`, `statement-hero-slide-monogram-golden.jpg`, `statement-hero-slide-hood-arch.jpg`).
  - Added explicit focal point positioning (`object-position: center 20%` / `center 25%`) and vertical viewport clamping (`clamp(34rem, 78svh, 54rem)`).

### 3. Hero Slider Pagination Styling (Severity: MEDIUM)
- **Defect**: Hero pagination used generic dot indicators and bulky arrow controls that degraded the luxury feel.
- **Root Cause**: Legacy carousel CSS relied on dot styling without fine-line animation mechanics.
- **Resolution**:
  - Implemented an editorial fine-line progress rail (`.statement-hero-slider__rail-segment` and `.statement-hero-slider__rail-fill`) with 350ms cubic-bezier progress indicator.
  - Added a monospace numerical slide counter (`01 / 03`).
  - Replaced bulky buttons with tactile, glassmorphic hairline arrow micro-controls.

### 4. Drop Page Presentation Weakness (Severity: HIGH)
- **Defect**: Drop taxonomy archive (`taxonomy-statement_drop.php`) displayed as a plain, uninspiring text table where product images were restricted to tiny 80px hover thumbnails.
- **Root Cause**: The template lacked full-scale product card integration.
- **Resolution**:
  - Re-architected `taxonomy-statement_drop.php` into a unified Release Dossier combining:
    1. Monolithic Drop Masthead & Spec Sheet.
    2. Lookbook Diptych Grid featuring full 4:5 vertical product cards with secondary image hover reveals.
    3. Monospace Collection Register summary.

### 5. Inline JavaScript in Size Guide Template (Severity: MEDIUM)
- **Defect**: `template-parts/product/size-guide.php` included an inline `<script>` block, violating strict CSP policies and modern WordPress theme standards.
- **Root Cause**: Quick modal listener prototype was embedded directly within the template part.
- **Resolution**:
  - Removed all inline `<script>` tags from `size-guide.php`.
  - Moved accessible dialog management (open, close, Escape key, backdrop click, focus trapping) into `assets/js/product.js`.

### 6. Product Detail Page (PDP) Mobile Sticky CTA Bar (Severity: MEDIUM)
- **Defect**: On mobile viewports, after scrolling past the product details, the customer lost visibility of the Add to Bag action and price.
- **Root Cause**: Lack of dynamic viewport scroll detection and safe-area anchored bottom bar.
- **Resolution**:
  - Engineered `.statement-mobile-sticky-bar` in `assets/js/product.js` and `assets/css/product.css` with IntersectionObserver detection and iOS bottom safe-area padding (`env(safe-area-inset-bottom)`).

### 7. Secondary Image Hover in Product Cards (Severity: LOW)
- **Defect**: Product catalog cards only showed primary static images on desktop hover.
- **Root Cause**: `template-parts/product/card.php` did not query gallery attachment IDs.
- **Resolution**:
  - Added gallery image resolution in `card.php` (`get_gallery_image_ids`) to render a secondary preview image that smoothly reveals on `@media (hover: hover)`.
