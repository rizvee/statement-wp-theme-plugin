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
- M13 Phase 1 & 2A complete: deterministic packaging scripts (`package-theme.mjs`, `package-plugin.mjs`, `package-all.mjs`), package verification (`verify-package.mjs`), version authority enforcement (`0.13.0-rc.2`), package manifest (`dist/manifest.json`), operator preflight checklist (`docs/atomic-integration-preflight.md`), emergency rollback runbook (`docs/atomic-rollback-runbook.md`), runtime integration checklist (`docs/runtime-integration-checklist.md`), issue log (`docs/runtime-integration-log.md`), release candidate record (`docs/runtime-release-candidate.md`), context document (`.ai/context/runtime-integration-m13.md`), and packaging test suite (`tests/m13-packaging.test.mjs`).
- **Phase 2A STOP GATE**: RC-2 candidate artifacts generated locally (`dist/statement-collector-core-0.13.0-rc.2.zip` and `dist/statement-collector-theme-0.13.0-rc.2.zip`). Manual upload/replacement of installed version `0.1.0` on WordPress.com Atomic requires EXPLICIT HUMAN OPERATOR APPROVAL.

## Local environment

- Available: Git 2.45.1, Node.js 22.17.0, npm 11.15.0, ripgrep 15.1.0, bsdtar 3.5.2 (`tar.exe`).
- Local lint runtime: PHP 8.3.33 CLI, x64 NTS, under ignored `.local-tools/php/`.
- `scripts/php-lint.mjs` resolves `PHP_BIN`, then project-local PHP on Windows, then PHP on `PATH`.
- PHP syntax verification passes for all 71 first-party runtime PHP files.

## Deployment state

`https://mystatement.store/` is hosted on WordPress.com Atomic. Core plugin `statement-collector-core` is installed and active (initial upload reported version `0.1.0` due to header defect `M13-ISSUE-02`). Corrected candidate `0.13.0-rc.2` is verified locally in `dist/` and ready for upload.

## Verification

M1–M13 Node structural tests (99 subtests + packaging tests including version mismatch negative test), PHP assertions (40 assertions in `m12-collector-provenance.php`), PHP lint (71 PHP files), `verify-foundation.mjs`, and `package-all.mjs` pass cleanly.
