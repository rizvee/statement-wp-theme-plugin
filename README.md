# Statement Collector's Piece

Source-of-truth repository for the custom WordPress theme and durable core plugin behind [mystatement.store](https://mystatement.store/).

Primary message: **Crafted. Limited. Never Restocked.**

## Current state

M0 repository foundation is complete. No theme, plugin, storefront, Drop, access, archive, or WooCommerce feature code exists yet. M1 is the next approved milestone: a minimum theme skeleton plus a minimum core plugin skeleton.

## Repository map

- `wp-content/themes/statement-collector-theme/` — presentation layer (empty until M1)
- `wp-content/plugins/statement-collector-core/` — durable domain layer (empty until M1)
- `.ai/context/` — concise project decisions loaded by need
- `.ai/checks/`, `.ai/skills/`, `.ai/prompts/` — focused development support
- `scripts/` — dependency-light local verification
- `docs/`, `tests/`, `releases/` — milestone documentation, tests, and generated release location

## Verify

```text
node scripts/verify-foundation.mjs
```

See `AGENTS.md` for operating rules and `RUNBOOK.md` for the workflow.
