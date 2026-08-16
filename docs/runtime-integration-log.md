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
| `M13-ISSUE-10` | `M13-FIX-02` | Fixture 0.3.0 Early Bootstrap | Fixtures 0.3.0 activates cleanly without fatal errors | `StatementQaGateway.php` required at top-level before `WC_Payment_Gateway` loaded; plugin auto-deactivated | FIXTURE / BOOTSTRAP | BLOCKER | `HEAD` | v0.3.1 | Retested on Atomic | RESOLVED (V0.3.1 Active) |
| `M13-ISSUE-11` | `M13-FIX-03` | Fixture 0.3.1 QA Harness Expiry & Reminder Tests | Expiry and Reminder test buttons verify genuine runtime contracts | Expiry compared static math on historical grant; Reminder passed integer IDs vs WC_Product object and lacked session cookie | FIXTURE / QA HARNESS | MEDIUM | `HEAD` | v0.3.2 | Pending Atomic Retest | FIXED LOCALLY IN V0.3.2 |



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

### `M13-EVIDENCE-12`: Verified rc.9 Private Access Core Flow

- Verified active components on Atomic: Core `0.13.0-rc.9`, Theme `0.13.0-rc.2`, Fixtures `0.2.2`, Secret Vault initialized with xchacha20-poly1305, private fixture `CREATED`.
- Anonymous Security: Re-verified clean (Drop gate HTTP 200 without product facts, PDP true 404 without data or request leaks, Store API clean, WP REST clean, Search clean).
- Gate POST & Session Grant: Valid gate submission executed via PRG HTTP 303 redirect. HttpOnly, Secure, SameSite=Lax session cookie `statement_drop_access_1376` issued.
- Authorized Access: Authorized profile successfully loads private Drop and single PDP (AUD 310, Private Integration Edition, Add to Bag UI available, zero public stock scarcity copy).
- Cache & Edge Isolation: Verified A/B isolation. Authorized profile responses set `Cache-Control: private, no-store, no-cache, max-age=0, must-revalidate`. Anonymous requests never receive authorized content.
- Grant Reuse Path: Re-submitting authorized identity reuses existing grant on frontend.
- Authorized Cart Integrity: Added private product to Bag (qty 1, AUD 310). Item survives Cart Integrity reconciliation across cart and checkout.
- Anonymous Add-to-Cart Bypass: Direct Add to Cart attempt by unauthenticated profile is rejected.
- Checkout Page Render: Checkout renders billing and payment fields without errors.
- Unexercised Matrix Classification: Expiry shortening/non-extension, admin revocation, session cap FIFO, rate-limit thresholds, single-use return tokens, unsubscribe separation, reminder scheduling/cancellation, and controlled order placement are classified as `STRUCTURAL_ONLY / RUNTIME_PENDING` pending fixture tool upgrade.

### `M13-EVIDENCE-14`: Fixture 0.3.1 Bootstrap Hotfix & Order Safety Hardening

- **Observed Incident**: WordPress.com automatic recovery caught `Uncaught Error: Class "WC_Payment_Gateway" not found` in `StatementQaGateway.php:18` during early plugin bootstrap when uploading Fixtures `0.3.0`.
- **Impact**: WordPress safely deactivated Fixtures `0.3.0`. Core `0.13.0-rc.9`, Theme `0.13.0-rc.2`, WooCommerce `11.0.1`, and Secret Vault remained completely healthy. Production impact: NONE.
- **Root Cause**: `statement-integration-fixtures.php` required `src/StatementQaGateway.php` unconditionally at top-level during early plugin initialization before WooCommerce had registered `WC_Payment_Gateway`.
- **Hotfix in Fixtures 0.3.1**:
  - Removed eager `require_once` of `StatementQaGateway.php` from plugin bootstrap.
  - Implemented lazy loading inside the `woocommerce_payment_gateways` filter hook, checking `class_exists( 'WC_Payment_Gateway' )` and ensuring idempotent registration without duplicates.
  - Fixture plugin boots cleanly and side-effect free even when WooCommerce is uninitialized or absent.
  - Hardened `StatementQaGateway::is_available()` to validate exact test SKU (`TEST-PD01-PAJ`) and match against the active test product ID resolved via `PrivateFixtureService`, rejecting any cart with non-target items.
  - Hardened `StatementQaGateway::process_payment()` to re-verify order line items before marking payment complete.
  - Removed redundant `wc_reduce_stock_levels()` call in `process_payment()`; inventory reduction is managed deterministically once by WooCommerce core `payment_complete()`.
