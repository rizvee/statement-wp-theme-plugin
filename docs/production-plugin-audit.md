# Production Plugin Stack Audit — M15 Launch Readiness

## 1. Executive Summary

Statement Collector's Piece utilizes a bespoke standalone WordPress theme (`statement-collector-theme`) and core domain plugin (`statement-collector-core`). It does NOT rely on page builders, commercial UI frameworks, or extraneous third-party tooling.

This audit classifies all active and historical plugins on the WordPress.com Atomic hosting environment into **KEEP**, **REMOVE AFTER MIGRATION**, or **REVIEW / CONFIGURE**.

---

## 2. Plugin Stack Classification Matrix

| Plugin Name | Classification | Rationale & Guidance |
| --- | --- | --- |
| **Statement Collector Core** (`statement-collector-core`) | **KEEP (CRITICAL)** | Primary source of truth for release state taxonomy, private access, cryptography, cart/checkout integrity, terminal scarcity invariant, and collector provenance. |
| **Statement Collector's Piece Theme** (`statement-collector-theme`) | **KEEP (CRITICAL)** | Bespoke standalone theme providing luxury typography, editorial layouts, responsive cart/checkout overrides, and accessible dialogs without framework bloat. |
| **WooCommerce** (`woocommerce`) | **KEEP (CRITICAL)** | Standard open-source e-commerce engine powering product objects, orders, line items, and customer accounts. |
| **WooPayments** (`woocommerce-payments`) | **KEEP (COMMERCE)** | Primary payment processor for card, Apple Pay, and Google Pay transactions in AUD. |
| **Statement Integration Fixtures** (`statement-integration-fixtures`) | **REMOVE AFTER MIGRATION** | Temporary administrator-only test tool and offline QA gateway. Must be deactivated and uninstalled before live customer access. |
| **Elementor** (`elementor`) | **REMOVE AFTER MIGRATION** | Redundant. Statement theme handles all frontend presentation natively. Elementor introduces severe performance overhead, template conflicts, and canvas hijacking. |
| **Elementor Pro** (`elementor-pro`) | **REMOVE AFTER MIGRATION** | Redundant. Same rationale as core Elementor. |
| **Elementor Image Optimization** | **REMOVE AFTER MIGRATION** | Redundant once Elementor is removed. Native WordPress responsive image sizes (`srcset`) and WebP support are sufficient. |
| **Page Optimize** | **REVIEW** | Host-level asset optimization tool. Test thoroughly to ensure script concatenation does not break vanilla navigation JavaScript or dialog bindings. |
| **Jetpack** (`jetpack`) | **REVIEW / CONFIGURE** | Host-integrated features (stats, security, backup). Must be configured so stats integrations do not serialize private request slugs. |
| **Akismet Anti-spam** (`akismet`) | **KEEP / REVIEW** | Standard WordPress comment/form spam protection. Safe to retain if spam filtering is active. |
| **MailPoet** (`mailpoet`) | **REVIEW** | Only retain if operator actively utilizes MailPoet for broadcast newsletter campaigns. Not required for core Private Access or transactional emails. |
| **Google for WooCommerce** (`google-listings-and-ads`) | **REVIEW** | Evaluate whether public product syndication to Google Shopping is desired. Note: Ensure PRIVATE_ACCESS and ARCHIVED products are strictly excluded. |
| **WooCommerce Tax** (`woocommerce-tax`) | **REVIEW / CONFIGURE** | Standard automated Australian GST (10%) calculation. Verify prices entered inclusive of tax. |

---

## 3. Recommended Migration Actions Prior to Public Launch

1. **Deactivate and delete Elementor & Elementor Pro**.
2. **Deactivate and delete Elementor Image Optimization**.
3. **Verify WordPress reading settings** (Ensure Homepage is set to the static front page using the default theme template).
4. **Deactivate and uninstall `statement-integration-fixtures`** following the execution of `docs/final-fixture-cleanup.md`.
5. **Re-test all 9 primary storefront routes** after plugin stack streamlining.
