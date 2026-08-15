# Milestones

## M0 — Repository foundation

Status: complete (2026-08-13)

- [x] Audit the initially empty repository and local Git/tool state.
- [x] Initialize local Git without a remote or commit.
- [x] Establish concise AI context, repository hygiene, and deployment safeguards.
- [x] Create empty theme/plugin runtime roots without feature implementation.
- [x] Add and run lightweight structure, secret, scope, and PHP-lint discovery checks.

## M1 — Theme Skeleton + Core Plugin Skeleton

Status: complete (2026-08-14)

- [x] Add the minimum valid standalone theme skeleton at version `0.1.0`.
- [x] Add the minimum valid core plugin skeleton at version `0.1.0`.
- [x] Establish separated bootstraps, graceful WooCommerce absence behavior, structural tests, PHP lint, and a narrow bootstrap smoke test.
- [x] Keep storefront, Drop, private-access, archive, and inventory-domain features out of scope.

## M2 — Design System + Global Shell

Status: complete (2026-08-14)

- [x] Add `theme.json` v3 with the approved palette, restrained typography, spacing, and layout tokens.
- [x] Add accessible global base/layout CSS and an isolated frontend asset loader.
- [x] Add the minimum WordPress document shell without branded header, navigation, or footer components.
- [x] Preserve plugin and commerce/domain scope boundaries.

## M3 — Header + Mobile Navigation + Footer

Status: complete (2026-08-14)

- [x] Add the sticky desktop/mobile header with centered native site identity and WordPress-driven menus.
- [x] Add accessible native-dialog navigation and search interactions with lightweight vanilla JavaScript.
- [x] Add conditional WooCommerce Account/Bag links without cart behavior or hardcoded routes.
- [x] Add the restrained WordPress-driven footer and verify M1–M3 regressions.

## M4 — Drop Architecture + Product Metadata

Status: complete; integrity hardened (2026-08-14)

- [x] Register the `statement_drop` product taxonomy with a controlled one-Drop admin field.
- [x] Add canonical release-state and optional edition-label metadata through WooCommerce CRUD.
- [x] Enforce same/forward-only lifecycle transitions and terminal purchasability locks.
- [x] Verify malformed input, WooCommerce absence, and positive-stock terminal products.
- [x] Inherit terminal parent release state for variations without duplicating lifecycle metadata.
- [x] Lock established historical Drop relationships from `PRIVATE_ACCESS` onward while retaining bounded first-assignment recovery.

## M5 — Homepage

Status: complete (2026-08-14)

- [x] Add the bespoke editorial homepage with featured-image hero and native page-content zone.
- [x] Add a minimal read-only core presentation API and restrict normal public exposure to `LIVE`.
- [x] Select one deterministic LIVE Drop and up to four legitimate products through a bounded WooCommerce query.
- [x] Add omittable empty states, conditional homepage CSS, privacy tests, and dependency-absence coverage.

## M6 — Shop + Drop Storefront

Status: complete (2026-08-14)

- [x] Restrict public Shop and `statement_drop` main queries to canonical `LIVE` products before pagination.
- [x] Keep WooCommerce's native Shop query/pagination and the taxonomy relationship authoritative.
- [x] Add a shared Home/Shop/Drop product card, restrained catalog styling, and privacy-safe empty states.
- [x] Preserve WooCommerce absence safety and exclude Add to Cart, quick-commerce, and later access/archive behavior.

## M7 — Product Detail Page

Status: complete (2026-08-14)

- [x] Restrict ordinary direct product pages and crafted Add-to-Cart requests to canonical `LIVE` products.
- [x] Add a focused native WooCommerce product composition for gallery, provenance, price, description, and purchase form.
- [x] Preserve native simple/variable purchase mechanics without custom variation JavaScript or cart behavior.
- [x] Add conditional responsive product CSS, lifecycle/privacy regression tests, and WooCommerce-absence coverage.

## M8 — Cart + Bag Experience

Status: complete (2026-08-14)

- [x] Keep the direct Bag link and add a safe server-rendered WooCommerce quantity count without fragments or a drawer.
- [x] Revalidate restored and current cart lines against canonical `LIVE`, including variation parent ownership.
- [x] Add the restrained classic WooCommerce Cart presentation while retaining native quantity, removal, totals, and checkout routing.
- [x] Omit Cart coupon entry and cross-sell presentation without changing WooCommerce data or global settings.

## M9 — Checkout

Status: complete (2026-08-14)

