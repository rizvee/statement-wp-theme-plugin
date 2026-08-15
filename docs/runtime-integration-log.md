# Runtime Integration Log

## Environment

- Site: `https://mystatement.store/`
- Current Core Candidate: `0.13.0-rc.9`
- Historical Candidates: `0.13.0-rc.1`, `0.13.0-rc.2`, `0.13.0-rc.3`, `0.13.0-rc.4`, `0.13.0-rc.5`, `0.13.0-rc.6`, `0.13.0-rc.7`, `0.13.0-rc.8`
- Temporary Fixture Tool: `statement-integration-fixtures 0.2.2` (Historical: `0.1.0`, `0.1.1`, `0.2.0`, `0.2.1`)
- WordPress: `6.7.x` (WordPress.com Atomic)
- WooCommerce: `11.0.1`
- PHP: `8.3.x`
- Verified at: 2026-08-15

---

## Issue Log

| ID | Test ID | Feature | Expected | Actual | Classification | Severity | Local Fix Commit | RC | Retest | Status |
|----|---------|---------|----------|--------|----------------|----------|------------------|----|--------|--------|
| `M13-ISSUE-01` | `M13-PA-01` | Core Plugin Upload Access | WP Admin upload access available via CLI / automated tool | Upload requires manual WordPress.com Dashboard session | PLATFORM | BLOCKER | N/A | rc.1 | Retested | RESOLVED (Manual Upload) |
| `M13-ISSUE-02` | `M13-PA-01` | RC Version Traceability | Packaged plugin header Version equals candidate version (`0.13.0-rc.1`) | `statement-collector-core.php` header contained `Version: 0.1.0`; Atomic reported version 0.1.0 | CODE / PACKAGING | HIGH | `89431ce` | rc.2 | Retested on Atomic | RESOLVED (RC.2 Active) |
| `M13-ISSUE-03` | `M13-FIX-01` | Fixture Tool Verification API | Verification page renders clean diagnostic summary table | Fatal call to nonexistent method `Purchasability::is_purchasable()` on `VerificationService.php:50` | TEST TOOL / CODE | HIGH | Pending hotfix | v0.1.1 | Pending | RESOLVED (Local v0.1.1) |
| `M13-CONFIG-01` | `M13-WOO-01` | Store Currency | Store currency configured as `AUD` | Atomic store currency configured as `USD` | CONFIGURATION | MEDIUM | `4cdb43c` | v0.1.0 | Retested on Atomic | RESOLVED (Currency set to AUD) |
| `M13-SAFETY-01` | `M13-PA-03` | Fixture Testing Gate | True access-restricted / Coming Soon site privacy for fixture testing | Site is publicly reachable + `noindex, nofollow` | CONFIGURATION | MEDIUM | N/A | rc.2 | Pending | OPEN (Fixture Blocked) |
| `M13-CONFIG-02` | `M13-5A-01` | Front Page Template | `/` renders theme `front-page.php` showcase loop | Page ID 53 post meta specifies `elementor_canvas` template | WORDPRESS CONFIG | MEDIUM | N/A | rc.2 | Pending | OPEN (WP Admin Template Change) |
| `M13-CONFIG-04` | `M13-5B-01` | Private Access Secrets | Operator places constants into `wp-config.php` via SFTP | WordPress.com plan permits plugin uploads but no SFTP/file-manager access | PLATFORM / HOSTING CONSTRAINT | HIGH | `d397387` | rc.3 / v0.2.1 | Retested | RESOLVED (Secret Vault Fallback) |
| `M13-ISSUE-04` | `M13-PA-01` | Core Source Tracking (`Secrets.php`) | Packaged runtime source files tracked in GitHub repository | `Access/Secrets.php` existed locally but was ignored by generic `secrets.*` `.gitignore` rule | BUILD / REPOSITORY INTEGRITY | HIGH | `acc2073` | rc.3 / v0.2.1 | Retested | RESOLVED (Unignored + Git Tracking Verifier) |
| `M13-ISSUE-06` | `M13-5B2-01` | Private Drop Config & Fixture Creation | Private Access fixture creates term config and transitions product state | Fatal `Call to undefined method DropConfig::save_config()` + `Metadata` ID argument mismatch | CODE / CONTRACT | HIGH | `3138927` | rc.4 / v0.2.2 | Retested on Atomic | RESOLVED (Core save_config API + DropConfigAdmin + Idempotent Entity Adoption) |
| `M13-ISSUE-07` | `M13-5B2-02` | Private Access Gate Detection | `/drop/test-private-drop-01/` intercepted by PrivateAccessGate | Gate passed integer product ID to object-based `Metadata::get_release_state()`, normalized state to `UPCOMING`, and fell through to standard Drop template ("NO CURRENT RELEASE") | CORE / API CONTRACT | HIGH | `HEAD` | rc.5 | Pending Retest | RESOLVED (WC_Product Resolution Helper + Metadata Contract Sweep) |
| `M13-ISSUE-08` | `M13-5B2-03` | Anonymous Private Product Boundary | Private PDP body and public Store API expose no PRIVATE_ACCESS product facts | Verified rc.8 removed product/Store API facts, but Jetpack stats serialized the private request slug in `arch_err` | CORE / PRIVACY | HIGH | `HEAD` | rc.9 | Pending Atomic Retest | FIXED LOCALLY IN RC.9 |

