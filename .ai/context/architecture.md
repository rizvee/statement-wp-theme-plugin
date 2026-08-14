# Locked Architecture

## `statement-collector-theme`

Presentation layer only:

- custom standalone WordPress theme; no parent theme and no Elementor dependency
- hybrid/classic PHP architecture, with `theme.json` where useful
- WordPress/WooCommerce hooks and APIs; selective native blocks
- vanilla JavaScript and lightweight CSS/design tokens
- WooCommerce presentation, responsive behavior, and accessibility

The theme must not own durable release, access, archive, token, or inventory-domain decisions.

## `statement-collector-core`

Durable business/domain layer only. It owns the `statement_drop` product taxonomy, controlled product metadata, forward-only release states, and terminal WooCommerce purchasability lock. Its minimal read-only `PublicApi` exposes canonical release eligibility and Drop facts to presentation code without exposing mutation or metadata keys. Later approved milestones may add private email access, secure tokens/sessions, access tracking, reminder eligibility, archive presentation integrations, and collector functionality.

The plugin must gracefully handle WooCommerce absence and must not depend on a specific theme for domain correctness.

## Catalog boundary

- Core applies canonical public release eligibility to the main WooCommerce Shop and `statement_drop` queries before results; it preserves WooCommerce meta constraints and does not post-filter products.
- WooCommerce remains responsible for native catalog query mechanics, loop cardinality, and pagination.
- The theme owns catalog markup, shared product-card presentation, and contextual removal of generic result-count, ordering, and sidebar UI; it does not interpret release metadata.

## Product boundary

- Core owns direct product visibility and Add-to-Cart eligibility from canonical parent release state; normal public access is `LIVE` only and fails closed when unresolved.
- WooCommerce remains responsible for native product gallery, pricing, simple/variable selection, and purchase-form mechanics.
- The theme composes the product page from the core read-only API and native WooCommerce template functions. It does not interpret release metadata or implement custom variation/cart behavior.

## Cart boundary

- Core revalidates restored and current cart lines against canonical parent release state; only `LIVE` may remain in the normal public cart or proceed toward checkout.
- WooCommerce owns cart sessions, quantities, removal URLs, totals, notices, and checkout routing.
- The theme owns server-rendered Bag count presentation and the classic Cart template/CSS. It does not interpret lifecycle metadata, mutate stock, or add cart networking.

## Checkout boundary

- Core reuses Cart Integrity on WooCommerce's final cart-check boundary; stale non-`LIVE` lines produce a blocking lifecycle-neutral checkout error before native order creation.
- WooCommerce owns customer fields, shipping methods, payment gateways, validation, totals, terms/privacy, nonces, order creation, and payment processing.
- The theme owns one classic Checkout form composition and conditional CSS. It removes only coupon-entry presentation, adds no checkout JavaScript, and does not interpret release metadata.

## Extension boundaries

- Use WordPress/WooCommerce public hooks and APIs; never edit core.
- Presentation integrations consume the core read-only API; themes must not duplicate release metadata interpretation.
- Future sensitive actions require validation, sanitization, escaping, nonces, capability checks, prepared queries, safe redirects, and appropriately hashed tokens.
- Keep dependencies minimal and assets conditional. Avoid page builders, unnecessary JS frameworks, large bundles, and trivial animation libraries.
- The canonical lifecycle uses `UPCOMING`, `PRIVATE_ACCESS`, `LIVE`, `SOLD_OUT`, and `ARCHIVED`; core domain code owns its interpretation.