- [x] Reuse M8 Cart Integrity as the final canonical `LIVE` checkout gate and emit a blocking lifecycle-neutral error when stale lines are removed during checkout processing.
- [x] Add one focused classic WooCommerce Checkout override with native customer fields, order review, payment, shipping, validation, and order processing intact.
- [x] Add responsive Statement Checkout CSS, omit only coupon-entry presentation, and introduce no first-party checkout JavaScript.
- [x] Preserve order-pay/order-received flows and keep payment gateways, shipping methods, account policy, and private access out of scope.

## M10 — Private Access

Status: complete (2026-08-14)

- [x] Add versioned schema migration manager with 5 operational tables.
- [x] Add secret configuration and authenticated encryption with keyring versioning.
- [x] Add HMAC email and IP rate-limit identity hashing.
- [x] Add immutable grant expiry logic and 5-active session cap per grant.
- [x] Add single-use access return and marketing unsubscribe tokens.
- [x] Add rolling IP and email rate limiting thresholds.
- [x] Add append-only marketing consent event tracking.
- [x] Add centralized commerce eligibility service reused across product detail, add-to-bag, cart integrity, checkout, and order audit.
- [x] Add POST/303 PRG private access gate with private cache control and noindex headers.
- [x] Add Make Drop Live admin action with read-only preflight and atomic transition.
- [x] Add WooCommerce Action Scheduler marketing reminders with Add-to-Bag auto-cancellation.
- [x] Add WooCommerce admin grant management UI with masked email reporting and re-grant support.
- [x] Add privacy data retention cleanup for expired tokens, rate limits, and inactive grants.

## M11 — Archive & Terminal Presentation

Status: complete (2026-08-15)

- [x] Enforce forward-only terminal transitions (`LIVE` -> `SOLD_OUT` -> `ARCHIVED`) and reject invalid backward mutations.
- [x] Establish permanent commerce lock override so positive WooCommerce stock cannot restore purchasability for `SOLD_OUT` or `ARCHIVED` products.
- [x] Maintain permanent direct product permalink viewability for `SOLD_OUT` and `ARCHIVED` items without returning 404s.
- [x] Update active catalog queries to include `LIVE` and `SOLD_OUT` items ordered with `LIVE` first, while excluding `ARCHIVED` items for dedicated Archive presentation.
- [x] Resolve fully historical Drop pages (`is_past_drop`) to render their `ARCHIVED` items instead of leaving them empty.
- [x] Omit Add to Bag forms and quantity selectors on terminal product pages, rendering restrained status badges (**SOLD OUT** or **ARCHIVED**).
- [x] Add dedicated `page-archive.php` template displaying archived product grids and historical past Drop terms.
- [x] Filter WooCommerce structured data offer availability to `OutOfStock` for terminal items.
- [x] Protect core brand scarcity model (**Crafted. Limited. Never Restocked.**) with zero waitlist or restock logic.

## M12 — Collector Provenance & Order Experience

Status: complete (2026-08-15)

- [x] Create write-once purchase provenance service capturing schema version, product ID, variation ID, Drop ID, Drop name, edition label, piece title, release state, and timestamp during order line item creation.
- [x] Enforce immutability so subsequent product edits, Drop name changes, or source deletions do not alter historical order item provenance.
- [x] Maintain explicit boundary separating order purchase provenance from certified collector ownership and authenticity certificate generation.
- [x] Coexist cleanly with M10 Private Access audit metadata without duplicating PII, session tokens, or payment secrets.
- [x] Provide future-facing commercial completion helper (`Completion::is_commercially_completed`) evaluating `processing` and `completed` order statuses.
- [x] Add read-only Statement Provenance presentation to WooCommerce admin order item screens with zero edit inputs.
- [x] Enhance customer order details and Order Received (Thank You) experience with status-aware banners and "Continue Exploring" navigation.
- [x] Enrich WooCommerce customer transactional emails with frozen provenance data independent of marketing unsubscribe flags.
- [x] Maintain strict scarcity model (**Crafted. Limited. Never Restocked.**) with zero collector numbers, production caps, or certificate generation.

## M13 — Runtime Integration Preparation & Verification

Status: Phase 1, Phase 2, Phase 3A, Phase 4A, Phase 5A, Phase 5B1, & Phase 5B2.1 complete (2026-08-15)

