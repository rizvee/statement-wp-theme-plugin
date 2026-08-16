# Production Plugin Stack Audit & Staged Reduction Plan — M15 Launch Readiness

## 1. Executive Summary

Statement Collector's Piece utilizes a bespoke standalone WordPress theme (`statement-collector-theme`) and core domain plugin (`statement-collector-core`). It does NOT rely on page builders, commercial UI frameworks, or extraneous third-party tooling.

This audit classifies all active and historical plugins on the WordPress.com Atomic hosting environment into **KEEP**, **REMOVE AFTER MIGRATION**, or **REVIEW / CONFIGURE**, and outlines the exact step-by-step reduction order with post-deactivation smoke testing.

---

## 2. Plugin Stack Classification Matrix

| Plugin Name | Classification | Rationale & Guidance |
| --- | --- | --- |
| **Statement Collector Core** (`statement-collector-core`) | **KEEP (CRITICAL)** | Primary source of truth for release state taxonomy, private access, cryptography, cart/checkout integrity, terminal scarcity invariant, and collector provenance. |
| **Statement Collector's Piece Theme** (`statement-collector-theme`) | **KEEP (CRITICAL)** | Bespoke standalone theme providing luxury typography, editorial layouts, responsive cart/checkout overrides, and accessible dialogs without framework bloat. |
| **WooCommerce** (`woocommerce`) | **KEEP (CRITICAL)** | Standard open-source e-commerce engine powering product objects, orders, line items, and customer accounts. |
| **WooPayments** (`woocommerce-payments`) | **KEEP (COMMERCE)** | Primary payment processor for card, Apple Pay, and Google Pay transactions in AUD. |
| **Statement Integration Fixtures** (`statement-integration-fixtures`) | **REMOVE AFTER MIGRATION** | Temporary administrator-only test tool and offline QA gateway. Must be deactivated and uninstalled before live customer access. |
| **Elementor** (`elementor`) | **REMOVE AFTER MIGRATION** | Redundant. Statement theme handles all frontend presentation natively. Elementor introduces performance overhead and canvas hijacking. Stored Real Home page in database is unaffected by plugin removal. |
| **Elementor Pro** (`elementor-pro`) | **REMOVE AFTER MIGRATION** | Redundant. Same rationale as core Elementor. |
| **Elementor Image Optimization** | **REMOVE AFTER MIGRATION** | Redundant once Elementor is removed. Native WordPress responsive image sizes (`srcset`) and WebP support are sufficient. |
| **Gutenberg Plugin** (`gutenberg`) | **REMOVE AFTER FINAL QA** | Beta/preview plugin. WordPress Core 6.6+ natively includes all required block editor functionality. Removing reduces plugin attack surface. |
| **Page Optimize** | **KEEP / REVIEW** | Host-level asset optimization tool. Verified safe: private routes dynamically emit `Cache-Control: private, no-store, no-cache, max-age=0` and `Vary: Cookie` preventing edge cache leakage. |
| **Jetpack** (`jetpack`) | **KEEP / CONFIGURE** | Host-integrated features (stats, security, backup). Core rc.9 scrubs WordPress request state and `REQUEST_URI` before footer integrations. |
| **Akismet Anti-spam** (`akismet`) | **KEEP / REVIEW** | Standard WordPress comment/form spam protection. Safe to retain if spam filtering is active. |
| **MailPoet** (`mailpoet`) | **REVIEW / OPTIONAL** | Only retain if operator actively utilizes MailPoet for broadcast newsletter campaigns. Not required for core Private Access or transactional emails. |
| **Google for WooCommerce** (`google-listings-and-ads`) | **REVIEW** | Evaluate whether public product syndication to Google Shopping is desired. Ensure PRIVATE_ACCESS and ARCHIVED products are strictly excluded. |
| **WooCommerce Tax** (`woocommerce-tax`) | **KEEP / CONFIGURE** | Automated Australian GST (10%) calculation. Prices entered inclusive of tax. |

---

## 3. Elementor Independence Evidence

A full repository audit of all PHP, JavaScript, and CSS source files in `wp-content/themes/statement-collector-theme/` and `wp-content/plugins/statement-collector-core/` confirms:
- **0** Elementor PHP classes, traits, or functions referenced.
- **0** Elementor action or filter hooks used.
- **0** Elementor CSS selectors, stylesheet dependencies, or font assets enqueued.
- **100%** native WordPress template hierarchy (`front-page.php`, `page-archive.php`, `single-product.php`, `header.php`, `footer.php`).

The legacy `Real Home` page created in Elementor remains stored safely in the WordPress database drafts and does NOT affect theme execution.

---

## 4. Page Optimize Cache & Asset Safety Analysis

1. **Edge Cache Isolation**:
   - `statement-collector-core` explicitly sends `Cache-Control: private, no-store, no-cache, max-age=0, must-revalidate` and `Vary: Cookie` on all private Drop gates, private PDPs, Cart, and Checkout routes.
   - Page Optimize HTML/asset caching respects private cache headers and does not cache personalized private access sessions.
2. **Script Concatenation Safety**:
   - The theme uses vanilla ES6 JavaScript (`navigation.js`, `search-dialog.js`) without external JS framework dependencies or inline script blocks, ensuring concatenation passes cleanly without execution order bugs.

---

## 5. Staged Reduction Order & Verification Protocol

Execute deactivations one-by-one, running `node scripts/test-production-readiness.mjs` after each stage:

```
Stage 1: Purge & Uninstall Statement Integration Fixtures
         └── Run: node scripts/verify-production-clean-state.mjs (Assert CLEAN)
         └── Run: node scripts/test-production-readiness.mjs (Assert 10/10 PASS)

Stage 2: Deactivate & Delete Elementor, Elementor Pro, & Elementor Image Optimization
         └── Run: node scripts/test-production-readiness.mjs (Assert 10/10 PASS)

Stage 3: Deactivate & Delete Gutenberg Standalone Plugin
         └── Run: node scripts/test-production-readiness.mjs (Assert 10/10 PASS)

Stage 4: Evaluate Optional Stack (MailPoet, Google for WooCommerce)
         └── Run: node scripts/test-production-readiness.mjs (Assert 10/10 PASS)
```
