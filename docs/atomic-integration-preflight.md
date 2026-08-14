# WordPress.com Atomic Integration Preflight Checklist

## Overview

This preflight checklist must be completed by an operator prior to uploading any integration package to the target WordPress.com Atomic environment (`mystatement.store`).

---

## 1. WordPress Platform Baseline

- [ ] WordPress version: Record exact version (e.g. `6.7.x`)
- [ ] PHP runtime version: Record active PHP version (e.g. `8.3.x`)
- [ ] Site Timezone: Verify site timezone setting (e.g. `UTC` or local)
- [ ] Permalink Structure: Verify permalink structure (e.g. `/%postname%/`)
- [ ] Access Privacy State: Confirm site is Coming Soon / Private before testing

---

## 2. WooCommerce Platform Configuration

- [ ] WooCommerce core version: Record version (e.g. `9.4.x`)
- [ ] WooPayments status: Record active/test mode status
- [ ] Store Currency: Confirm currency code (e.g. `USD`)
- [ ] Tax Configuration: High-level status recorded
- [ ] Shipping Configuration: High-level status recorded

---

## 3. Core Page Assignments

- [ ] Home Page: Assignee recorded
- [ ] Shop Page: Assignee recorded
- [ ] Cart Page: Assigned classic cart page recorded
- [ ] Checkout Page: Assigned classic checkout page recorded
- [ ] My Account Page: Assignee recorded
- [ ] Archive Page: Assigned page with "Statement Archive" template recorded

---

## 4. Theme & Plugin State

- [ ] Active Theme: Record currently active theme (e.g. `twentytwentyfour` or custom)
- [ ] Fallback Theme: Confirm fallback theme remains installed and available
- [ ] Active Plugins: Record active plugin list
- [ ] Elementor / Legacy Content: Confirm existing homepage and Elementor content are preserved and safe

---

## 5. Background Jobs & Cache

- [ ] Action Scheduler: Verify pending/failed queue health
- [ ] Edge / Server Cache: Note Atomic server cache behavior and header rules

---

## 6. Private Access Configuration (wp-config.php)

- [ ] `STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION` configured?
- [ ] `STATEMENT_ACCESS_ENCRYPTION_KEYS` configured?
- [ ] `STATEMENT_ACCESS_IDENTITY_KEY` configured?
- [ ] `STATEMENT_ACCESS_RATE_LIMIT_KEY` configured?

*(Record CONFIGURED / NOT CONFIGURED status only — NEVER record actual secret key values in this document).*

---

## 7. Backup & Rollback Verification

- [ ] Atomic site backup confirmed available before upload
- [ ] Previous theme/plugin packages retained locally
