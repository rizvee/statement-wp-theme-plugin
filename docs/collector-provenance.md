# Statement Collector Provenance & Order Experience (M12)

## Overview & Purpose

M12 introduces durable, immutable purchase provenance for WooCommerce order items representing Statement pieces. It answers:

> "What exact Statement release/product did this WooCommerce order item represent at purchase time?"

It explicitly does **NOT** determine certified collector ownership or issue authenticity certificates. Those belong to future milestones.

---

## Provenance Schema & Fields

Captured write-once at order line item creation (`woocommerce_checkout_create_order_line_item`):

- `_statement_provenance_version`: Schema version (`1`)
- `_statement_product_id_at_purchase`: Parent product ID
- `_statement_variation_id_at_purchase`: Variation ID (`0` if simple product)
- `_statement_drop_id_at_purchase`: Drop term ID (`0` if unassigned)
- `_statement_drop_name_at_purchase`: Drop name at purchase time
- `_statement_edition_label_at_purchase`: Human-readable edition label at purchase time (e.g. `First Edition`)
- `_statement_product_title_at_purchase`: Piece title at purchase time
- `_statement_release_state_at_purchase`: Statement release state at purchase time (`LIVE`, `PRIVATE_ACCESS`, `SOLD_OUT`, `ARCHIVED`)
- `_statement_purchased_at`: GMT timestamp (`Y-m-d H:i:s`)

---

## Historical Immutability & Idempotency

1. **Write-Once**: Once `_statement_provenance_version` exists on a WooCommerce line item, `Statement\Collector\Core\Order\Provenance::capture_line_item_provenance` will not mutate or overwrite the snapshot.
2. **Product / Term Editing**: Subsequent edits to product titles, Drop names, edition labels, or release states in the WordPress admin do not rewrite historical order provenance.
3. **Missing / Deleted Source**: If a product or Drop taxonomy term is later deleted, customer and admin order views render using the frozen snapshot without crashing.

---

## M10 Coexistence & Data Minimization

1. **M10 Coexistence**: Private access authorization audit fields (`_statement_private_access_grant_id`, etc.) remain completely separate from M12 provenance fields.
2. **Data Minimization**: M12 provenance meta contains no customer PII (email, address, IP), tokens, payment secrets, or HMAC hashes.

---

## Future Commercial Completion Helper

`Statement\Collector\Core\Order\Completion::is_commercially_completed( $order )`:
- Returns `true` for `processing` and `completed` status.
- Returns `false` for `pending`, `on-hold`, `failed`, `cancelled`, and `refunded` status.

---

## Presentation & Security

- **Admin Order View**: Read-only display rendered via `woocommerce_after_order_itemmeta`. Contains no input forms or edit buttons.
- **Customer Order View**: Renders status-aware confirmation banners (`Order Confirmed / Your piece has been secured` for processing/completed) and frozen provenance details. Preserves native WooCommerce order key and session ownership security.
- **Transactional Emails**: Enriches WooCommerce order emails with frozen provenance data. Operates independently of M10 marketing consent flags.
