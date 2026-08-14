# WordPress.com Atomic Integration Preflight Checklist

## Overview

This preflight checklist records evidence gathered from read-only inspection and controlled runtime testing of the target WordPress.com Atomic environment (`mystatement.store`).

> [!IMPORTANT]
> **Platform Auto-Activation Notice:** Custom plugin installation on this WordPress.com Atomic site automatically activates the plugin upon upload completion. All operator preflight checklists and rollback procedures MUST be fully verified before pressing Install/Upload.

---

## 1. WordPress Platform Baseline

- [x] WordPress version: `6.7.x` (WordPress.com Atomic platform)
- [x] PHP runtime version: `8.3.x` (Atomic platform)
- [x] Site Timezone: `Australia/Sydney`
- [x] Permalink Structure: `/%postname%/` (WordPress default structure)
- [x] Access Privacy State: **PUBLICLY REACHABLE / SEARCH INDEXING DISCOURAGED** (`<meta name='robots' content='noindex, nofollow' />` present; platform setting `blog_public = 0`)
- [!] Fixture Testing Gate: **M13-SAFETY-01 = BLOCKED FOR FIXTURE TESTING** (Controlled products, Drops, grants, or orders require true access-restricted/Coming Soon state).

---

## 2. WooCommerce Platform Configuration

- [x] WooCommerce core version: `11.0.1` (`wp-content/plugins/woocommerce/`)
- [x] WooPayments status: **ACTIVE** (`payments/woopay` REST namespace, `woocommerce-payments` asset loader)
- [x] Store Currency: `USD` (**Issue M13-CONFIG-01**: Must be confirmed/corrected to `AUD` prior to live payment/checkout validation)
- [x] Tax Configuration: Standard WooCommerce tax defaults
- [x] Shipping Configuration: Standard WooCommerce shipping defaults

---

## 3. Core Page Assignments

- [x] Home Page: Assigned to Page ID 53 (`Real Home` at `/`)
- [x] Shop Page: Assigned to Page ID 182 (`Shop` at `/shop/`)
- [x] Cart Page: Assigned to Page ID 183 (`Cart` at `/cart/`)
- [x] Checkout Page: Assigned to Page ID 184 (`Checkout` at `/checkout/`)
- [x] My Account Page: Assigned to Page ID 185 (`My account` at `/my-account/`)
- [ ] Archive Page: Pending future M13 phase

---

## 4. Theme & Plugin State

- [x] Active Theme: `Assembler` (`wp-content/themes/assembler/`) — **UNCHANGED**
- [x] Fallback Theme: Twenty Twenty-Four / Twenty Twenty-Three present on platform
- [x] Active Plugins: WooCommerce 11.0.1, WooPayments, Jetpack, Elementor 4.2.2, MailPoet, Akismet, WPComSH (Atomic mu-plugins)
- [x] Installed Core Plugin: `statement-collector-core` active at **Version 0.13.0-rc.2** (Replaced candidate `0.1.0`; verified matching header & constant).
- [x] Elementor / Legacy Content: **CONFIRMED INTACT & PRESERVED** (Homepage ID 53 built with Elementor)

---

## 5. Background Jobs & Cache

- [x] Action Scheduler: Available on platform via WooCommerce Core; operating normally
- [x] Edge / Server Cache: Atomic edge proxy caching active with `noindex` headers for private site

---

## 6. Private Access Configuration (wp-config.php)

- [ ] `STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION`: **NOT CONFIGURED** (Phase 2B runtime validation; no production secrets created)
- [ ] `STATEMENT_ACCESS_ENCRYPTION_KEYS`: **NOT CONFIGURED**
- [ ] `STATEMENT_ACCESS_IDENTITY_KEY`: **NOT CONFIGURED**
- [ ] `STATEMENT_ACCESS_RATE_LIMIT_KEY`: **NOT CONFIGURED**

*(Status: NOT CONFIGURED — zero production secrets created or exposed).*

---

## 7. Backup & Rollback Verification

- [x] Atomic site automatic backups active on WordPress.com platform
- [x] Previous theme (`Assembler`) and Elementor content remain untouched
- [x] Installed plugin directory `statement-collector-core` is canonical; version `0.13.0-rc.2` is active and operational.
