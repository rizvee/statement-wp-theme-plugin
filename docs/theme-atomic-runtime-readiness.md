# Statement Theme Atomic Runtime-Readiness Audit & Runbook

## 1. Overview & Scope Boundary

This document records the local runtime-readiness audit and verification results for `statement-collector-theme` (`0.13.0-rc.2`) prior to its initial activation on the target WordPress.com Atomic environment (`mystatement.store`).

> [!IMPORTANT]
> **LOCAL PREPARATION ONLY — NO ATOMIC UPLOAD/ACTIVATION:** This phase performs local code auditing, contract verification, PHP 8.4 safety checks, and template override integrity validation. The Statement Theme MUST NOT be uploaded or activated on Atomic until explicit user/ChatGPT activation gate approval is granted.

---

## 2. Target Runtime Environment Baseline

- **WordPress Platform Version:** `7.0.4` (observed on Atomic diagnostic output)
- **PHP Engine Version:** `8.4.23` (observed on Atomic diagnostic output)
- **WooCommerce Core Version:** `11.0.1` (`wp-content/plugins/woocommerce/`)
- **Statement Collector Core Version:** `0.13.0-rc.2` (ACTIVE on Atomic)
- **WooCommerce Store Currency:** `AUD` (Confirmed set during Phase 3A)
- **Active Theme:** `Assembler` (`wp-content/themes/assembler/` — **UNCHANGED**)
- **Preserved Content:** `Real Home` (Page ID 53, built with Elementor)

---

## 3. Real Integration Fixture Semantic Matrix

The theme presentation layer consumes the current live integration fixture dataset created in Phase 3A:

| Product ID | Name | SKU | Woo Type | Statement Release State | Price | Woo Stock | Expected Theme Presentation |
|------------|------|-----|----------|-------------------------|-------|-----------|-----------------------------|
| **198** | `TEST — Monogram Jacket` | `TEST-LD01-MJ` | `variable` | `LIVE` | AUD 295 | 2 each (S/M/L) | Rendered in Shop/Home loop; variation size form (S/M/L), price, Add to Bag button. |
| **202** | `TEST — Studio Overshirt` | `TEST-LD01-SO` | `simple` | `LIVE` | AUD 240 | 5 (`instock`) | Rendered in Shop/Home loop; editorial summary, price, Add to Bag button. |
| **203** | `TEST — Terminal Jacket` | `TEST-LD01-TJ` | `simple` | `SOLD_OUT` | AUD 275 | 5 (`instock`) | Rendered in Shop (after LIVE) & Drop loops; **SOLD OUT badge**, **NO Add to Bag form**, **NO quantity input**, **NO restock/waitlist messaging**. |

> [!NOTE]
> **Terminal Lock Protection:** Product 203 retains positive WooCommerce stock (5 / `instock`), but Statement Core's `woocommerce_is_purchasable` filter returns `false`. `summary.php` and `card.php` display `SOLD OUT` badges and omit purchase controls.

---

## 4. Public API Contract Audit

Every static method called by `statement-collector-theme` was verified against `Statement\Collector\Core\PublicApi` in `statement-collector-core`:

| Theme Call | Core PublicApi Method | Status | Contract Guarantee |
|------------|------------------------|--------|-------------------|
| `PublicApi::get_drop( $product )` | `public static function get_drop( $product )` | **VERIFIED** | Returns canonical Drop term or `null`. |
| `PublicApi::get_edition_label( $product )` | `public static function get_edition_label( $product )` | **VERIFIED** | Returns sanitized edition string or `''`. |
| `PublicApi::get_release_state( $product )` | `public static function get_release_state( $product )` | **VERIFIED** | Returns `UPCOMING`, `PRIVATE_ACCESS`, `LIVE`, `SOLD_OUT`, or `ARCHIVED`. |
| `PublicApi::is_publicly_live( $product )` | `public static function is_publicly_live( $product )` | **VERIFIED** | Returns `true` iff release state is `LIVE`. |
| `PublicApi::get_archive_products( $limit )` | `public static function get_archive_products( int $limit = 12 )` | **VERIFIED** | Returns array of `ARCHIVED` WooCommerce products. |
| `PublicApi::get_past_drops()` | `public static function get_past_drops()` | **VERIFIED** | Returns array of `statement_drop` terms with strictly `ARCHIVED` items. |

*(All calls are guarded with `class_exists('Statement\Collector\Core\PublicApi')` or `class_exists(PublicApi::class)`; if Core is unbooted, theme falls back safely without fatal errors).*

---

## 5. WooCommerce Template Overrides

The theme maintains strictly **four** WooCommerce template overrides:

1. `woocommerce/content-product.php` (Woo version 9.4.0) — Minimal product loop card wrapper.
2. `woocommerce/content-single-product.php` (Woo version 3.6.0) — Single product layout structure.
3. `woocommerce/cart/cart.php` (Woo version 11.0.0) — Classic Cart form and layout override.
4. `woocommerce/checkout/form-checkout.php` (Woo version 9.4.0) — Classic Checkout form layout.

---

## 6. PHP 8.4 & WP 7 Safety Audit Findings

- **Implicit Nullable Parameters:** Zero implicit nullable parameters.
- **Dynamic Properties:** Zero dynamic property declarations.
- **Null String Functions:** All `trim()`, `esc_*()`, `strlen()`, `preg_replace()` parameters are guarded with `is_string()` or cast safely.
- **WP Hooks:** Layout files include `wp_head()`, `wp_footer()`, and `wp_body_open()`.
- **Scarcity Invariants:** Zero references to serial numbers, collector numbers, edition counts (`200 pieces`), restock, or waitlist messaging.

---

## 7. Future Controlled Activation Workflow

When activation is authorized in a future task, follow this exact sequence:

1. Record current active theme (`Assembler`) and confirm Elementor `Real Home` (ID 53) remains untouched.
2. Operator manually uploads `statement-collector-theme` ZIP in normal authenticated Chrome browser.
3. **DO NOT activate immediately.** Verify uploaded theme version on Plugins/Themes screen.
4. Obtain explicit human activation gate approval.
5. Activate `Statement Collector Theme`.
6. Perform immediate read-only route smoke testing:
   - `/` (Home page)
   - `/shop/` (Shop page)
   - `/drop/test-live-drop-01/` (Drop page)
   - `/product/test-studio-overshirt/` (LIVE simple product)
   - `/product/test-monogram-jacket/` (LIVE variable product)
   - `/product/test-terminal-jacket/` (SOLD OUT product)
   - `/cart/` (Cart page)
   - `/checkout/` (Checkout page)
   - `/my-account/` (Account page)
7. If any critical failure occurs, **immediately reactivate Assembler theme**.
