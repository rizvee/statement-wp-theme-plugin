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

`SOLD_OUT` and `ARCHIVED` are permanent commerce-lock states. The core plugin's release state overrides normal WooCommerce purchasability, so positive stock or later stock adjustments cannot reopen purchasing; normal WooCommerce stock manipulation cannot reverse these states.

Canonical lifecycle: `UPCOMING` → `PRIVATE_ACCESS` → `LIVE` → `SOLD_OUT` → `ARCHIVED`. Normal saves may remain in the same state or move forward; backward transitions are rejected. Private-access policy is not implemented yet.

## Product and Drop integrity

- Each Statement product belongs to one historical Drop; it may remain unassigned while in preparation, and the controlled product UI stores zero or one `statement_drop` term.
- While `UPCOMING`, an assigned Drop may be changed or cleared. From `PRIVATE_ACCESS` onward, an established valid Drop is immutable through normal product saves: replacement and removal are rejected. A released product with no valid historical Drop may receive one first valid assignment, which then becomes immutable.
- A Drop may contain many products. Drop terms are admin-created data, never hardcoded releases.
- The product owns its release state and optional concise edition label through WooCommerce metadata APIs.
- Edition labels and future piece numbers must not expose or imply a public production total.

## Forbidden product logic and messaging

- restock workflows or “restocking soon” states
- production-cap or maximum-piece logic
- public edition-size totals, including “200 pieces” messaging
- temporary sold-out states
- final-run or demand-based replenishment

Piece numbers may exist without exposing total production.