- **Verification**:
  - Added 19-assertion behavior test `tests/php/test-fixture-bootstrap.php` validating Woo-absent boot, lazy registration on `woocommerce_payment_gateways`, duplicate protection, scope enforcement, and single stock reduction.
  - Package: `dist/statement-integration-fixtures-0.3.1.zip` (24,979 bytes, SHA-256: `484244bc2ee698ad8425cbb95a0aca7e7b852ad2305a3a27439e2d676f45bea2`).

### `M13-EVIDENCE-15`: Fixture 0.3.2 Expiry & Reminder QA Harness Hardening

- **Observed Diagnostics in 0.3.1**:
  - `RUN EXPIRY TEST` failed when older historical grants existed whose remaining duration did not match hardcoded 1-hour assumptions, and Drop configuration was not mutated/re-read from storage.
  - `RUN REMINDER TEST` failed because the test fired `statement_private_access_added_to_cart` with integer IDs `($grant_id, $drop_id)` without a valid session cookie, whereas Core `ReminderService::cancel_reminder_on_add_to_bag( $product )` expects a `WC_Product` object and validates `$_COOKIE`.
- **Harness Upgrades in 0.3.2**:
  - Added `find_latest_active_qa_grant()` in `QaTestService.php` to strictly query unrevoked, unexpired grants (`revoked_at IS NULL AND grant_expires_at > NOW()`).
  - Upgraded `run_expiry_test()` to dynamically compute a safe earlier close time, persist it via `DropConfig::save_config()`, assert effective expiry shortening, persist a later close exceeding grant expiry, assert grant expiry invariance, and restore original config in a `finally` block.
  - Upgraded `run_reminder_test()` to establish a valid active session token in `$_COOKIE`, pass the resolved `WC_Product` object to `statement_private_access_added_to_cart`, assert `reminder_cancelled_at` with reason `add_to_cart`, and clean up test actions and cookies in a `finally` block.
- **Verification**:
  - 82 PHP files linted clean, 116 Node tests pass, `test-fixture-bootstrap.php` passes clean.
  - Package: `dist/statement-integration-fixtures-0.3.2.zip` (26,047 bytes, SHA-256: `cd4c806e686c8b19ac0ab6e48ed495d40e94b918ea091f1e10e2f39878d023ed`).

### `M13-EVIDENCE-16`: Fixture 0.3.3 Expiry Normalization, Access Email Test, and Terminal Revalidation

- **Expiry Root Cause Diagnosis in 0.3.2**:
  - `DropConfig::save_config( $term_id, $config )` parses and validates `$config['closes_at']` (string or timestamp).
  - In `0.3.2`, `run_expiry_test()` mutated only `$mutated_config_earlier['closes_at_ts']` and `closes_at_iso`, leaving the original `$mutated_config_earlier['closes_at']` unmutated.
  - Consequently, `DropConfig::save_config()` read the unchanged `closes_at` string from the configuration array and re-persisted the baseline timestamp, causing effective expiry comparison to fail.