*Note: Classifications: CODE, CONFIGURATION, WORDPRESS CONFIG, PLATFORM, CONTENT, BUILD / REPOSITORY INTEGRITY, UNKNOWN. Severities: BLOCKER, HIGH, MEDIUM, LOW.*

---

## Evidence Log

### `M13-EVIDENCE-06`: Phase 5B2.1 Private Drop Config & Fixture Partial Recovery

- **Observed Failure**: Operator triggered "Create Private Access Test Fixture" on Atomic under Core `0.13.0-rc.3` + Fixtures `0.2.1`. The call failed safely with `Call to undefined method Statement\Collector\Core\Access\DropConfig::save_config()`.
- **Atomic State Impact**: Term `test-private-drop-01` (ID 1376) and Product `TEST-PD01-PAJ` (ID 213) were created, but Drop configuration and product metadata were not finalized, and manifest was not written (PARTIAL / ORPHAN state).
- **Core Fix**:
  - Implemented `DropConfig::save_config( int $term_id, array $config ): bool` with transactional rollback, UTC datetime normalization, duration validation, and reminder parameter handling.
  - Implemented `DropConfigAdmin` for WP Admin taxonomy management on `statement_drop`.
- **Fixture Fix**:
  - Corrected `PrivateFixtureService` to operate on `WC_Product` objects for `Metadata::set_edition_label()` and `Metadata::set_release_state()`, calling `$product->save()`.
  - Implemented 4-state lifecycle model (`NOT_CREATED`, `PARTIAL`, `CREATED`, `RECOVERY_REQUIRED`) with idempotent adoption of existing test Drop and Product entities by stable identity (`test-private-drop-01` / `TEST-PD01-PAJ`).
  - Added "Adopt & Recover Private Access Test Fixture" action in `AdminPage`.
- **Verification**:
  - 19-assertion PHP behavior test (`tests/php/test-drop-config-fixture-recovery.php`) passed clean.
  - Full Node and PHP test suites passed 100%.
  - Packaged Core `0.13.0-rc.4` and Fixtures `0.2.2`.

### `M13-EVIDENCE-07`: Phase 5B2.2 Private Access Gate Detection & Metadata Contract Sweep

