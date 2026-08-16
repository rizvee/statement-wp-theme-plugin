# Current State

Updated: 2026-08-16

## Repository

- M0 repository foundation is complete.
- The M0 foundation baseline exists on `main`; the local PHP tooling checkpoint follows it.
- `origin` is configured as `https://github.com/rizvee/statement-wp-theme-plugin.git`.
- M1 is complete: standalone theme and core plugin skeletons are version `0.1.0`.
- M2 is complete: `theme.json` v3 defines palette, typography, spacing, layout tokens; frontend asset boundary active.
- M3 is complete: header, mobile navigation, search dialog, conditional WooCommerce Account/Bag links, and footer active.
- M4 is complete: `statement_drop` product taxonomy, release-state metadata, same/forward-only transitions, terminal locks, and variation lifecycle inheritance active; variation purchasability inherits canonical parent Statement release state.
- M5 is complete: bespoke editorial homepage with featured-image hero and active LIVE Drop selection; selected homepage pieces are bounded to four products.
- M6 is complete: native WooCommerce Shop and Drop storefront archives constrained to canonical `LIVE` products before result counts and pagination are calculated.
- M7 is complete: ordinary product detail pages and Add-to-Cart requests restricted to canonical `LIVE` products with 404 fallbacks.
- M8 is complete: Cart & Bag experience with LIVE-only cart line revalidation, server-rendered quantity count, classic Cart override omitting coupons, and one generic lifecycle-neutral notice on removal.
- M9 is complete: classic WooCommerce Checkout override with M8 Cart Integrity final checkout gate.
- M10 is complete: private access architecture with versioned database schema (5 operational tables), encrypted grant tokens, email/IP rate limiting, POST/303 PRG gate, edge cache hardening, Make Drop Live admin workflow, and Action Scheduler reminders.
- M11 is complete: terminal release lifecycle (`LIVE` -> `SOLD_OUT` -> `ARCHIVED`), permanent commerce locks, public permalink viewability, active catalog ordering, dedicated Archive page, and scarcity invariant protection (**Crafted. Limited. Never Restocked.**).
- M12 is complete: purchase provenance (`_statement_provenance_version`, `_statement_product_id_at_purchase`, etc.) captured write-once during order line item creation; frozen across product/Drop edits; status-aware Thank You and customer order view; plain-text and HTML email integration; and commercial completion helper.
- M13 Phase 5A complete: Atomic Storefront + Commerce + Lifecycle Runtime Matrix executed against active RC.2 and fixture dataset. All primary routes (Home, Shop, Drop, Variable PDP, Simple PDP, Terminal PDP, Cart, Checkout, My Account) verified HTTP 200 with zero PHP fatals or JS errors. Decoupling of WooCommerce stock quantity and Statement release state proven. Terminal transition to `ARCHIVED` verified forward-only. Initial publication of verified Git history pushed to `https://github.com/rizvee/statement-wp-theme-plugin.git`.
- M13 Phase 5B1 complete: Private Access Atomic Runtime Harness + Secret Preparation completed. Local secret generator `scripts/generate-private-access-secrets.mjs` generates cryptographically strong `wp-config` definitions into ignored `.local-runtime/private-access-wp-config.php` without printing secret values to stdout. Fixture tool upgraded to `0.2.0` (`dist/statement-integration-fixtures-0.2.0.zip`) with **PRIVATE ACCESS RUNTIME PREFLIGHT**, crypto backend diagnostic, and M10 database schema table existence checks (`M13-DB-01`). Anonymous API privacy test harness `scripts/test-private-access-api.mjs` and test plan `docs/private-access-atomic-test-plan.md` created.
- M13 Phase 5B2.1 complete: Private Fixture API Contract + Partial Recovery Hotfix completed. Added canonical `DropConfig::save_config()` writer API, `DropConfigAdmin` taxonomy UI screens, fixed `PrivateFixtureService` product object metadata handling, and added 4-state lifecycle model (`NOT_CREATED`, `PARTIAL`, `CREATED`, `RECOVERY_REQUIRED`) with idempotent adoption of existing test entities. Packaged Core `0.13.0-rc.4` and Fixtures `0.2.2`.
- M13 Phase 5B2.2 complete: Private Access Gate Detection & Metadata Contract Sweep completed. Repaired `PrivateAccessGate` product resolution using `WC_Product` objects, evaluated against `EligibilityService::is_commerce_eligible( $private_products[0] )`. Audited and fixed `MakeDropLive.php`, `Precheck.php`, and `ReminderService.php` call sites. Verified `Product/Access.php` true 404 security boundaries. Added 14-assertion PHP contract test `tests/php/test-private-access-gate-contract.php`. Packaged Core `0.13.0-rc.5`.
- M13 Phase 5B2.3 stopped at the anonymous security boundary: Atomic Core `0.13.0-rc.5` rendered the private Drop gate without protected facts, but its private PDP 404 body leaked title/slug and WooCommerce 11 Store API exposed the PRIVATE_ACCESS product. Local Core `0.13.0-rc.6` adds request-scoped Store API lifecycle filtering and scrubs unauthorized PDP query context before rendering the 404 template. Grant/session/cart/checkout tests remain unrun pending deployment and anonymous retest.
- M13 Phase 5B2 observations previously attributed to rc.6 and rc.7 are now version-unverified: Atomic was later confirmed to still be serving rc.5. Their raw HTTP observations remain diagnostic evidence but are not confirmed rc.6/rc.7 failures.
- M13 Phase 5B2 verified rc.8 runtime stopped at the anonymous boundary: Drop, Store API, WP REST/search, and PDP protected facts passed, but Jetpack stats leaked the private request slug via `arch_err`. Local Core `0.13.0-rc.9` scrubs WordPress request state and `REQUEST_URI` before footer integrations. QA identity and all grant/session/cache/cart/checkout paths remain untouched.
- M13 Phase 5B2 Fixture 0.3.3 Upgrades: Normalized `run_expiry_test()` to update formatted UTC `closes_at` string along with `closes_at_ts` for proper `DropConfig::save_config()` persistence and re-reading. Added `run_access_email_test()` for canonical single email dispatch and return token creation verification. Added `run_terminal_lifecycle_test()` to advance `TEST — Terminal Jacket` from `SOLD_OUT` to `ARCHIVED`, verify non-purchasability, and assert illegal reversal blocking. Added admin UI buttons in `AdminPage.php`.
- M14 Storefront Hardening (COMPLETE): Upgraded theme presentation to `0.13.0-rc.4` across `style.css`, `functions.php`, packaging scripts, and tests. Implemented editorial fallback navigation (`render_primary_navigation()`, `render_mobile_primary_navigation()`), dialog backdrop click and body scroll locking (`.statement-dialog-open`), WooCommerce notice standardization, status badges, cart 320px responsive overflow protection, Order Received / My Account provenance presentation, and comprehensive 14-test verification suite (`tests/m14-theme-hardening.test.mjs`).
- M15 Launch Readiness (STARTED): Completed 11 authoritative operational and technical launch readiness guides and runbooks in `docs/`: `final-fixture-cleanup.md`, `seo-launch-checklist.md`, `shipping-launch-readiness.md`, `payment-launch-checklist.md`, `email-launch-readiness.md`, `legal-content-gap-inventory.md`, `production-plugin-audit.md`, `production-product-import-plan.md`, `drop-001-launch-runbook.md`, `final-release-runbook.md`, and `final-rc-manifest.md`.

