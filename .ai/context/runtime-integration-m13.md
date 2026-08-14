# M13 Runtime Integration Preparation & Verification Architecture

## Overview & Scope Boundary

M13 Phase 1 and 2 establish deterministic packaging, package verification, security scanning, RC versioning, preflight runbooks, and controlled runtime testing on the WordPress.com Atomic environment (`mystatement.store`).

**Phase 1 & 2 Boundaries:**
- Candidate packaging and verification are deterministic and local.
- Core plugin manual upload is authorized after preflight verification.
- Platform auto-activation finding: WordPress.com Atomic may automatically activate custom plugins upon upload completion.
- Theme upload, public launch, live database mutation, Git push, tagging, and release publication remain strictly prohibited without explicit user approval.

---

## Development & Runtime Model

```
Local Git Repository (Source of Truth)
   ↓
verify-foundation.mjs & php-lint.mjs
   ↓
package-all.mjs (Deterministic ZIP Generation & Source Version Matching)
   ↓
verify-package.mjs (Extract, Inspect, Header/Constant Version Match, PHP-Lint Artifacts, Secret Scan)
   ↓
dist/manifest.json (Candidate Record: deployment_authorized = false)
   ↓
Explicit Human Operator Authorization (STOP GATE)
```

---

## RC Version Authority Invariant

The source code main file headers and runtime constants are the single source of truth for runtime versioning:

1. **Plugin Header & Constant:** `statement-collector-core.php` header `Version: X.Y.Z-rc.N` and `STATEMENT_COLLECTOR_CORE_VERSION` constant MUST equal the candidate version being packaged.
2. **Theme Header & Constant:** `style.css` header `Version: X.Y.Z-rc.N` and `STATEMENT_COLLECTOR_THEME_VERSION` constant MUST equal the candidate version being packaged.
3. **Verifier Assertion:** `verify-package.mjs` extracts the package and parses the literal WordPress `Version:` header and runtime constant. If either mismatches the candidate version, package verification FAILS.

---

## Packaging Infrastructure

| Script | Purpose |
| --- | --- |
| `scripts/package-theme.mjs` | Packages `statement-collector-theme` into single-root ZIP in `dist/` with version assertion |
| `scripts/package-plugin.mjs` | Packages `statement-collector-core` into single-root ZIP in `dist/` with version assertion |
| `scripts/verify-package.mjs` | Extracts candidate ZIPs, verifies single root, checks exclusions, parses & asserts headers/constants, lints packaged PHP, scans for secrets |
| `scripts/package-all.mjs` | Master script executing packaging, verification, and manifest generation |

---

## Documented Runbooks & Checklists

1. `docs/atomic-integration-preflight.md`: Operator preflight checklist for Atomic environment state & auto-activation notice.
2. `docs/atomic-rollback-runbook.md`: Emergency rollback procedures for theme/plugin/checkout/cache issues.
3. `docs/runtime-integration-checklist.md`: Structured test cases (`M13-PA-01` through `M13-RS-01`).
4. `docs/runtime-integration-log.md`: Integration defect tracking log (`M13-ISSUE-01`, `M13-ISSUE-02`, `M13-CONFIG-01`).
5. `docs/runtime-release-candidate.md`: Release candidate record tracking candidates `0.13.0-rc.1` (historical) and `0.13.0-rc.2` (current candidate).
