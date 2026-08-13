# Statement Collector Repository Rules

This repository is the source of truth for the Statement Collector's Piece WordPress project.

## Retrieval order

For every task, read:

1. `AGENTS.md`
2. `.ai/context/current-state.md`
3. Only the context, skill, and check files relevant to the task

Update `TASKS.md` and `current-state.md` when milestone state changes. Keep stable decisions in `MEMORY.md`; put transient evidence in task output or a dated session log only when it has lasting diagnostic value.

## Operating rules

- Inspect before modifying and preserve useful work.
- Keep changes within the named milestone. Do not edit WordPress or WooCommerce core.
- Priority: correctness -> security -> WooCommerce reliability -> maintainability -> performance -> accessibility -> visual polish.
- Theme presentation belongs in `statement-collector-theme`; durable domain logic belongs in `statement-collector-core`. See `.ai/context/architecture.md`.
- Protect the invariant: one release → limited availability → sold out → never restocked → permanent archive. See `.ai/context/business-rules.md`.
- Sanitize input, validate domain values, escape output, use nonces for CSRF-sensitive actions, check capabilities, prepare queries, use safe redirects, store no secrets, hash sensitive tokens where applicable, and handle WooCommerce absence gracefully.
- Prefer WordPress/WooCommerce APIs, vanilla JavaScript, lightweight CSS, conditional assets, responsive images, native browser APIs, and minimal dependencies.
- Do not introduce page builders, unnecessary JavaScript frameworks, large frontend bundles, or trivial animation libraries.
- Never edit production files directly, auto-deploy, publish, push, or change the live site from this workflow. Follow `.ai/context/deployment-rules.md`.
- Run the narrowest relevant checks and map completion claims to evidence. Disclose unavailable checks.

## M0 boundary

M0 establishes repository documentation, hygiene, empty runtime directories, and lightweight verification only. Theme, plugin, storefront, Drop, access, archive, and WooCommerce behavior begin in later approved milestones.
