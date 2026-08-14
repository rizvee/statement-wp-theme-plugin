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

## Later roadmap

After M12, continue through separately approved WooCommerce integration, testing, packaging, staging verification, and launch milestones. Keep each milestone independently scoped and verified.
