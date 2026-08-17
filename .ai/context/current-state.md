# Current State

Updated: 2026-08-17

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
- M11 is complete: terminal release lifecycle (`LIVE` -> `SOLD_OUT` -> `ARCHIVED`), permanent commerce locks, public permalink viewability, active catalog ordering, dedicated Archive page, and scarcity invariant protection (**Crafted. Not Mass Made.**).
- M12 is complete: purchase provenance (`_statement_provenance_version`, `_statement_product_id_at_purchase`, etc.) captured write-once during order line item creation; frozen across product/Drop edits; status-aware Thank You and customer order view; plain-text and HTML email integration; and commercial completion helper.
- M13 Phase 5A complete: Atomic Storefront + Commerce + Lifecycle Runtime Matrix executed against active RC.2 and fixture dataset. All primary routes (Home, Shop, Drop, Variable PDP, Simple PDP, Terminal PDP, Cart, Checkout, My Account) verified HTTP 200 with zero PHP fatals or JS errors. Decoupling of WooCommerce stock quantity and Statement release state proven. Terminal transition to `ARCHIVED` verified forward-only. Initial publication of verified Git history pushed to `https://github.com/rizvee/statement-wp-theme-plugin.git`.
- M13 Phase 5B1 complete: Private Access Atomic Runtime Harness + Secret Preparation completed. Local secret generator `scripts/generate-private-access-secrets.mjs` generates cryptographically strong `wp-config` definitions into ignored `.local-runtime/private-access-wp-config.php` without printing secret values to stdout. Fixture tool upgraded to `0.2.0` (`dist/statement-integration-fixtures-0.2.0.zip`) with **PRIVATE ACCESS RUNTIME PREFLIGHT**, crypto backend diagnostic, and M10 database schema table existence checks (`M13-DB-01`). Anonymous API privacy test harness `scripts/test-private-access-api.mjs` and test plan `docs/private-access-atomic-test-plan.md` created.
- M13 Phase 5B2.1 complete: Private Fixture API Contract + Partial Recovery Hotfix completed. Added canonical `DropConfig::save_config()` writer API, `DropConfigAdmin` taxonomy UI screens, fixed `PrivateFixtureService` product object metadata handling, and added 4-state lifecycle model (`NOT_CREATED`, `PARTIAL`, `CREATED`, `RECOVERY_REQUIRED`) with idempotent adoption of existing test entities. Packaged Core `0.13.0-rc.4` and Fixtures `0.2.2`.
- M13 Phase 5B2.2 complete: Private Access Gate Detection & Metadata Contract Sweep completed. Repaired `PrivateAccessGate` product resolution using `WC_Product` objects, evaluated against `EligibilityService::is_commerce_eligible( $private_products[0] )`. Audited and fixed `MakeDropLive.php`, `Precheck.php`, and `ReminderService.php` call sites. Verified `Product/Access.php` true 404 security boundaries. Added 14-assertion PHP contract test `tests/php/test-private-access-gate-contract.php`. Packaged Core `0.13.0-rc.5`.
- M13 Phase 5B2.3 stopped at the anonymous security boundary: Atomic Core `0.13.0-rc.5` rendered the private Drop gate without protected facts, but its private PDP 404 body leaked title/slug and WooCommerce 11 Store API exposed the PRIVATE_ACCESS product. Local Core `0.13.0-rc.6` adds request-scoped Store API lifecycle filtering and scrubs unauthorized PDP query context before rendering the 404 template. Grant/session/cart/checkout tests remain unrun pending deployment and anonymous retest.
- M13 Phase 5B2 verified rc.8 runtime stopped at the anonymous boundary: Drop, Store API, WP REST/search, and PDP protected facts passed, but Jetpack stats leaked the private request slug via `arch_err`. Local Core `0.13.0-rc.9` scrubs WordPress request state and `REQUEST_URI` before footer integrations. QA identity and all grant/session/cache/cart/checkout paths remain untouched.
- M13 Phase 5B2 Fixture 0.3.3 Upgrades & Live Execution: Upgraded fixtures to 0.3.3 on Atomic (`https://mystatement.store/`). Verified `RUN EXPIRY TEST` with `RUNTIME_PASS` (earlier close shortened effective authorization, later close preserved immutable grant, original config restored). Verified `RUN REMINDER TEST` with `RUNTIME_PASS` (action scheduled cleanly and auto-cancelled on Add to Bag).
- M14 Storefront Hardening (COMPLETE): Upgraded theme presentation to `0.13.0-rc.4` across `style.css`, `functions.php`, packaging scripts, and tests. Verified live on Atomic. Implemented editorial fallback navigation, dialog backdrop click and body scroll locking, WooCommerce notice standardization, status badges, cart 320px responsive overflow protection, Order Received / My Account provenance presentation, and comprehensive verification suite (`tests/m14-theme-hardening.test.mjs`).
- Visual Sprint 01 & 02 (COMPLETE): Theme upgraded to `0.13.0-rc.6`. Audited real brand assets from WhatsApp photo archive. Built Statement Client Demo Plugin v0.1.0 (`tools/statement-client-demo/`) for idempotent, manifest-driven demo content seeding with rollback preservation. Overhauled homepage with luxury Lookbook magazine grid and Brand Object ("The Object") packaging section. Added dedicated `/drops/` directory (`page-drops.php`), accessible Size Guide dialog on PDP with CM measurements table, and high-priority front-page template ownership guards (P0 Elementor isolation). Standardized all customer-facing branding to **"CRAFTED. NOT MASS MADE."**
- Visual Sprint 03 / Overnight Mega Build (COMPLETE): Theme promoted to `0.13.0-rc.7`, Core promoted to `0.13.0-rc.10`, Client Demo promoted to `0.2.0`. Strictly locked homepage release contract (6-part layout: Header, Hero, Active Drop, Featured 2 Pieces, Email Capture, Minimal Footer). Migrated off-home editorial sections (Lookbook, Brand Object, Principle) to dedicated Journal template (`single.php` / `page.php`). Implemented Typographical Brand Wordmark `STATEMENT`, responsive focal positioning (76svh hero), public catalog query isolation against QA fixtures (`Visibility::filter_public_catalog_posts_clauses`), repaired `/drops/` index, built production Mode A/B/C Email Signup Engine with SecretVault encryption (`SignupService.php`), implemented privileged Inventory Lifecycle v2 Admin overrides (`LifecycleV2Admin.php`) with structured audit logs, enforced strict Client Demo deterministic ownership (`_statement_client_demo = 1` and `STMT-CD-D001-*` SKUs) with collision repair, and slimmed theme ZIP to ~548KB.

