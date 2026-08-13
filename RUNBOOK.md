# Local Development Runbook

## Start a task

1. Read `AGENTS.md` and `.ai/context/current-state.md`.
2. Read only task-relevant context, skill, and check files.
3. Inspect `git status --short --branch` and the affected files before editing.
4. Work inside the approved milestone and preserve unrelated changes.

## Verify M0

From PowerShell or any shell with Node available:

```text
node scripts/verify-foundation.mjs
```

The script checks required structure, locked documentation signals, M0 runtime scope, obvious secret patterns, generated ZIP absence, and PHP syntax when both PHP and PHP files are present. Treat reported limitations as unavailable checks, not passes.

PHP lint uses `scripts/php-lint.mjs`. It resolves `PHP_BIN` first, then ignored `.local-tools/php/php.exe` on Windows, then PHP on `PATH`. Project-local binaries are never committed.

Also inspect:

```text
git status --short --branch
git diff --check
node scripts/php-lint.mjs
```

## Packaging and release

M0 does not produce packages. Future approved packaging must create:

- `statement-collector-theme-vX.Y.Z.zip`
- `statement-collector-core-vX.Y.Z.zip`

Each ZIP must contain only its installable runtime directory and exclude Git data, repository AI/dev files, unnecessary tests, local caches, secrets, `node_modules`, and unrelated build artifacts. Generated ZIPs belong under `releases/` and are ignored by Git.

Manual upload to the WordPress.com Atomic integration/production site is a separate, confirmation-gated operation. Never auto-deploy or modify live files from this repository.

## Recovery

Do not rewrite Git history or discard uncommitted user work. If a future site release fails, retain the current Assembler/Elementor homepage as the rollback fallback until the replacement has been verified.
