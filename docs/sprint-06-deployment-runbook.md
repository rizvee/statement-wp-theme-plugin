# Sprint 06 Production Deployment Runbook

**Target Production Domain:** `https://mystatement.store`
**Deployment Authority:** Manual Operator Upload via WordPress Admin (`wp-admin`)
**Hard Rule:** NO DIRECT OR AUTOMATED MUTATIONS TO LIVE PRODUCTION FROM WORKSPACE.

---

## 1. Candidate Package Artifacts

| Component | Target Version | Artifact File | Size | SHA-256 Checksum |
|---|---|---|---|---|
| **Theme** | `0.13.0-rc.10` | `dist/statement-collector-theme-0.13.0-rc.10.zip` | 4.13 MB | `cf63e2f3f54a3fb3e3e952de5cde9aa7e655d4c32c64cc43a17b4ac74ac2bbca` |
| **Core Plugin** | `0.13.0-rc.13` | `dist/statement-collector-core-0.13.0-rc.13.zip` | 78.2 KB | `94760ddbf49c974b36a7d28553f715a7d207fe7e8fb6359c9fa7143ce4933a25` |
| **Client Demo** | `0.2.3` | `dist/statement-client-demo-0.2.3.zip` | 4.05 MB | `0bc37da82b172622454ad2f3fb16607777ffd97cbc5fe4f13f13be6d837d69ca` |
| **Child Theme** | `0.1.0` | `dist/statement-collector-child-0.1.0.zip` | 1.44 KB | `8fd5d2323d51b37e16ef868a498635baf44a70abce0a45d7924ed710e06c978e` |

---

## 2. Pre-Deployment Operator Checklist

Before uploading ZIP packages:
1. Ensure a fresh full backup (database and `wp-content`) exists on the host.
2. Confirm WooCommerce (8.5+) and WordPress (6.4+) are active.
3. Verify that `WP_DEBUG_LOG` is accessible in `wp-content/debug.log`.

---

## 3. Step-by-Step Manual Deployment Sequence

### Step 1: Upload & Replace Core Plugin
1. Log into WordPress Admin -> **Plugins -> Add New Plugin -> Upload Plugin**.
2. Select `dist/statement-collector-core-0.13.0-rc.13.zip` and click **Install Now**.
3. Click **Replace current with uploaded** if prompted.
4. Verify active status: **Statement Collector Core v0.13.0-rc.13** active.

### Step 2: Upload & Replace Theme
1. Navigate to **Appearance -> Themes -> Add New Theme -> Upload Theme**.
2. Select `dist/statement-collector-theme-0.13.0-rc.10.zip` and click **Install Now**.
3. Click **Replace current with uploaded**.
4. Confirm **Statement Collector's Piece v0.13.0-rc.10** is the active theme (or active parent if using Child Theme).

### Step 3: (Optional) Upload Client Demo Seeder
1. If staging or client preview demo content is needed, upload `dist/statement-client-demo-0.2.3.zip`.
2. Go to **WooCommerce -> Client Demo**.
3. Review **Ownership Diagnostics** table to confirm Product 213 (if present) is recognized as `QA_FIXTURE` and not conflicted.
4. Click **Dry Run / Preview Actions** to verify planned operations before seeding.

---

## 4. Post-Deployment Verification & Smoke Tests

1. **Homepage:**
   - Verify luxury typography, hero slider, active Drop section, and email capture form render cleanly.
2. **Shop & Drops:**
   - Verify `/shop/` and `/drops/` load without errors.
   - Confirm only `LIVE` drop pieces are purchasable.
3. **WooCommerce Features & HPOS:**
   - Navigate to **WooCommerce -> Settings -> Advanced -> Features**.
   - Verify High-Performance Order Storage shows compatibility declared.
4. **Checkout & Bag:**
   - Test adding a live item to Bag, viewing Bag drawer/page, and progressing to Checkout.

---

## 5. Rollback Procedure

If unexpected runtime issues occur:
1. Re-upload previous known stable candidates:
   - Core Plugin: `dist/statement-collector-core-0.13.0-rc.12.zip`
   - Theme: `dist/statement-collector-theme-0.13.0-rc.9.zip`
2. If database rollback is needed, restore database snapshot from pre-deployment backup.
