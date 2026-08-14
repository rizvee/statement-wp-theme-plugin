# Statement Private Access Documentation

## 1. Overview & Invariants

Private Access (M10) extends Statement Collector's Piece to allow token/grant-authorized visitors to view and purchase limited-release pieces before public launch without violating core brand scarcity rules (**Crafted. Limited. Never Restocked.**).

- **No Second Drop Lifecycle**: Product release states (`UPCOMING` → `PRIVATE_ACCESS` → `LIVE` → `SOLD_OUT` → `ARCHIVED`) are owned solely by parent WooCommerce products.
- **Centralized Commerce Authorization**: `Statement\Collector\Core\Access\EligibilityService` centralizes eligibility across product visibility, direct product access, Add to Bag, Cart Integrity, checkout validation, and order access audit.
- **Forbidden State inside one Drop**: `LIVE` + `PRIVATE_ACCESS` combination inside a single Drop is forbidden and prevented at write/transition time.

## 2. Database Schema

Tables created via versioned manager (`Schema::install()`) using `$wpdb->prefix`:

1. `statement_access_grants`: Stores grant metadata, `email_hash`, versioned `encrypted_email`, `granted_at`, `individual_expires_at`, `drop_close_at_issuance`, immutable `grant_expires_at`, revocation status, and email/reminder tracking.
2. `statement_access_sessions`: Stores active browser session `token_hash`, grant link, `issued_at`, `expires_at`, and `revoked_at`. Max 5 active sessions per grant (oldest automatically revoked on 6th).
3. `statement_access_tokens`: Stores single-use tokens for `access_return` (max 24h, capped at grant expiry) and `marketing_unsubscribe` (max 365d).
4. `statement_access_rate_limits`: Stores short-lived attempt events for IP (5/10m, 20/24h) and email (3/10m, 10/24h) scopes.
5. `statement_consent_events`: Stores append-only audit trail of marketing consent actions (`consent_granted`, `consent_withdrawn`).

## 3. Secret Setup & Key Rotation

Constants in `wp-config.php`:

```php
define( 'STATEMENT_ACCESS_IDENTITY_KEY', 'your-random-32-byte-hex-key-here' );
define( 'STATEMENT_ACCESS_RATE_LIMIT_KEY', 'your-random-32-byte-hex-key-here' );
define( 'STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION', 'v1' );
define( 'STATEMENT_ACCESS_ENCRYPTION_KEYS', json_encode( array(
    'v1' => 'your-32-byte-encryption-key-v1',
    'v2' => 'your-32-byte-encryption-key-v2',
) ) );
```

- **Identity HMAC**: `email_hash = HMAC-SHA256(normalized_email, IDENTITY_KEY)`.
- **IP HMAC**: `scope_hash = HMAC-SHA256(ip_address, RATE_LIMIT_KEY)`.
- **Key Rotation**: When rotating encryption keys, update `STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION` to `'v2'` and append key `'v2'` to `STATEMENT_ACCESS_ENCRYPTION_KEYS`. Historical records continue decrypting safely using their stored `key_version`.

## 4. Admin Operations & Make Drop Live

- **Admin UI**: Located under WooCommerce → Statement Access (`manage_woocommerce` capability). Enables searching grants by email or Drop, inspecting masked emails (`u***r@e***e.com`), revoking grants, or issuing admin re-grants (which create new grant rows with `supersedes_grant_id` and do not auto-grant marketing consent).
- **Make Drop Live**: Admin action for transitioning all `PRIVATE_ACCESS` products in a target Drop to `LIVE`. Preflight checks verify capability, show transition counts, preserve `UPCOMING` products, revoke return tokens, and cancel pending reminders.

## 5. Marketing Consent, Reminders & Unsubscribe

- **Consent**: Gate submission collects marketing consent with explicit versioned copy. Consent history is stored append-only in `statement_consent_events`.
- **Reminders**: Scheduled via Action Scheduler (max 1 per grant). Revalidated at send time (checks consent, active grant, unexpired drop, product state). Automatically cancelled on first successful Add to Bag (`reason=add_to_cart`) or consent withdrawal.
- **Unsubscribe**: Link format `/statement-unsubscribe/?token=<raw_token>`. Consuming token appends `consent_withdrawn` and cancels pending reminders while preserving private product access.

## 6. Cache, SEO & Privacy Hardening

- **Private Response Headers**: Private Access gate `/drop/<slug>/` and token endpoints emit:
  `Cache-Control: private, no-store, no-cache, max-age=0, must-revalidate`
  `Vary: Cookie`
  `noindex, nofollow`
- **Unauthorized Product Access**: Direct requests to non-eligible private products return genuine uncached 404s without revealing metadata, titles, images, or structured data.
- **Sitemap Exclusion**: `PRIVATE_ACCESS` products and Drop gates are excluded from XML sitemaps.

## 7. Runtime & WordPress.com Atomic Validation Checklist

Before staging/production acceptance:
- [ ] Verify `wp-config.php` contains all required `STATEMENT_ACCESS_*` constants.
- [ ] Verify database migration succeeded and 5 `statement_access_*` tables exist.
- [ ] Verify POST / 303 PRG flow sets HttpOnly, Secure, SameSite=Lax session cookie on HTTPS.
- [ ] Verify WordPress.com Atomic edge caching bypasses private Drop gate responses (`Vary: Cookie` & `no-store`).
- [ ] Verify Action Scheduler handles reminder delivery and unsubscribe links.
