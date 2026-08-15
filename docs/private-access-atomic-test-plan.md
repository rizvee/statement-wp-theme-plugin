# Private Access Atomic Runtime Test Plan (M13 Phase 5B)

## 1. Executive Summary & Scope

This document defines the Atomic runtime test procedures and verification matrix for Statement Private Access architecture (M10/M13). It covers secret provisioning, database/schema observability, anonymous API privacy, edge cache isolation, session management, and status-aware commerce integrity.

---

## 2. Cryptographic Secret Contract

The Private Access architecture requires four `wp-config.php` constants. Zero secret values are stored in Git, documentation, or candidate artifacts.

| Constant | Description | Format |
|---|---|---|
| `STATEMENT_ACCESS_IDENTITY_KEY` | HMAC key for email identity hashing (`Crypto::hash_email`) | 64-char hex string (32 random bytes) |
| `STATEMENT_ACCESS_RATE_LIMIT_KEY` | HMAC key for IP rate-limit hashing (`Crypto::hash_ip`) | 64-char hex string (32 random bytes) |
| `STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION` | Active key version identifier | String (e.g., `'v1'`) |
| `STATEMENT_ACCESS_ENCRYPTION_KEYS` | Versioned encryption keyring array or JSON string | JSON string `{"v1":"<64-char-hex>"}` |

---

## 3. Atomic Operator Secret Installation Runbook

1. Generate local secret snippet without printing values to stdout:
   ```bash
   node scripts/generate-private-access-secrets.mjs
   ```
2. Open the ignored local file `.local-runtime/private-access-wp-config.php`.
3. Connect to WordPress.com Atomic site via SFTP.
4. Back up current `wp-config.php` locally.
5. Paste the generated PHP `define()` snippet into `wp-config.php` before the WordPress bootstrap completion line (`/* That's all, stop editing! */`).
6. Save and close.
7. **DO NOT** commit `wp-config.php` or paste secret values into chat/logs.

---

## 4. Fixture Tool v0.2.0 Deployment & Preflight Verification

1. Upload `dist/statement-integration-fixtures-0.2.0.zip` via WordPress.com Dashboard or WP Admin Plugins screen.
2. Confirm plugin is active.
3. Open `WooCommerce -> Statement Integration Fixtures`.
4. Inspect the **PRIVATE ACCESS RUNTIME PREFLIGHT** diagnostic panel:
   - Encryption Active Version: `CONFIGURED (v1)`
   - Encryption Keyring: `CONFIGURED`
   - Identity Key: `CONFIGURED`
   - Rate-Limit Key: `CONFIGURED`
   - Required Crypto Backend: `AVAILABLE (xchacha20-poly1305)` or `AVAILABLE (aes-256-gcm)`
   - Database / Schema (M10): `EXISTS (5 tables, db_version: 1.0.0)`
   - Private Fixture Status: `NOT CREATED`

---

## 5. Phase 5B2 Execution Matrix

### A. Edge Cache Isolation Plan
- **Profile A (Authorized Session):** Submits valid gate POST on `/drop/test-private-drop-01/`. Accesses private Drop/product.
- **Profile B (Anonymous Profile / DevTools MCP):** Accesses `/drop/test-private-drop-01/` and `/product/test-private-access-jacket/`.
- **Assertions:**
  - Profile B MUST receive HTTP 404 on `/product/test-private-access-jacket/` and access gate form on `/drop/test-private-drop-01/`.
  - Response headers MUST include `Cache-Control: private, no-cache, no-store, must-revalidate` for authorized requests.
  - Profile B MUST NEVER receive cached HTML intended for Profile A.

### B. Direct Product Access Matrix
- **Unauthorized:** Direct URL `/product/test-private-access-jacket/` returns true HTTP 404 fallback with no title, price, images, or Product schema.
- **Authorized:** Renders single product template with AUD 310 price, edition label, and Add to Bag button.

### C. Private Drop Gate Matrix
- **Unauthorized:** `/drop/test-private-drop-01/` renders minimal email request form.
- **Gate Submission:** Valid POST creates grant token, sets `statement_session_<drop>` cookie (HttpOnly, Secure, SameSite=Lax), performs 303 PRG redirect to Drop URL.
- **Re-submission:** Submitting existing authorized email reuses grant and session without extending original grant expiry.

### D. Grant & Session Rules
- **Grant Expiry:** Effective expiry = `min(grant_expires_at, drop_close_at)`.
- **Session Cap:** Maximum 5 active sessions per grant. 6th session revokes oldest session token.
- **Revocation:** Admin revocation immediately invalidates all active session tokens and blocks self-regrant.

### E. Commerce & Provenance Matrix
- **Cart Validation:** Add to Bag allowed only for active authorized session. Expiry or revocation removes line item during cart revalidation.
- **Checkout Enforcement:** Checkout billing email MUST match the authorized grant email. Mismatch blocks checkout submission.
- **Order Provenance:** Placed order line item records both M10 Private Access audit metadata and M12 immutable purchase provenance.
