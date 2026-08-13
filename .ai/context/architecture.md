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

Durable business/domain layer only. Later approved milestones may add Drops, release states, product-to-Drop relationships, piece/edition metadata, permanent sold-out/archive enforcement, private email access, secure tokens/sessions, access tracking, reminder eligibility, admin fields, WooCommerce integration, and collector functionality.

The plugin must gracefully handle WooCommerce absence and must not depend on a specific theme for domain correctness.

## Extension boundaries

- Use WordPress/WooCommerce public hooks and APIs; never edit core.
- Future sensitive actions require validation, sanitization, escaping, nonces, capability checks, prepared queries, safe redirects, and appropriately hashed tokens.
- Keep dependencies minimal and assets conditional. Avoid page builders, unnecessary JS frameworks, large bundles, and trivial animation libraries.
- The lifecycle may use `UPCOMING`, `PRIVATE_ACCESS`, `LIVE`, `SOLD_OUT`, and `ARCHIVED`, but M0 does not implement it.
