# Immutable Business Rules

## Release invariant

```text
ONE RELEASE
→ LIMITED AVAILABILITY
→ SOLD OUT
→ NEVER RESTOCKED
→ PERMANENT ARCHIVE
```

Primary brand message: **Crafted. Limited. Never Restocked.**

`SOLD_OUT` and `ARCHIVED` are permanent domain states. A future implementation must prevent normal WooCommerce stock manipulation from reversing them. M0 documents this constraint; it does not implement a state machine.

Potential lifecycle: `UPCOMING` → `PRIVATE_ACCESS` → `LIVE` → `SOLD_OUT` → `ARCHIVED`.

## Forbidden product logic and messaging

- restock workflows or “restocking soon” states
- production-cap or maximum-piece logic
- public edition-size totals, including “200 pieces” messaging
- temporary sold-out states
- final-run or demand-based replenishment

Piece numbers may exist without exposing total production.
