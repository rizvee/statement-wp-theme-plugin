# M13 Runtime Integration Preparation & Verification Architecture

## Overview & Scope Boundary

M13 Phase 1, 2, and 3 establish deterministic packaging, package verification, security scanning, RC versioning, preflight runbooks, and controlled runtime testing on the WordPress.com Atomic environment (`mystatement.store`).

---

## Authenticated Browser Interaction Strategy

> [!IMPORTANT]
> **Durable Browser Authentication Rule:** Authenticated WordPress.com / `wp-admin` workflows MUST NOT depend on Antigravity Chrome DevTools MCP because it runs in a separate browser profile and authentication is costly/unreliable for this user.
>
> **Preferred Hierarchy:**
> 1. WordPress.com connector / authenticated API
> 2. Purpose-built temporary admin-only integration tooling (e.g. `statement-integration-fixtures`)
> 3. User's already-authenticated default Chrome browser for minimal manual actions
> 4. Chrome DevTools MCP strictly for anonymous/frontend/network inspection

---

## Development & Runtime Model

```
Local Git Repository (Source of Truth)
   ↓
verify-foundation.mjs & php-lint.mjs
   ↓
package-all.mjs & package-fixtures.mjs
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
| `scripts/package-fixtures.mjs` | Packages temporary `statement-integration-fixtures` tool into single-root ZIP in `dist/` |
| `scripts/verify-package.mjs` | Extracts candidate ZIPs, verifies single root, checks exclusions, parses & asserts headers/constants, lints packaged PHP, scans for secrets |
| `scripts/package-all.mjs` | Master script executing packaging, verification, and manifest generation |

---

## Documented Runbooks & Checklists

1. `docs/atomic-integration-preflight.md`: Operator preflight checklist for Atomic environment state & auto-activation notice.
2. `docs/atomic-rollback-runbook.md`: Emergency rollback procedures for theme/plugin/checkout/cache issues.
3. `docs/runtime-integration-checklist.md`: Structured test cases (`M13-PA-01` through `M13-RS-01`).
4. `docs/runtime-integration-log.md`: Integration defect tracking log (`M13-ISSUE-01` through `M13-ISSUE-11`).
5. `docs/runtime-release-candidate.md`: Release candidate record tracking Core `0.13.0-rc.9`, Theme `0.13.0-rc.2`, and Fixtures `0.3.2`.