## Local environment

- Available: Git 2.45.1, Node.js 22.17.0, npm 11.15.0, ripgrep 15.1.0, bsdtar 3.5.2 (`tar.exe`).
- Local lint runtime: PHP 8.3.33 CLI, x64 NTS, under ignored `.local-tools/php/`.
- `scripts/php-lint.mjs` resolves `PHP_BIN`, then project-local PHP on Windows, then PHP on `PATH`.
- PHP syntax verification passes for all 97 first-party runtime and demo PHP files.

## Deployment state

`https://mystatement.store/` is hosted on WordPress.com Atomic. Core `0.13.0-rc.9` is active with Fixtures `0.3.3` active on Atomic, Theme `0.13.0-rc.4` active, Secret Vault initialized (xchacha20-poly1305), and private fixture adopted and recovered (`Product ID: 213`, `Drop ID: 1376`). Synchronized with GitHub `https://github.com/rizvee/statement-wp-theme-plugin.git`.

- **Theme Candidate**: `statement-collector-theme-0.13.0-rc.7.zip` (548,949 bytes, SHA-256: `687e51a2b31b87e5f407ea0982d10d593984590711055a3fccc563b0a14c7b09`)
- **Core Candidate**: `statement-collector-core-0.13.0-rc.10.zip` (72,593 bytes, SHA-256: `2aaa184bf3d1d00707eaeecc32cb79daf202e8ca550e99424a47e70bc920ea45`)
- **Client Demo Candidate**: `statement-client-demo-0.2.0.zip` (4,050,773 bytes, SHA-256: `60eb50fbd1b7c6b5d20f1de366c54a89e0974a2d6d198f822cd7758f621a11b5`)
- **Fixture Candidate**: `statement-integration-fixtures-0.3.3.zip` (27,311 bytes, SHA-256: `97fbb481613fc619434e87b5d81fb3815ab7b690bd2d1e80e94dbf547ec70850`)

## Verification

M1–M16 Node structural tests (136 subtests across 19 suites pass 100% clean), PHP lint (97 PHP files), `verify-foundation.mjs`, `verify-git-runtime-tracking.mjs`, QA Contract PHP tests (9 assertions), Fixture Bootstrap & QA Gateway Behavior PHP tests (19 assertions), PrivateAccessGate contract tests (14 assertions), DropConfig & Fixture Recovery behavior tests (19 assertions), Client Demo seeder tests, theme package verifier (`statement-collector-theme-0.13.0-rc.7.zip`), core plugin package verifier (`statement-collector-core-0.13.0-rc.10.zip`), client demo package verifier (`statement-client-demo-0.2.0.zip`), and master `packageAll` manifest generation pass cleanly.
