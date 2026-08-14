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

Status: complete (2026-08-14)

- [x] Register the `statement_drop` product taxonomy with a controlled one-Drop admin field.
- [x] Add canonical release-state and optional edition-label metadata through WooCommerce CRUD.
- [x] Enforce same/forward-only lifecycle transitions and terminal purchasability locks.
- [x] Verify malformed input, WooCommerce absence, and positive-stock terminal products.

## M5 — Homepage

Status: next; not started

- [ ] Build the first approved storefront homepage presentation on the verified domain foundation.

## Later roadmap

After M5, continue through separately approved storefront, private-access, archive presentation, WooCommerce integration, testing, packaging, staging verification, and launch milestones. Keep each milestone independently scoped and verified.
