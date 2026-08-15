# Private Access Atomic Runtime Test Plan (M13 Phase 5B)

## 1. Executive Summary & Scope

This document defines the Atomic runtime test procedures and verification matrix for Statement Private Access architecture (M10/M13). It covers secret provisioning, database/schema observability, anonymous API privacy, edge cache isolation, session management, and status-aware commerce integrity.

---

## 2. Secret Provider Architecture

Private Access supports two secret providers evaluated in order:

1. **Preferred Provider (`wp_config`):**
   Requires four `wp-config.php` constants (`STATEMENT_ACCESS_IDENTITY_KEY`, `STATEMENT_ACCESS_RATE_LIMIT_KEY`, `STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION`, `STATEMENT_ACCESS_ENCRYPTION_KEYS`). Use when server configuration file access (SFTP/SSH) is available.
2. **WordPress.com Compatible Fallback (`encrypted_vault`):**
   Uses option `statement_access_secret_vault_v1` (`autoload = false`). The secret bundle is encrypted server-side using AEAD (libsodium XChaCha20-Poly1305 or OpenSSL AES-256-GCM) with a wrapping key derived via HMAC-SHA256 from `wp_salt('auth')`. **Zero plaintext secrets are stored in the database.**
3. **Fail-Closed Gate (`unavailable` / `invalid_wp_config`):**
   If `wp-config` constants are partially defined, or if the vault is uninitialized/corrupted, provider evaluates to `unavailable` or `invalid_wp_config`. Commerce eligibility checks fail closed.

---

## 3. Atomic Operator Secret Installation Runbook (No-SFTP Path)

On hosting environments such as WordPress.com Atomic where SFTP/file-manager access is unavailable:

1. Upload `dist/statement-collector-core-0.13.0-rc.3.zip` in WordPress Admin -> Plugins -> Add New -> Upload Plugin.
2. Upload `dist/statement-integration-fixtures-0.2.1.zip` in WordPress Admin.
3. Navigate to `WooCommerce -> Statement Fixtures`.
4. Under **PRIVATE ACCESS RUNTIME PREFLIGHT**, observe:
   - Secret Provider: `UNAVAILABLE`
   - Secret Vault Status: `NOT INITIALIZED`
5. Click **INITIALIZE PRIVATE ACCESS SECRET VAULT**.
6. Observe status update:
   - Secret Provider: `ENCRYPTED VAULT`
   - Secret Vault Status: `INITIALIZED`
   - Identity Key: `CONFIGURED`
   - Rate-Limit Key: `CONFIGURED`
   - Encryption Keyring: `CONFIGURED`
   - Required Crypto Backend: `AVAILABLE (xchacha20-poly1305)` or `AVAILABLE (aes-256-gcm)`
   - Database / Schema (M10): `EXISTS (5 tables, db_version: 1.0.0)`

*Optional WP-Config Tool (`scripts/generate-private-access-secrets.mjs`) remains available for environments with SFTP/wp-config access.*

---

## 4. Phase 5B2 Execution Matrix

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