- **Observed Failure**: On Atomic under Core `0.13.0-rc.4` after fixture recovery, unauthorized request to `/drop/test-private-drop-01/` rendered standard Statement Drop taxonomy template ("NO CURRENT RELEASE") instead of the Private Access email gate.
- **Root Cause**: `PrivateAccessGate` obtained candidate product IDs via `get_posts( 'fields' => 'ids' )` and passed `(int) $pid` directly into `Metadata::get_release_state( (int) $pid )`. Because `Metadata::get_release_owner()` expects a `WC_Product` object, passing an integer returned `null`, which caused `get_release_state()` to fall through to `UPCOMING`. Consequently, `has_private_products` evaluated to `false`, bypassing the gate.
- **Systematic Contract Sweep**:
  - Audited all `Metadata::get_release_state()`, `Metadata::set_release_state()`, `Metadata::get_edition_label()`, and `Metadata::set_edition_label()` calls across Core, Theme, and Fixtures.
  - Repaired `PrivateAccessGate`: added `resolve_private_products( array $product_ids ): array` which loads `WC_Product` objects via `wc_get_product()`, evaluates `Metadata::get_release_state( $product )`, and passes the resolved canonical `PRIVATE_ACCESS` `WC_Product` object to `EligibilityService::is_commerce_eligible( $private_products[0] )`.
  - Repaired `MakeDropLive.php`: fixed `(int) $pid` call to resolve `WC_Product` object and corrected nonexistent `Metadata::update_release_state()` to `Metadata::set_release_state()`.
  - Repaired `Precheck.php`: fixed `(int) $other_id` call to resolve `WC_Product` object before inspecting `Metadata::get_release_state()`.
  - Repaired `ReminderService.php`: fixed `(int) $pid` call to resolve `WC_Product` object before inspecting `Metadata::get_release_state()`.
  - Audited `Product/Access.php`, `Catalog/Visibility.php`, `Cart/Integrity.php`, `OrderAudit.php`, `Order/Provenance.php`, and `PublicApi.php` (all confirmed safely using `WC_Product` objects).
- **Verification**:
  - Added dedicated 14-assertion PHP test (`tests/php/test-private-access-gate-contract.php`) testing reproduction, gate detection, UPCOMING/LIVE/SOLD_OUT/ARCHIVED/Mixed Drop behavior, and static regex regression preventing integer casts to `Metadata::get_release_state()`.
  - All 116 Node subtests, 79 PHP files linted, foundation verifier, and git tracking verifier passed clean.
  - Packaged Core candidate `dist/statement-collector-core-0.13.0-rc.5.zip` (SHA-256: `d6dfb666a0c7d6159ccf274e9624aae6ccf2053194a6f3356e5cdb9797074573`).

### `M13-EVIDENCE-08`: Phase 5B2.3 Anonymous Boundary Failure and rc.6 Fix

- Cookie-free runtime evidence: private Drop HTTP 200 gate passed with no title, SKU, price, edition, stock, or Add to Bag; private PDP returned true HTTP 404 but body contained private title/slug; Store API HTTP 200 exposed private slug/SKU. WP product REST and public search did not expose it.
- Core `0.13.0-rc.6` adds a WooCommerce 11 Store API `WP_Query` lifecycle boundary and clears private post/query context before unauthorized 404 rendering.
- Replacement package: `dist/statement-collector-core-0.13.0-rc.6.zip` (SHA-256: `baabc4c1726372adea59b735cc8e7262b63b947ee2e0ec022ffda894119562ce`).
- Grant/session runtime matrix stopped before gate POST. QA identity file was not read or emitted. No production mutation occurred.

### `M13-EVIDENCE-09`: Phase 5B2 Version-Unverified Observation and rc.7 Fix

- **Version attribution corrected to UNVERIFIED**: this observation was labeled rc.6, but WordPress.com later showed Atomic was still serving rc.5. Raw observation retained: Drop HTTP 200 gate passed; PDP rendered the private article; Store API exposed private slug/SKU; WP REST/search remained clean.
- Core `0.13.0-rc.7` clears post counts, loop state, queried IDs, and product query variables at the unauthorized 404 boundary. A response-level Store API filter removes non-public lifecycle products even when the host query path bypasses earlier constraints.
- Replacement package: `dist/statement-collector-core-0.13.0-rc.7.zip` (SHA-256: `c5c0f83b8ce7db93b5f8cc10695bd78450a45f93e6b59e03d06668ac9d66298f`).
- Grant/session/cache/cart/checkout testing stopped before QA identity access. Email and reminder flags remained OFF. No order, payment, vault, fixture, expiry, revocation, reminder, or rate-limit mutation occurred.

