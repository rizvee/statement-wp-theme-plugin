# M13 Runtime Integration Preparation & Verification Architecture

## Overview & Scope Boundary

M13 Phase 1 establishes deterministic packaging, package verification, security scanning, RC versioning, and preflight runbooks for controlled integration testing on the WordPress.com Atomic environment (`mystatement.store`).

**Phase 1 is strictly LOCAL ONLY.**
- NO WordPress.com upload, installation, activation, or API write calls
- NO public deployment or live database mutations
- NO Git push, tagging, or release publication

## Development & Runtime Model

```
Local Git Repository (Source of Truth)
   ↓
verify-foundation.mjs & php-lint.mjs
   ↓
package-all.mjs (Deterministic ZIP Generation)
   ↓
verify-package.mjs (Extract, Inspect, PHP-Lint Artifacts, Secret Scan)
   ↓
dist/manifest.json (Candidate Record: deployment_authorized = false)
   ↓
Explicit Human Operator Authorization (STOP GATE)
```

## Packaging Infrastructure

| Script | Purpose |
| --- | --- |
| `scripts/package-theme.mjs` | Packages `statement-collector-theme` into single-root ZIP in `dist/` |
| `scripts/package-plugin.mjs` | Packages `statement-collector-core` into single-root ZIP in `dist/` |
| `scripts/verify-package.mjs` | Extracts candidate ZIPs, verifies single root, checks exclusions, lints packaged PHP, scans for secrets |
| `scripts/package-all.mjs` | Master script executing packaging, verification, and manifest generation |

## Package Exclusions

Runtime packages explicitly exclude:
- `.git/`, `.github/`, `.ai/`, `tests/`, `scripts/`, `docs/`, `.local-tools/`, `node_modules/`, `dist/`, `coverage/`, `tmp/`, `logs/`
- Development artifacts: `*.log`, `php.ini`, `.env`, `.env.*`, `package-lock.json`, IDE settings (`.vscode`, `.idea`), OS metadata (`.DS_Store`, `Thumbs.db`)

## Documented Runbooks & Checklists

1. `docs/atomic-integration-preflight.md`: Operator preflight checklist for Atomic environment state.
2. `docs/atomic-rollback-runbook.md`: Emergency rollback procedures for theme/plugin/checkout/cache issues.
3. `docs/runtime-integration-checklist.md`: 19 structured test cases with IDs `M13-PA-01` through `M13-RS-01`.
4. `docs/runtime-integration-log.md`: Integration defect tracking log template.
5. `docs/runtime-release-candidate.md`: Release candidate record template.
