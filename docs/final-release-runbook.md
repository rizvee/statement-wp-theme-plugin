# Final Release Runbook — Statement Production Cutover Protocol

## 1. Overview

This document specifies the exact sequential cutover procedure to transition the Statement WordPress repository from release candidate validation on Atomic to live production launch.

---

## 2. Complete Sequential Cutover Protocol

```
Step 1: M13 Runtime Verification Closeout
   ↓
Step 2: Verify Fresh Automatic Jetpack / WordPress.com Backup Restore Point (Post-dating Final QA)
   ↓
Step 3: Trigger & Confirm Clean Jetpack Security Scan
   ↓
Step 4: Execute Fixture Cleanup (Admin -> Statement Fixtures -> Purge Test Records)
   ↓
Step 5: Deactivate & Delete statement-integration-fixtures Plugin
   ↓
Step 6: Post-Purge Verification (Run: scripts/verify-production-clean-state.mjs)
   ↓
Step 7: Staged Plugin Streamlining (Elementor, Elementor Image Optimization, Gutenberg plugin)
   ↓
Step 8: Storefront Readiness Smoke Test (Run: scripts/test-production-readiness.mjs)
   ↓
Step 9: Configure Production Settings (WooPayments Live AUD, Australia Post Shipping, Legal Policies)
   ↓
Step 10: Import Drop 001 Production Products & Media (Private Access State)
   ↓
Step 11: Production Live-Card Test Checkout ($1 Controlled Real AUD Transaction)
   ↓
Step 12: Refund Test Transaction & Verify Order Provenance Snapshot
   ↓
Step 13: Final Pre-Launch Database Backup
   ↓
Step 14: Stable Version Release Decision (Tag v1.0.0 in Git)
   ↓
Step 15: Enable Public Indexing & Launch Private Access Gate
```

---

## 3. Detailed Stage Instructions

### Stage 1: Backup & Security Verification
1. **Automatic Backup Verification**:
   - In WordPress.com Dashboard -> Jetpack -> Backup: Confirm an automatic point-in-time restore point exists with a timestamp **after** the latest RC deployment and final QA mutations. Record the backup timestamp and confirm rollback readiness.
2. **Fresh Security Scan**:
   - In Jetpack -> Security: Trigger a fresh scan and confirm zero threats post-dating current release candidates.

### Stage 2: Fixture Uninstallation & Legacy Cleanup
1. **Purge Test Data**:
   - In WP Admin -> Statement Fixtures -> Click "PURGE TEST FIXTURES".
   - Confirm all test products (`TEST-*`) and drop (`test-private-drop-01`) are permanently deleted.
2. **Deactivate Fixtures**:
   - In WP Admin -> Plugins -> Deactivate `Statement Integration Fixtures` -> Delete Plugin.
3. **Verify Clean State**:
   - Run `node scripts/verify-production-clean-state.mjs` to assert `CLEAN (READY)`.

### Stage 3: Staged Plugin Streamlining
1. **Deactivate Elementor**:
   - Deactivate `Elementor`, `Elementor Pro`, and `Elementor Image Optimization`.
   - Run `node scripts/test-production-readiness.mjs` (Assert 10/10 routes pass).
2. **Deactivate Gutenberg Standalone Plugin**:
   - Deactivate `Gutenberg` development plugin (WordPress Core 6.6+ natively provides full block editor).
   - Run `node scripts/test-production-readiness.mjs` (Assert 10/10 routes pass).

### Stage 4: Commerce & Shipping Setup
1. **Payment**:
   - Complete WooPayments / Stripe live onboarding in AUD.
   - Verify QA Gateway is absent.
2. **Shipping**:
   - Configure Australia Shipping Zone with Australia Post flat rates.
3. **Legal Pages**:
   - Publish static pages for Privacy Policy, Terms & Conditions, Refund & Returns, and Shipping Policy using [`docs/legal-content-input.md`](file:///c:/Users/ADMIN/OneDrive/Desktop/statement-store/docs/legal-content-input.md).

### Stage 5: Production Drop Import & Real Checkout Test
1. **Import Products**: Follow [`docs/production-product-import-plan.md`](file:///c:/Users/ADMIN/OneDrive/Desktop/statement-store/docs/production-product-import-plan.md) and [`config/drop-001.example.json`](file:///c:/Users/ADMIN/OneDrive/Desktop/statement-store/config/drop-001.example.json).
2. **Execute Live Card Test**:
   - Perform 1 live checkout with a real credit card for an authorized test drop item ($1 AUD).
   - Verify transaction appears in WooPayments dashboard.
   - Verify Order Received page and Order audit table record complete frozen provenance snapshot.
   - Process immediate full refund via WooPayments dashboard.

### Stage 6: Final Production Launch
1. **Stable Release Decision**:
   - If current candidates survive live production checkout cleanly, create Git tag `v1.0.0` on `main`.
   - Push tag to GitHub: `git push origin v1.0.0`.
2. **Search Visibility**:
   - In WP Admin -> Settings -> Reading: Ensure "Search engine visibility" allows indexing for public catalog. (Private Access Drops remain automatically protected by `noindex` headers).
3. **Launch Private Access Gate**:
   - Send Drop 001 launch communication.