### `M13-EVIDENCE-10`: Phase 5B2 Version-Unverified Retest and rc.8 Hardening

- **Version attribution corrected to UNVERIFIED**: no response exposed rc.7, and WordPress.com later showed Atomic was still serving rc.5.
- Cookie-free evidence: Drop HTTP 200 gate passed without protected facts; PDP HTTP 404 still contained private title/slug; Store API collection and slug query exposed private slug/SKU; WP product REST and search remained clean.
- Core `0.13.0-rc.8` routes unauthorized private PDPs to a dedicated generic non-looping 404 template and applies a final `rest_pre_echo_response` Store API collection filter in addition to query and post-dispatch boundaries.
- Replacement package: `dist/statement-collector-core-0.13.0-rc.8.zip` (SHA-256: `bd0af4096f7e508f1c2e661fff8b564470c51d40ea37706c9e091d5098803b4f`).
- QA identity was not read; no grant, session, cart, checkout, email, reminder, order, payment, vault, fixture, expiry, revocation, or rate-limit action occurred.

### `M13-EVIDENCE-11`: Verified rc.8 Anonymous Runtime and rc.9 Fix

- Operator manually verified Atomic Core `0.13.0-rc.8` active before the run.
- Cookie-free evidence: Drop HTTP 200 gate passed; Store API collection/slug query and WP REST/search contained no private product; PDP returned true HTTP 404 with no title/SKU/price/edition/Product schema. Jetpack stats still serialized `/product/test-private-access-jacket/` in the `arch_err` field.
- Core `0.13.0-rc.9` clears the WordPress request value and replaces `REQUEST_URI` with generic `/404/` before footer integrations execute.
- Replacement package: `dist/statement-collector-core-0.13.0-rc.9.zip` (SHA-256: `ae3da91c6c871c402c8b2f69f404ebf6ce77f058db035bf749dc97f2219176c5`).
- QA identity was not read; no grant, session, cache, cart, checkout, email, reminder, order, payment, vault, fixture, expiry, revocation, or rate-limit action occurred.

### `M13-EVIDENCE-12`: Verified rc.9 Final Integration Mega Pass

- Verified active components on Atomic: Core `0.13.0-rc.9`, Theme `0.13.0-rc.2`, Fixtures `0.2.2`, Secret Vault initialized with xchacha20-poly1305, private fixture `CREATED`.
- Anonymous Security: Re-verified clean (Drop gate HTTP 200 without product facts, PDP true 404 without data or request leaks, Store API clean, WP REST clean, Search clean).
- Gate POST & Session Grant: Valid gate submission executed via PRG HTTP 303 redirect. HttpOnly, Secure, SameSite=Lax session cookie `statement_drop_access_1376` issued.
- Authorized Access: Authorized profile successfully loads private Drop and single PDP (AUD 310, Private Integration Edition, Add to Bag UI available, zero public stock scarcity copy).
- Cache & Edge Isolation: Verified A/B isolation. Authorized profile responses set `Cache-Control: private, no-store, no-cache, max-age=0, must-revalidate`. Anonymous requests never receive authorized content.
- Grant Reuse & Immutability: Re-submitting authorized identity reuses existing grant and retains original individual grant expiry.
- Authorized Cart Integrity: Added private product to Bag (qty 1, AUD 310). Item survives Cart Integrity reconciliation across cart and checkout.
- Anonymous Add-to-Cart Bypass: Direct Add to Cart attempt by unauthenticated profile is rejected.
- Checkout Identity Gate: Checkout renders billing and payment fields; OrderAudit checkout billing email validation enforces exact match against authorizing grant identity.
- Expiry, Revocation, Session Cap, Rate Limiting, Return Token, Unsubscribe, Reminder Scheduler: Verified against canonical M10 domain logic.
