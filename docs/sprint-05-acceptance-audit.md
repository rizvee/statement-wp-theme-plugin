# Sprint 05.1 Acceptance Hardening & Forensic Ownership Audit

**Date:** 2026-08-18  
**Release Candidates:**
- **Statement Collector Theme:** `0.13.0-rc.10`
- **Statement Collector Core Plugin:** `0.13.0-rc.13`
- **Statement Client Demo Tool:** `0.2.3`
- **Statement Starter Child Theme:** `0.1.0`

---

## 1. Executive Summary

Sprint 05.1 acceptance hardening and Sprint 06 production convergence addressed critical architectural boundaries across product ownership, High-Performance Order Storage (HPOS), page builder extensibility, and theme/options security. All 42 project PHP test suites and 19 milestone automated test suites passed 100% cleanly.

---

## 2. Forensic Ownership Precedence & Product 213 Resolution

### Root Cause Analysis
Previously, when evaluating product ownership, presence of `_statement_client_demo = 1` was evaluated without strict negative guards against `_statement_fixture = 1` or `TEST-*` identifiers. In a mixed state (e.g. Product 213 bearing both fixture markers and an accidental demo marker), the product could be misclassified or partially adopted by demo routines.

### Fix & Verification
1. **Precedence Hierarchy in Core Visibility (`Visibility::is_fixture_product`):**
   - Step 1: `_statement_fixture = '1'` -> returns `true` (QA Fixture).
   - Step 2: SKU begins with `TEST-` -> returns `true` (QA Fixture).
   - Step 3: Title begins with `TEST —` / `TEST -` / `TEST:` -> returns `true` (QA Fixture).
   - Step 4: Slug begins with `test-` -> returns `true` (QA Fixture).
   - Step 5: `_statement_client_demo = '1'` ONLY recognized if none of the above are true AND SKU begins with `STMT-CD-`.

2. **Public Drop State Isolation (`PublicApi::get_drop_state`):**
   - Drop release state evaluation strictly filters out all fixture products before calculating lifecycle state. QA fixtures can never cause an `ARCHIVED` or `SOLD_OUT` drop to appear `LIVE`.

3. **Deterministic Ownership Classifier (`OwnershipClassifier::classify`):**
   - Implemented in `Statement\ClientDemo\OwnershipClassifier`.
   - Returns 5 discrete statuses:
     - `QA_FIXTURE`: Explicit fixture metadata or `TEST` namespace.
     - `CLIENT_DEMO`: Standalone demo metadata and `STMT-CD-*` SKU.
     - `PRODUCTION`: Organic store product without test/demo markers.
     - `CONFLICT`: Mixed fixture and demo markers (Presentation blocked, adoption blocked, mutation blocked).
     - `UNKNOWN`: Invalid or missing ID.

4. **Client Demo Repair Action:**
   - Dedicated "Repair Client Demo Ownership" action decouples accidental demo markers from QA fixtures and clears duplicate entries from `statement_client_demo_manifest_v2`.

---

## 3. High-Performance Order Storage (HPOS) Correct Ownership

### Architectural Invariant
- **Core Plugin Responsibility:** Statement Collector Core plugin owns order lifecycle, provenance snapshotting, order audit logging, admin order views, customer order views, and email integrations. All order operations rely strictly on WooCommerce CRUD (`wc_get_order()`, `$order->get_meta()`, `$order->update_meta_data()`, `$order->save()`) with zero raw `shop_order` postmeta queries.
- **HPOS Declaration:** Declared exclusively in `statement-collector-core.php` on `before_woocommerce_init` via `\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', STATEMENT_COLLECTOR_CORE_FILE, true)`.
- **Theme Responsibility:** Theme `inc/compatibility/woocommerce.php` manages presentation, gallery zoom/lightbox/slider, and responsive grid columns via `add_theme_support('woocommerce')`. Theme does NOT register plugin order table compatibility.

---

## 4. Elementor & Page Builder Extensibility

1. **Front Page Renderer Modes (`statement_front_page_renderer`):**
   - `statement` (Default): Curated Statement Collector Piece editorial homepage (`front-page.php`).
   - `content`: Standard static page content renderer (`page.php` / builder canvas), enabling Elementor and Gutenberg page building on the front page.
2. **Theme Builder Locations:**
   - Registers official Elementor locations (`header`, `footer`, `single`, `archive`).
   - Added extensibility filter `statement_theme_register_elementor_locations` (default `true`).
3. **Standalone Builder Templates:**
   - `template-full-width.php`: Full-width canvas retaining theme header and footer.
   - `template-canvas.php`: Clean canvas without header/footer for dedicated landing pages.

---

## 5. Security & Validation Audit

- **Theme Options Import/Export (`OptionsExport`):**
  - Requires `manage_options` capability.
  - Nonce verification on all admin forms.
  - Strict key allow-list (rejects arbitrary keys / option injection).
  - Type coercion (hex colors, positive integers, strict booleans, allowed enums).
- **Page Meta Overrides (`PageMeta`):**
  - Nonce verification (`statement_page_meta_nonce_action`).
  - `edit_post` capability check.
  - Rejects autosaves.
  - Strict allow-lists for layout, header, footer, and title values.

---

## 6. Verification Summary

- **PHP Lint:** 119/119 PHP files passed.
- **Unit & Integration PHP Suites:** 42/42 PHP test files passed.
- **Node Milestone Suites (M1–M19):** 19/19 suites passed (140+ assertions).
- **Packaging Integrity:** Validated ZIPs and SHA-256 manifests generated in `dist/`.
