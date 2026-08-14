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

## Extension boundaries

- Use WordPress/WooCommerce public hooks and APIs; never edit core.
- Presentation integrations consume the core read-only API; themes must not duplicate release metadata interpretation.
- Future sensitive actions require validation, sanitization, escaping, nonces, capability checks, prepared queries, safe redirects, and appropriately hashed tokens.
- Keep dependencies minimal and assets conditional. Avoid page builders, unnecessary JS frameworks, large bundles, and trivial animation libraries.
- The canonical lifecycle uses `UPCOMING`, `PRIVATE_ACCESS`, `LIVE`, `SOLD_OUT`, and `ARCHIVED`; core domain code owns its interpretation.