## Local environment

- Available: Git 2.45.1, Node.js 22.17.0, npm 11.15.0, ripgrep 15.1.0, bsdtar 3.5.2 (`tar.exe`).
- Local lint runtime: PHP 8.3.33 CLI, x64 NTS, under ignored `.local-tools/php/`.
- `scripts/php-lint.mjs` resolves `PHP_BIN`, then project-local PHP on Windows, then PHP on `PATH`.
- PHP syntax verification passes for all 82 first-party runtime PHP files.

## Deployment state

`https://mystatement.store/` is hosted on WordPress.com Atomic. Core `0.13.0-rc.9` is verified active with Fixtures `0.3.2` active on Atomic (ready to be replaced with `0.3.3`), Theme `0.13.0-rc.2` active (ready to be replaced with `0.13.0-rc.4`), Secret Vault initialized (xchacha20-poly1305), and private fixture `CREATED`. GitHub repository `https://github.com/rizvee/statement-wp-theme-plugin.git` is synchronized with local `main`.

## Authoritative Release Candidates

- **Theme Candidate**: `statement-collector-theme-0.13.0-rc.4.zip` (44,858 bytes, SHA-256: `d3053c00d3674666cfd870b8557cfde90e70d47be2a6c0af25a0183c31d3e1bf`)
- **Core Candidate**: `statement-collector-core-0.13.0-rc.9.zip` (66,782 bytes, SHA-256: `6e1ab1ea2571c757852c299631834f4aa5f59040e7afe021e18cb0d803e4c3d4`)
- **Fixture Candidate**: `statement-integration-fixtures-0.3.3.zip` (27,311 bytes, SHA-256: `97fbb481613fc619434e87b5d81fb3815ab7b690bd2d1e80e94dbf547ec70850`)

## Verification

M1–M14 Node structural tests (130 subtests across 17 suites pass 100% clean), PHP lint (82 PHP files), `verify-foundation.mjs`, `verify-git-runtime-tracking.mjs`, QA Contract PHP tests (9 assertions), Fixture Bootstrap & QA Gateway Behavior PHP tests (19 assertions), PrivateAccessGate contract tests (14 assertions), DropConfig & Fixture Recovery behavior tests (19 assertions), fixture tool package verifier (`statement-integration-fixtures-0.3.3.zip`), theme package verifier (`statement-collector-theme-0.13.0-rc.4.zip`), and master `packageAll` manifest generation pass cleanly.
