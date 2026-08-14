# M12 Collector Provenance & Order Experience Architecture

## Domain Boundary

M12 implements **Order Item Purchase Provenance** ("What exact Statement release/product did this WooCommerce order item represent at purchase time?").

M12 does **NOT** implement:
- Certified collector ownership / authenticity credentials
- Serial numbers (e.g. `17/200` or `#17`)
- Production totals / lifetime caps (e.g. `200 pieces`)
- Ownership transfer registry / public collector profiles

## Immutable Provenance Snapshot

Captured at WooCommerce order-line-item creation time (`woocommerce_checkout_create_order_line_item`):

| Meta Key | Description |
| --- | --- |
| `_statement_provenance_version` | Schema version (`1`) |
| `_statement_product_id_at_purchase` | Parent/simple product ID |
| `_statement_variation_id_at_purchase` | Variation ID (`0` if simple) |
| `_statement_drop_id_at_purchase` | Drop term ID (`0` if none) |
| `_statement_drop_name_at_purchase` | Drop name at purchase time |
| `_statement_edition_label_at_purchase` | Human-readable edition label at purchase time |
| `_statement_product_title_at_purchase` | Piece title at purchase time |
| `_statement_release_state_at_purchase` | Release state at purchase time (`LIVE`, `PRIVATE_ACCESS`, etc.) |
| `_statement_purchased_at` | Provenance capture timestamp (`Y-m-d H:i:s`) |

*Note: `_statement_purchased_at` is the capture timestamp during order creation and does NOT imply payment completion or ownership.*

## Immutability & Snapshot Integrity

- **Write-Once**: Provenance metadata is written once during line item creation. Subsequent order recalculations or product metadata changes do NOT overwrite captured provenance.
- **Snapshot Validation**: `Provenance::get_snapshot_status()` classifies snapshots as `complete`, `invalid`, or `missing`. `Provenance::is_valid()` checks for completeness.
- **Source Independence**: If the source product, variation, or Drop term is later edited or deleted, historical order details and emails render using the frozen snapshot data.

## Coexistence & Privacy

- **M10 Coexistence**: M10 private access audit metadata (`_statement_private_access_grant_id`, etc.) remains separate and untouched.
- **Data Minimization**: Provenance metadata contains NO customer PII (email, address, IP), session tokens, secrets, or payment data.
- **Access Boundary**: Provenance is displayed exclusively in authorized WooCommerce contexts (Admin Order Detail, Customer My Account / Order Received via native WooCommerce security, and WooCommerce transactional emails). It is never exposed in public REST/Store API product endpoints or search results.

## Commercial Completion Helper

`Statement\Collector\Core\Order\Completion::is_commercially_completed( $order )` returns `true` for `processing` and `completed` status, and `false` for `pending`, `failed`, `on-hold`, `cancelled`, and `refunded` orders.
