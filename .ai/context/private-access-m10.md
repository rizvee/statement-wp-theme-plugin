# Private Access Context (M10)

## Overview

M10 introduces private drop access for Statement Collector's Piece without violating the core scarcity model (Crafted. Limited. Never Restocked.) or introducing a duplicate Drop lifecycle.

## Domain & Commerce Eligibility

- **Canonical Product Lifecycle**: `UPCOMING` → `PRIVATE_ACCESS` → `LIVE` → `SOLD_OUT` → `ARCHIVED`. Owned by parent WooCommerce product (variations inherit parent state).
- **Centralized Commerce Authorization**: `Statement_Collector_Core\Access\Eligibility_Service` (or equivalent) evaluates:
  - `LIVE` => ALWAYS ELIGIBLE
  - `PRIVATE_ACCESS` => ELIGIBLE ONLY IF visitor has valid session for product's exact Drop AND grant active AND session active AND grant not revoked AND grant expiry not passed AND Drop closing time not passed.
  - `SOLD_OUT` / `ARCHIVED` => ALWAYS BLOCKED.
- Used across: product visibility, direct product access, Add to Bag, Cart Integrity, checkout final validation, and order access audit.

## Storage & Database Schema

Operational tables using `$wpdb->prefix`:
1. `statement_access_grants`: Drop ID, `email_hash`, encrypted email, key version, `granted_at`, `individual_expires_at`, `drop_close_at_issuance`, `grant_expires_at`, source, `supersedes_grant_id`, revocation data, email tracking, reminder tracking.
2. `statement_access_sessions`: Grant ID, `token_hash`, `issued_at`, `expires_at`, `revoked_at`. Max 5 active sessions per grant (6th revokes oldest).
3. `statement_access_tokens`: `grant_id` (nullable), `subject_email_hash` (nullable), purpose (`access_return`, `marketing_unsubscribe`), `token_hash`, `issued_at`, `expires_at`, `consumed_at`, `revoked_at`.
4. `statement_access_rate_limits`: `drop_term_id`, `scope_type` (`ip`, `email`), `scope_hash`, `attempted_at`, `expires_at`.
5. `statement_consent_events`: Append-only consent audit trail (`email_hash`, `drop_term_id`, `grant_id`, `event_type`, `consent_version`, `exact_consent_text`, `consent_text_hash`, `source`, `occurred_at`, `schema_version`).

## Secrets & Cryptography

Constants defined in `wp-config.php` (never stored in DB or code):
- `STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION`
- `STATEMENT_ACCESS_ENCRYPTION_KEYS` (JSON map of version => secret key)
- `STATEMENT_ACCESS_IDENTITY_KEY` (used for email HMAC-SHA256)
- `STATEMENT_ACCESS_RATE_LIMIT_KEY` (used for IP HMAC-SHA256)

Email identity: HMAC-SHA256(normalized_email, IDENTITY_KEY).
IP identity: HMAC-SHA256(normalized_ip, RATE_LIMIT_KEY).
Email encryption: Authenticated encryption (sodium XChaCha20-Poly1305 or AES-256-GCM) with versioned keyring.

## Public Access Form & Cookie

- **Route**: POST handler on `/drop/<slug>/` with PRG (HTTP 303) response. No AJAX, no REST grant endpoints, no client-side JS token storage.
- **Cookie**: Opaque random token cookie per Drop (`statement_drop_access_<drop_id>`), `HttpOnly`, `Secure`, `SameSite=Lax`, `Path=/`. Raw token in cookie, SHA-256 hash in DB session.

## Commerce & Order Audit

- **Add to Bag**: Validates exact-Drop authorization. First successful Add to Bag permanently cancels pending marketing reminder (`reason=add_to_cart`).
- **Cart & Checkout**: Cart Integrity checks current session validity. Checkout enforces billing email identity matches authorizing grant `email_hash`. Multiple private items must resolve to the same email identity.
- **Order Line Item Metadata**:
  - `_statement_private_access_grant_id`
  - `_statement_private_access_drop_id`
  - `_statement_private_access_authorized_at`
  - `_statement_private_access_context_version`
  (No secrets, tokens, IP, or raw email in order metadata).

## Make Drop Live

- Admin action with read-only preflight summary and confirmation.
- Transitions all `PRIVATE_ACCESS` products in the target Drop to `LIVE`.
- Revokes return tokens and cancels pending reminders.
- `UPCOMING`, `SOLD_OUT`, and `ARCHIVED` products remain unchanged.

## Privacy, SEO & Caching

- `PRIVATE_ACCESS` Drop gate: HTTP response headers `Cache-Control: private, no-store, no-cache, max-age=0` and `noindex, nofollow`. Protected product data NEVER rendered in unauthorized HTML/metadata.
- Unauthorized direct product: Genuine uncached 404.
