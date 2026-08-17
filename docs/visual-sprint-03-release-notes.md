# Visual Sprint 03 / Overnight Mega Build — Release Notes & Operator Runbook

**Release Date:** 2026-08-17
**Artifacts Generated:**
- **Theme:** `dist/statement-collector-theme-0.13.0-rc.7.zip`
- **Core Plugin:** `dist/statement-collector-core-0.13.0-rc.11.zip`
- **Client Demo Tool:** `dist/statement-client-demo-0.2.0.zip`

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
- Implemented `Visibility::filter_public_catalog_posts_clauses` in Core: Automatically filters out QA fixtures (`_statement_fixture = 1`, `_sku LIKE 'TEST-%'`, `post_title LIKE 'TEST —%'`, and `post_name LIKE 'test-%'`) from public `/shop/` and catalog queries.
- Preserves direct lookup capabilities for administrative verification and automated testing.

### D. Production Marketing & Access Email Capture Engine (`SignupService.php`)
- **Mode A (Private Drop Active):** Automatically grants private access via `GrantService::get_or_create_public_grant()` and establishes a secure session cookie (`SessionService::set_session_cookie()`), redirecting the collector directly to the unlocked Drop.
- **Mode B (Live Drop Active):** Captures collector interest for upcoming releases with encrypted email storage in `statement_consent_events`.
- **Mode C (No Active Drop):** Captures general VIP registration with encrypted email storage in `statement_consent_events`.
- **Security & Privacy:**
  - POST+303 PRG (Post-Redirect-Get) architecture preventing form resubmission.
  - CSRF nonce validation (`wp_verify_nonce`).
  - IP-based and email-based rate limiting via canonical `RateLimiter::is_allowed()` / `RateLimiter::record_attempt()`.
  - SHA-256 HMAC identity derivation (`Crypto::hash_email`, `Crypto::hash_ip`) via Statement keys.
  - End-to-end encryption via `SecretVault` / `Crypto` before storing in database consent table.
  - Fail-closed security on unavailable encryption keys (no plaintext persistence).

### E. Inventory Lifecycle v2 Admin Controls (`LifecycleOverrideService.php` & `LifecycleV2Admin.php`)
- Gives store administrators privileged override capability on product edit screens:
  - Reopen `SOLD_OUT` -> `LIVE` (Requires Stock > 0 and mandatory reason)
  - Reopen `ARCHIVED` -> `LIVE` (Requires Stock > 0 and mandatory reason)
  - Set `PRIVATE_ACCESS` (Requires assigned Drop with valid future `DropConfig` and Stock > 0)
- **Safety Invariants:**
  - Normal public `Metadata::set_release_state()` remains strictly forward-only.
  - Requires `manage_woocommerce` capability and valid nonce.
  - Requires explicit confirmation checkbox and mandatory non-empty reason / audit note.
  - Normal WooCommerce stock quantity edits do **NOT** mutate the Statement release state.
  - Historical order provenance remains frozen across state overrides.
  - Re-reads and verifies database persistence before recording structured audit events (`statement_lifecycle_audit_log`).

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
2. Select `dist/statement-collector-core-0.13.0-rc.11.zip`.
3. Click **Install Now** -> **Replace active with uploaded**.
4. Verify version is **0.13.0-rc.11** under Installed Plugins.

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
