# M11 Archive & Terminal Presentation Architecture

## Locked Terminal Lifecycle Rules

```text
UPCOMING → PRIVATE_ACCESS → LIVE → SOLD_OUT → ARCHIVED
```

- **Forward-Only Transitions**: Release state transitions are strictly forward-only. Backward mutations (`SOLD_OUT` → `LIVE`, `ARCHIVED` → `SOLD_OUT`, `ARCHIVED` → `LIVE`, or terminal → `PRIVATE_ACCESS`/`UPCOMING`) are permanently rejected by `ReleaseState::can_transition()` and `Metadata::set_release_state()`.
- **Permanent Commerce Lock**: `SOLD_OUT` and `ARCHIVED` products are permanently non-purchasable. WooCommerce stock mutations (positive `stock_quantity` or `instock` status) cannot reopen purchasability.
- **Direct Permalink Public Viewability**: Direct product URLs for `SOLD_OUT` and `ARCHIVED` products remain permanently accessible, returning HTTP 200 and standard public indexable HTML (`index, follow`). They do not return 404s.

## Catalog & Drop Page Behavior

- **Active Catalog Query**: Main Shop loop and active Drop loops query products with state `LIVE` or `SOLD_OUT`, ordered with `LIVE` first (`meta_value` ASC), then `SOLD_OUT`, and `date` DESC. `ARCHIVED` items are excluded from active commerce catalog loops.
- **Drop Historical Resolution**: If a Drop term is a **Past Drop** (contains ONLY `ARCHIVED` products), visiting `/drop/<slug>/` renders its `ARCHIVED` products.
- **Past Drop Classification**: A Drop is classified as a Past Drop ONLY if it has `0` `LIVE`, `0` `SOLD_OUT`, `0` `PRIVATE_ACCESS`, and `0` `UPCOMING` pieces, and at least `1` `ARCHIVED` piece.

## Presentation & Scarcity Model

- **Product Page**: Add to Bag form and quantity selectors are omitted for `SOLD_OUT` and `ARCHIVED` products. Replaced by a restrained status badge (**SOLD OUT** or **ARCHIVED**).
- **Brand Invariant**: **Crafted. Limited. Never Restocked.** Strictly zero restock, waitlist, stock notification, back-in-stock, or total production cap (e.g. "200 pieces") logic or UI.
- **Structured Data**: `Visibility::filter_structured_data_offer()` overrides schema.org offer availability for terminal products to `https://schema.org/OutOfStock`.
