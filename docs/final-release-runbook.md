# Final Release Runbook — Statement Production Cutover

## 1. Overview

This document specifies the exact sequential cutover procedure to transition the Statement WordPress repository from release candidate validation on Atomic to live production launch.

---

## 2. Complete Sequential Cutover Protocol

```
Step 1: M13 Runtime Verification Closeout
   ↓
Step 2: Post-RC Full System Backup (Files & Database)
   ↓
Step 3: Jetpack Post-RC Security Scan
   ↓
Step 4: Execute Fixture Cleanup (Admin -> Statement Fixtures -> Purge Test Records)
   ↓
Step 5: Deactivate & Delete statement-integration-fixtures Plugin
   ↓
Step 6: Deactivate & Remove Elementor & Unused Plugin Dependencies
   ↓
Step 7: Smoke Test Frontend Routes (Home, Shop, Archive, Cart, Checkout, My Account)
   ↓
Step 8: Configure Production Settings (WooPayments Live, Australia Post Shipping, Legal Policies)
   ↓
Step 9: Import Drop 001 Production Products & Media (Private Access State)
   ↓
Step 10: Production Live-Card Test Checkout ($1 Controlled Real AUD Transaction)
   ↓
Step 11: Refund Test Transaction & Verify Order Provenance Snapshot
   ↓
Step 12: Promote to Stable Release (Tag v1.0.0 in Git)
   ↓
Step 13: Final Pre-Launch Database Backup
   ↓
Step 14: Enable Public Indexing & Launch Private Access Gate
```

---

## 3. Detailed Stage Instructions

### Stage 1: Backup & Security Verification
1. **Full Backup**: In WordPress.com Hosting Dashboard, trigger an immediate point-in-time backup labeled `pre-launch-rc-clean`.
2. **Security Scan**: Verify Jetpack Scan shows 0 security threats and 0 modified core files.

### Stage 2: Fixture Uninstallation & Legacy Cleanup
1. **Purge Test Data**:
   - In WP Admin -> Statement Fixtures -> Click "PURGE TEST FIXTURES".
   - Confirm all test products (`TEST-*`) and drop (`test-private-drop-01`) are permanently deleted.
2. **Deactivate Fixtures**:
   - In WP Admin -> Plugins -> Deactivate `Statement Integration Fixtures` -> Delete Plugin.
3. **Deactivate Legacy Plugins**:
   - Deactivate `Elementor` and `Elementor Image Optimization`.
   - Smoke test homepage `/` to confirm pure theme rendering without Elementor dependencies.

### Stage 3: Commerce & Shipping Setup
1. **Payment**:
   - Connect WooPayments / Stripe live account.
   - Set currency to AUD ($).
   - Ensure QA Gateway is absent.
2. **Shipping**:
   - Add Shipping Zone: Australia.
   - Add Standard Flat Rate ($15 AUD) and Express Flat Rate ($25 AUD).
3. **Legal Pages**:
   - Publish static pages for Privacy Policy, Terms & Conditions, Refund & Returns, and Shipping Policy.
   - Ensure links in footer point to published pages.

### Stage 4: Production Drop Import & Real Checkout Test
1. **Import Products**: Follow [`docs/production-product-import-plan.md`](file:///C:/Users/ADMIN/OneDrive/Desktop/statement-store/docs/production-product-import-plan.md).
2. **Execute Live Card Test**:
   - Perform 1 live checkout with a real credit card for an authorized test drop item.
   - Verify transaction appears in WooPayments dashboard.
   - Verify Order Received page and Order audit table record complete frozen provenance snapshot.
   - Process immediate refund via WooPayments dashboard.

### Stage 5: Final Production Launch
1. **Git Tagging**:
   - Create Git tag `v1.0.0` on `main` branch.
   - Push tag to GitHub: `git push origin v1.0.0`.
2. **Search Visibility**:
   - In WP Admin -> Settings -> Reading: Ensure "Search engine visibility" allows indexing for public catalog. (Private Access Drops remain automatically protected by `noindex` headers).
3. **Launch Private Access Gate**:
   - Send Drop 001 launch communication.