- [x] Create deterministic packaging scripts (`package-theme.mjs`, `package-plugin.mjs`, `package-all.mjs`) generating single-root ZIP artifacts in `dist/`.
- [x] Implement package content verification (`verify-package.mjs`) checking root directory, required runtime files, packaged PHP linting, and dev/secret exclusions.
- [x] Enforce package exclusion rules for `.git/`, `.github/`, `.ai/`, `tests/`, `scripts/`, `docs/`, `.local-tools/`, `node_modules/`, `dist/`, `coverage/`, `tmp/`, `logs/`, `php.ini`, `.env`, `package-lock.json`, and OS/IDE metadata.
- [x] Support candidate versioning (`0.13.0-rc.2`) and generate `dist/manifest.json` with SHA-256 checksums and `deployment_authorized = false`.
- [x] Create operator preflight checklist (`docs/atomic-integration-preflight.md`) for WordPress, WooCommerce, theme/plugin, and secret configuration.
- [x] Create emergency rollback runbook (`docs/atomic-rollback-runbook.md`) covering plugin, theme, checkout, and private edge cache failures.
- [x] Establish structured integration test checklist (`docs/runtime-integration-checklist.md`) with 19 test cases (`M13-PA-01` through `M13-RS-01`).
- [x] Create defect tracking log template (`docs/runtime-integration-log.md`) and release candidate record template (`docs/runtime-release-candidate.md`).
- [x] Build dedicated Node packaging test suite (`tests/m13-packaging.test.mjs`) validating zero remote deployment calls and manifest safety.
- [x] Phase 2: Verify `statement-collector-core` candidate `0.13.0-rc.2` bootstrap on Atomic.
- [x] Phase 3A: Build temporary integration fixture plugin `statement-integration-fixtures` (`0.1.1`) and seed/verify LIVE/SOLD_OUT dataset on Atomic.
- [x] Phase 4A: Perform local theme runtime-readiness audit and contract verification (`docs/theme-atomic-runtime-readiness.md` and `tests/m13-theme-runtime-readiness.test.mjs`).
- [x] Phase 5A: Execute Atomic Storefront + Commerce + Lifecycle Runtime Matrix across all 9 primary routes. Decouple WooCommerce stock from Statement release state. Verify forward-only `ARCHIVED` lifecycle transition. Publish verified local Git repository history to GitHub remote `https://github.com/rizvee/statement-wp-theme-plugin.git`.
- [x] Phase 5B1: Build Private Access Atomic Runtime Harness & Secret Preparation. Create local secret generator `scripts/generate-private-access-secrets.mjs` writing to ignored `.local-runtime/private-access-wp-config.php` without printing secret values. Upgrade fixture tool to `0.2.0` (`dist/statement-integration-fixtures-0.2.0.zip`) adding **PRIVATE ACCESS RUNTIME PREFLIGHT**, crypto backend diagnostic, and M10 database schema table existence checks (`M13-DB-01`). Build anonymous API test harness `scripts/test-private-access-api.mjs` and test plan `docs/private-access-atomic-test-plan.md`.
- [x] Phase 5B1.2: Fix `.gitignore` tracking for `src/Access/Secrets.php` runtime source and add `scripts/verify-git-runtime-tracking.mjs`.
- [x] Phase 5B2.1: Private Fixture API Contract & Partial Recovery Hotfix:
  - Added canonical `DropConfig::save_config( int $term_id, array $config ): bool` writer API with UTC conversion, duration validation, and transactional rollback.
  - Added `DropConfigAdmin` for WP Admin `statement_drop` taxonomy screen management.
  - Repaired `PrivateFixtureService` to operate on `WC_Product` objects for `Metadata::set_edition_label()` and `Metadata::set_release_state()`, with explicit `$product->save()`.
  - Implemented 4-state fixture lifecycle (`NOT_CREATED`, `PARTIAL`, `CREATED`, `RECOVERY_REQUIRED`) with idempotent adoption/recovery of existing test entities (`test-private-drop-01` / `TEST-PD01-PAJ`).
  - Packaged `statement-collector-core-0.13.0-rc.4.zip` and `statement-integration-fixtures-0.2.2.zip`.
  - Added 19-assertion PHP behavior test (`tests/php/test-drop-config-fixture-recovery.php`).
  - Full test suite green (116 Node subtests, 79 PHP files linted).
  - Pushed commit `3138927` to GitHub `main`.