- **Harness Upgrades in 0.3.3**:
  - In `QaTestService::run_expiry_test()`: Mutated both `$mutated_config['closes_at']` (formatted as `gmdate('Y-m-d H:i:s', $ts)`) and `closes_at_ts`, ensuring deterministic persistence and reading from storage. Asserted dynamic shortening for earlier close and grant expiry invariance for later close.
  - Added `QaTestService::run_access_email_test()`: Resolves active QA grant, temporarily enables `send_access_email = yes` in DropConfig, triggers canonical `EmailAccessGranted::trigger()` with decrypted test identity, asserts return token creation in `wp_statement_access_tokens`, and restores original DropConfig in `finally`.
  - Added `QaTestService::run_terminal_lifecycle_test()`: Revalidates `TEST — Terminal Jacket` (`TEST-TJ01-ARC`), asserts unpurchasable in `SOLD_OUT`, transitions to `ARCHIVED` via canonical `Metadata::set_release_state()`, asserts unpurchasable in `ARCHIVED` regardless of inventory, asserts illegal reversal to `LIVE` is blocked, and leaves entity in `ARCHIVED`.
  - Added UI triggers for `RUN ACCESS EMAIL TEST` and `REVALIDATE TERMINAL LIFECYCLE` to Section 3 of `AdminPage.php`.
- **Verification**:
  - Package: `dist/statement-integration-fixtures-0.3.3.zip` (27,311 bytes, SHA-256: `7d0d8dc20681ad31166835fcd414bc793fe4c185a64d7a0e618572741bf90bac`).
  - PHP syntax, bootstrap unit tests (19 assertions), and QA contract tests (9 assertions) pass clean.

### `M14-EVIDENCE-01`: Storefront Hardening & Theme 0.13.0-rc.3 Integration

- **Private Access Gate Styling**: Added dedicated luxury editorial typography and responsive form styling to `assets/css/catalog.css` (`.statement-access-gate`), featuring `"Instrument Serif"` display headings, subtle luxury uppercase inputs, high-contrast ink-navy CTA buttons, and accessible focus states.
- **Header & Navigation Hardening**: Verified keyboard accessibility, `aria-controls`, `aria-expanded` attributes, responsive mobile drawer toggle, and WooCommerce Bag counter integration.

### `M14-EVIDENCE-02`: Full M14 Storefront Hardening, Theme 0.13.0-rc.4 Bump, and M15 Launch Readiness

- **Editorial Fallback Navigation**: Added `get_shop_url()`, `get_archive_url()`, `render_primary_navigation()`, and `render_mobile_primary_navigation()` in `inc/navigation.php` to provide graceful luxury fallback links when no WordPress primary menu is assigned.
- **Dialog Navigation JS**: Enhanced `assets/js/navigation.js` with backdrop click close, body scroll locking via `.statement-dialog-open`, and window resize event listener to automatically dismiss open mobile menus on viewport expansion.
- **Design System & WooCommerce Notices**: Standardized `.woocommerce-message`, `.woocommerce-info`, `.woocommerce-error`, `.statement-badge` (live, sold out, archived) in `assets/css/base.css`.
- **Checkout & Order Presentation**: Fully styled Order Received, My Account, Order details, and provenance presentation in `assets/css/checkout.css`.
- **Cart Responsive Protection**: Added 320px viewport media query with `overflow-wrap: anywhere` in `assets/css/cart.css`.
- **Theme Version Bump**: Promoted theme candidate from `0.13.0-rc.3` to `0.13.0-rc.4` across `style.css`, `functions.php`, packaging scripts, and tests.
- **M15 Launch Readiness Documentation Suite**: Created 8 comprehensive operational guides in `docs/`: `final-fixture-cleanup.md`, `seo-launch-checklist.md`, `shipping-launch-readiness.md`, `payment-launch-checklist.md`, `email-launch-readiness.md`, `legal-content-gap-inventory.md`, `production-plugin-audit.md`, `final-rc-manifest.md`.
- **Authoritative Candidate Checksums (Reconciled)**:
  - Theme Candidate `0.13.0-rc.4`: `statement-collector-theme-0.13.0-rc.4.zip` (44,858 bytes, SHA-256: `d3053c00d3674666cfd870b8557cfde90e70d47be2a6c0af25a0183c31d3e1bf`).
  - Core Candidate `0.13.0-rc.9`: `statement-collector-core-0.13.0-rc.9.zip` (66,782 bytes, SHA-256: `6e1ab1ea2571c757852c299631834f4aa5f59040e7afe021e18cb0d803e4c3d4`).
  - Fixtures Tool `0.3.3`: `statement-integration-fixtures-0.3.3.zip` (27,311 bytes, SHA-256: `97fbb481613fc619434e87b5d81fb3815ab7b690bd2d1e80e94dbf547ec70850`).
