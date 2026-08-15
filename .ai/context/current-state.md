# Current State

Updated: 2026-08-15

## Repository

- M0 repository foundation is complete.
- The M0 foundation baseline exists on `main`; the local PHP tooling checkpoint follows it.
- `origin` is configured as `https://github.com/rizvee/statement-wp-theme-plugin.git`; it has not been pushed.
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

## Local environment

- Available: Git 2.45.1, Node.js 22.17.0, npm 11.15.0, ripgrep 15.1.0, bsdtar 3.5.2 (`tar.exe`).
- Local lint runtime: PHP 8.3.33 CLI, x64 NTS, under ignored `.local-tools/php/`.
- `scripts/php-lint.mjs` resolves `PHP_BIN`, then project-local PHP on Windows, then PHP on `PATH`.
- PHP syntax verification passes for all 79 first-party runtime PHP files.

## Deployment state

`https://mystatement.store/` is hosted on WordPress.com Atomic. Core candidate `0.13.0-rc.5` packaged in `dist/` and ready for operator upload. Atomic currently runs Core `0.13.0-rc.4` + Fixtures `0.2.2` with Secret Vault initialized and private fixture `CREATED`. GitHub repository `https://github.com/rizvee/statement-wp-theme-plugin.git` is synchronized with local `main`.

## Verification

M1–M13 Node structural tests (116 subtests), PHP lint (79 PHP files), `verify-foundation.mjs`, `verify-git-runtime-tracking.mjs`, PrivateAccessGate contract tests (14 assertions), DropConfig & Fixture Recovery behavior tests (19 assertions), fixture tool package verifier, and GitHub remote synchronization pass cleanly.
