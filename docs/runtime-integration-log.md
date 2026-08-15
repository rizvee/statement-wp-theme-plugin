# Runtime Integration Log

## Environment

- Site: `https://mystatement.store/`
- Current Core Candidate: `0.13.0-rc.4`
- Historical Candidates: `0.13.0-rc.1`, `0.13.0-rc.2`, `0.13.0-rc.3`
- Temporary Fixture Tool: `statement-integration-fixtures 0.2.2` (Historical: `0.1.0`, `0.1.1`, `0.2.0`, `0.2.1`)
- WordPress: `6.7.x` (WordPress.com Atomic)
- WooCommerce: `11.0.1`
- PHP: `8.3.x`
- Verified at: 2026-08-15

---

## Issue Log

| ID | Test ID | Feature | Expected | Actual | Classification | Severity | Local Fix Commit | RC | Retest | Status |
|----|---------|---------|----------|--------|----------------|----------|------------------|----|--------|--------|
| `M13-ISSUE-01` | `M13-PA-01` | Core Plugin Upload Access | WP Admin upload access available via CLI / automated tool | Upload requires manual WordPress.com Dashboard session | PLATFORM | BLOCKER | N/A | rc.1 | Retested | RESOLVED (Manual Upload) |
| `M13-ISSUE-02` | `M13-PA-01` | RC Version Traceability | Packaged plugin header Version equals candidate version (`0.13.0-rc.1`) | `statement-collector-core.php` header contained `Version: 0.1.0`; Atomic reported version 0.1.0 | CODE / PACKAGING | HIGH | `89431ce` | rc.2 | Retested on Atomic | RESOLVED (RC.2 Active) |
| `M13-ISSUE-03` | `M13-FIX-01` | Fixture Tool Verification API | Verification page renders clean diagnostic summary table | Fatal call to nonexistent method `Purchasability::is_purchasable()` on `VerificationService.php:50` | TEST TOOL / CODE | HIGH | Pending hotfix | v0.1.1 | Pending | RESOLVED (Local v0.1.1) |
| `M13-CONFIG-01` | `M13-WOO-01` | Store Currency | Store currency configured as `AUD` | Atomic store currency configured as `USD` | CONFIGURATION | MEDIUM | `4cdb43c` | v0.1.0 | Retested on Atomic | RESOLVED (Currency set to AUD) |
| `M13-SAFETY-01` | `M13-PA-03` | Fixture Testing Gate | True access-restricted / Coming Soon site privacy for fixture testing | Site is publicly reachable + `noindex, nofollow` | CONFIGURATION | MEDIUM | N/A | rc.2 | Pending | OPEN (Fixture Blocked) |
| `M13-CONFIG-02` | `M13-5A-01` | Front Page Template | `/` renders theme `front-page.php` showcase loop | Page ID 53 post meta specifies `elementor_canvas` template | WORDPRESS CONFIG | MEDIUM | N/A | rc.2 | Pending | OPEN (WP Admin Template Change) |
| `M13-CONFIG-04` | `M13-5B-01` | Private Access Secrets | Operator places constants into `wp-config.php` via SFTP | WordPress.com plan permits plugin uploads but no SFTP/file-manager access | PLATFORM / HOSTING CONSTRAINT | HIGH | `d397387` | rc.3 / v0.2.1 | Retested | RESOLVED (Secret Vault Fallback) |
| `M13-ISSUE-04` | `M13-PA-01` | Core Source Tracking (`Secrets.php`) | Packaged runtime source files tracked in GitHub repository | `Access/Secrets.php` existed locally but was ignored by generic `secrets.*` `.gitignore` rule | BUILD / REPOSITORY INTEGRITY | HIGH | `acc2073` | rc.3 / v0.2.1 | Retested | RESOLVED (Unignored + Git Tracking Verifier) |
| `M13-ISSUE-06` | `M13-5B2-01` | Private Drop Config & Fixture Creation | Private Access fixture creates term config and transitions product state | Fatal `Call to undefined method DropConfig::save_config()` + `Metadata` ID argument mismatch | CODE / CONTRACT | HIGH | `HEAD` | rc.4 / v0.2.2 | Pending Retest | RESOLVED (Core save_config API + DropConfigAdmin + Idempotent Entity Adoption) |

*Note: Classifications: CODE, CONFIGURATION, WORDPRESS CONFIG, PLATFORM, CONTENT, BUILD / REPOSITORY INTEGRITY, UNKNOWN. Severities: BLOCKER, HIGH, MEDIUM, LOW.*

---

## Evidence Log

### `M13-EVIDENCE-06`: Phase 5B2.1 Private Drop Config & Fixture Partial Recovery

- **Observed Failure**: Operator triggered "Create Private Access Test Fixture" on Atomic under Core `0.13.0-rc.3` + Fixtures `0.2.1`. The call failed safely with `Call to undefined method Statement\Collector\Core\Access\DropConfig::save_config()`.
- **Atomic State Impact**: Term `test-private-drop-01` (ID 1376) and Product `TEST-PD01-PAJ` (ID 213) were created, but Drop configuration and product metadata were not finalized, and manifest was not written (PARTIAL / ORPHAN state).
- **Core Fix**:
  - Implemented `DropConfig::save_config( int $term_id, array $config ): bool` with transactional rollback, UTC datetime normalization, duration validation, and reminder parameter handling.
  - Implemented `DropConfigAdmin` for WP Admin taxonomy management on `statement_drop`.
- **Fixture Fix**:
  - Corrected `PrivateFixtureService` to operate on `WC_Product` objects for `Metadata::set_edition_label()` and `Metadata::set_release_state()`, calling `$product->save()`.
  - Implemented 4-state lifecycle model (`NOT_CREATED`, `PARTIAL`, `CREATED`, `RECOVERY_REQUIRED`) with idempotent adoption of existing test Drop and Product entities by stable identity (`test-private-drop-01` / `TEST-PD01-PAJ`).
  - Added "Adopt & Recover Private Access Test Fixture" action in `AdminPage`.
- **Verification**:
  - 19-assertion PHP behavior test (`tests/php/test-drop-config-fixture-recovery.php`) passed clean.
  - Full Node and PHP test suites passed 100%.
  - Packaged Core `0.13.0-rc.4` and Fixtures `0.2.2`.