- **Test Suite Expansion**: Comprehensive 14-test suite in `tests/m14-theme-hardening.test.mjs` verifying design tokens, fallback nav, dialog a11y, search privacy, homepage LIVE-only bounds, catalog card aspect ratio, archive permanence, PDP lifecycle locks, cart 320px overflow protection, checkout hooks, order provenance presentation, scarcity invariant, and exact four WooCommerce template overrides.

### `M15-EVIDENCE-01`: Live Storefront QA & Production Preparation Runbooks

- **Live Storefront QA Execution**: Audited `/`, `/shop/`, `/product/test-studio-overshirt/`, `/product/test-monogram-jacket/`, `/product/test-terminal-jacket/`, `/drop/test-private-drop-01/`, `/product/test-private-access-jacket/`, `/cart/`, and `/checkout/` on Atomic.
- **Anonymous Security Boundary Re-verified**:
  - `Store API /wc/store/v1/products`: Zero private product leakage (`test-private-access-jacket` absent).
  - `REST API /wp/v2/product`: Zero private product leakage.
  - `Frontend Search /?s=...`: Zero private product cards in loop.
  - `Private PDP /product/test-private-access-jacket/`: Verified true HTTP 404.
  - `Private Drop /drop/test-private-drop-01/`: Minimal access gate rendered without product leakage.
- **Live PDP Verification**:
  - Simple PDP (`test-studio-overshirt`): HTTP 200, Add to Bag present.
  - Variable PDP (`test-monogram-jacket`): HTTP 200, WooCommerce variation form active, Add to Bag present.
  - Terminal PDP (`test-terminal-jacket`): HTTP 200, Add to Bag absent, Sold Out / Archived indicators active.
- **Production Launch Runbook Suite**:
  - Created `docs/production-product-import-plan.md` defining schema, variations, AUD pricing, inventory, and scarcity rules for debut Statement Drop 001.
  - Created `docs/drop-001-launch-runbook.md` detailing operational management across Private Access, Live transition, Sold Out lock, and permanent archive.
  - Created `docs/final-release-runbook.md` specifying complete sequential cutover protocol (post-RC backup -> Jetpack scan -> fixture purge & removal -> production configuration -> live card test -> stable release tagging -> public launch).

### `M13-EVIDENCE-03`: Fixtures 0.3.3 Live Atomic Deployment & Real Expiry/Reminder Runtime Verification

- **Fixtures 0.3.3 Active on Atomic**: Verified `Statement Integration Fixtures (v0.3.3)` installed and active on `https://mystatement.store/`.
- **Private Access Test Fixture Adoption**: Successfully adopted and recovered test fixture (`Product ID: 213`, `Drop ID: 1376`).
- **Secret Vault & Identity Status**: Secret Provider: `ENCRYPTED VAULT`, Status: `INITIALIZED`, Identity Key: `CONFIGURED`, Rate-Limit Key: `CONFIGURED`.
- **Expiry Rules Runtime Verification (`RUNTIME_PASS`)**:
  - Executed `RUN EXPIRY TEST` on Atomic.
  - Verified: "Expiry rules verified: Earlier close dynamically shortened effective authorization; later close did not extend immutable grant; original Drop config restored clean."
  - Proved: `EARLIER_CLOSE_SHORTENED = YES`, `LATER_CLOSE_EXTENDED_GRANT = NO`, `ORIGINAL_CONFIG_RESTORED = YES`.
- **Reminder Scheduler Runtime Verification (`RUNTIME_PASS`)**:
  - Executed `RUN REMINDER TEST` on Atomic.
  - Verified: "Reminder Scheduler verified: Action scheduled cleanly and auto-cancelled upon Add to Bag with valid session context."
- **Production Pre-Cutover Verification Scripts**:
  - Created `scripts/verify-production-clean-state.mjs` (read-only verification of post-purge clean state).
  - Created `scripts/test-production-readiness.mjs` (read-only smoke test across storefront routes: 6/6 passed, 0 fatals).