- [x] Phase 5B2.2: Private Access Gate Detection & Metadata Contract Sweep:
  - Root cause confirmed: `PrivateAccessGate` passed integer product ID into object-based `Metadata::get_release_state()` API, which normalized state to `UPCOMING` and bypassed the gate.
  - Core `Metadata` call-site contract sweep completed across all Core source files.
  - Gate detection fixed: implemented `PrivateAccessGate::resolve_private_products( array $product_ids ): array` returning valid `WC_Product` objects, evaluated against `EligibilityService::is_commerce_eligible( $private_products[0] )`.
  - Audited `Product/Access.php` (confirmed true 404, nocache hardening, no product data leakage for unauthorized requests).
  - Fixed `MakeDropLive.php`, `Precheck.php`, and `ReminderService.php` product resolution and canonical `Metadata::set_release_state()` calls.
  - Added dedicated 14-assertion PHP contract test (`tests/php/test-private-access-gate-contract.php`).
  - Packaged `statement-collector-core-0.13.0-rc.5.zip` (SHA-256: `d6dfb666a0c7d6159ccf274e9624aae6ccf2053194a6f3356e5cdb9797074573`).
  - Full test suite green (116 Node subtests, 79 PHP files linted, foundation & tracking verifiers pass).

### Phase 5B2.2 Runtime Status & Next Steps

**STATUS**: CODE FIX COMPLETE / READY FOR ATOMIC RETEST

**Atomic Current**:
- Core `0.13.0-rc.4` currently deployed
- Fixture `0.2.2` deployed
- Private fixture `CREATED`
- Secret Vault `INITIALIZED`
- Frontend private Drop currently falls through to normal Drop template until `rc.5` deployment

**Next**:
- Deploy Core `0.13.0-rc.5`
- Verify vault remains initialized
- Open private Drop (`/drop/test-private-drop-01/`)
- Verify email gate renders (`PRIVATE ACCESS`, email input, `ENTER PRIVATE ACCESS`)
- Verify unauthorized private PDP (`/product/test-private-access-jacket/`) is true 404
- Resume Phase 5B2 runtime matrix

### Phase 5B2.3 Anonymous Boundary Runtime Result

**STATUS**: CODE FIX REQUIRED / GRANT TESTING STOPPED (2026-08-15)

- Cookie-free Atomic checks: private Drop gate passed (HTTP 200, no protected product facts); private PDP returned true HTTP 404 but leaked its title/slug into the 404 body; WooCommerce Store API returned the PRIVATE_ACCESS product. WP product REST and public search did not expose it.
- Added regressions for WooCommerce 11 Store API query enforcement and unauthorized PDP query-context scrubbing.
- Core `0.13.0-rc.6` fixes both proven anonymous leaks; Theme remains `0.13.0-rc.2`; Fixtures remain `0.2.2`.
- Gate post, cookie, authorized content, cache/session isolation, grant reuse, cart, and checkout were not run after the failed anonymous boundary. No email, order, payment, expiry, revocation, reminder, rate-limit exhaustion, vault reset, or fixture mutation occurred.
- Atomic state remains Core `0.13.0-rc.5` ACTIVE, Fixtures `0.2.2` ACTIVE, Vault `INITIALIZED`, private fixture `CREATED`.
- Remaining blockers: operator deployment of Core `0.13.0-rc.6`; ignored `.local-runtime/qa-email.txt` is absent and is required only after the anonymous retest passes.
- **NEXT**: Deploy `dist/statement-collector-core-0.13.0-rc.6.zip`, rerun the anonymous matrix, then create `.local-runtime/qa-email.txt` before the legitimate grant/session flow.

### Phase 5B2 Final Anonymous Retest

**STATUS**: CODE FIX REQUIRED / GRANT TESTING STOPPED (2026-08-15)

- Atomic Core `0.13.0-rc.6` retest: private Drop gate passed at HTTP 200 with no protected product facts; private PDP remained a true HTTP 404 but still rendered the private title/slug; Store API still returned the private slug/SKU. WP product REST and public search passed.
- Root runtime behavior showed the 404 falling through to the theme index loop with the private post still present, while WooCommerce 11 bypassed the query-only Store API constraint.
- Core `0.13.0-rc.7` strengthens 404 query/loop scrubbing and adds a fail-closed Store API response boundary, with focused regressions.
- Grant/session, cookie flags, authorized access, cache isolation, grant reuse, cart, and checkout were not run after the anonymous failure. QA identity was not read. Email/reminder remained OFF.
- Atomic state: Theme `0.13.0-rc.2`, Core `0.13.0-rc.6` ACTIVE, Fixtures `0.2.2` ACTIVE, private fixture `CREATED`, Vault `INITIALIZED`.
- **NEXT**: Deploy `dist/statement-collector-core-0.13.0-rc.7.zip` and rerun the anonymous hard gate before any grant/session flow.

## Later roadmap

Proceed to Phase 5B2 Private Access Atomic Runtime validation after operator completes manual upload of Core `0.13.0-rc.7` and the anonymous boundary retest passes.
