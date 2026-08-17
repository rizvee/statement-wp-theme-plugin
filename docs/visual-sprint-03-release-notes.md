# Visual Sprint 03 / Overnight Mega Build — Release Notes & Operator Runbook

**Release Date:** 2026-08-17  
**Artifacts Generated:**  
- **Theme:** `dist/statement-collector-theme-0.13.0-rc.7.zip` (548 KB, SHA-256: `687e51a2b31b87e5f407ea0982d10d593984590711055a3fccc563b0a14c7b09`)
- **Core Plugin:** `dist/statement-collector-core-0.13.0-rc.10.zip` (72.5 KB, SHA-256: `2aaa184bf3d1d00707eaeecc32cb79daf202e8ca550e99424a47e70bc920ea45`)
- **Client Demo Tool:** `dist/statement-client-demo-0.2.0.zip` (4.05 MB, SHA-256: `60eb50fbd1b7c6b5d20f1de366c54a89e0974a2d6d198f822cd7758f621a11b5`)

---

## 1. Executive Summary of Changes

### A. Strict Minimal Homepage Release Contract
The homepage layout is now locked to a focused 6-part minimal luxury contract:
1. **Header:** Typographical `STATEMENT` wordmark, editorial fallback navigation, and bag indicator.
2. **Current Offer / Hero:** Editorial statement hero with responsive focal positioning (`object-position: center top`) and 76svh viewport height.
3. **Current Drop Presentation:** Clean status banner and editorial introduction.
4. **Featured Pieces:** Restrained 2-piece product presentation.
5. **Email / Access Capture Module:** Integrated luxury email signup.
6. **Minimal Footer:** Wordmark, copyright, and navigation links.

*Note:* Off-home brand sections (*Study & Form* lookbook grid, *The Object* packaging showcase, brand essays) have been cleanly migrated to dedicated Journal post/page templates (`single.php`, `page.php`).

### B. Brand Identity & Header Typography
- Replaced cramped logo rendering with clean, typographical `STATEMENT` wordmark with refined letter spacing.
- Preserves native WordPress custom logo fallback support while prioritizing the modern typographical wordmark.

### C. Storefront Catalog Isolation
- Implemented `Visibility::filter_public_catalog_posts_clauses` in Core: Automatically filters out QA fixtures (`post_title LIKE 'TEST —%'` and `post_name LIKE 'test-%'`) from public `/shop/` and catalog queries.
- Preserves direct lookup capabilities for administrative verification and automated testing.

### D. Production Marketing & Access Email Capture Engine (`SignupService.php`)
- **Mode A (Private Drop Active):** Automatically grants private access via `GrantService::issue_grant()` and establishes a secure session cookie (`SessionService::set_session_cookie()`), redirecting the collector directly to the unlocked Drop.
- **Mode B (Live Drop Active):** Captures collector interest for upcoming releases.
- **Mode C (No Active Drop):** Captures general VIP registration.
- **Security & Privacy:**
  - POST+303 PRG (Post-Redirect-Get) architecture preventing form resubmission.
  - CSRF nonce validation (`wp_verify_nonce`).
  - IP-based rate limiting (`RateLimiter`).
  - SHA-256 HMAC identity derivation for duplicate detection without exposing raw emails.
  - End-to-end encryption via `SecretVault` / `Crypto` before storing in bounded option storage.

### E. Inventory Lifecycle v2 Admin Controls (`LifecycleV2Admin.php`)
- Gives store administrators privileged override capability on product edit screens:
  - Reopen `SOLD_OUT` -> `LIVE`
  - Reopen `ARCHIVED` -> `LIVE`
  - Set `PRIVATE_ACCESS`
- **Safety Invariants:**
  - Requires `manage_woocommerce` capability and valid nonce.
  - Requires explicit confirmation checkbox and mandatory reason / audit note.
  - Normal WooCommerce stock quantity edits do **NOT** mutate the Statement release state.
  - Captures every state transition in a structured audit log (`_statement_lifecycle_audit_log`) recording actor ID, timestamp, stock before/after, and audit note.

### F. Client Demo Tool v0.2.0 (`statement-client-demo`)
- **Deterministic Ownership:** Strictly marks and verifies demo entities using `_statement_client_demo = 1` and deterministic SKUs (`STMT-CD-D001-MJ`, `STMT-CD-D001-PHJ`).
- **Collision Protection:** Never adopts or overwrites arbitrary existing products (e.g. QA fixture Product 213).
- **Repair Utility:** Includes automatic collision detection and repair in the WordPress Admin under **WooCommerce -> Statement Client Demo**.

### G. Theme Package Slimming
- Removed duplicate product media files from the theme repository, reducing the theme package from ~4.5MB down to **548 KB**.
- Theme contains only essential global brand assets; full high-resolution demo product media is dynamically provisioned by the Client Demo tool.

---

## 2. Operator Deployment Runbook (WordPress.com Atomic)

Follow these manual steps to deploy the update to `https://mystatement.store/`:

### Step 1: Upload and Activate Core Plugin Candidate
1. In WP Admin, navigate to **Plugins -> Add New -> Upload Plugin**.
2. Select `dist/statement-collector-core-0.13.0-rc.10.zip`.
3. Click **Install Now** -> **Replace active with uploaded**.
4. Verify version is **0.13.0-rc.10** under Installed Plugins.

### Step 2: Upload and Activate Theme Candidate
1. Navigate to **Appearance -> Themes -> Add New -> Upload Theme**.
2. Select `dist/statement-collector-theme-0.13.0-rc.7.zip`.
3. Click **Install Now** -> **Replace active with uploaded**.
4. Verify active theme is **Statement Collector's Piece (0.13.0-rc.7)**.

### Step 3: Upload and Run Client Demo Seeder
1. Navigate to **Plugins -> Add New -> Upload Plugin**.
2. Select `dist/statement-client-demo-0.2.0.zip`.
3. Click **Install Now** -> **Activate Plugin**.
4. In WP Admin, go to **WooCommerce -> Statement Client Demo**.
5. Click **Seed Demo Content (or Update)**.
6. Verify output confirms Drop 001 and products created with SKUs `STMT-CD-D001-MJ` and `STMT-CD-D001-PHJ`.

### Step 4: Verification Checklist
- [ ] Visit `https://mystatement.store/` -> Check clean typographical `STATEMENT` wordmark in header.
- [ ] Check hero presentation (76svh, centered focal alignment).
- [ ] Check active Drop 001 presentation with the 2 featured demo products.
- [ ] Check email capture form at the bottom of homepage.
- [ ] Visit `https://mystatement.store/shop/` -> Confirm QA fixtures (`TEST — ...`) are completely hidden.
- [ ] Visit `https://mystatement.store/drops/` -> Confirm Drop 001 card renders properly.
