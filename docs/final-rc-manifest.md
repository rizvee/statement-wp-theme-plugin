# Release Candidate Manifest — M13 / M14 / M15 Consolidated Release

## 1. Release Manifest Overview

| Attribute | Value |
| --- | --- |
| **Release Scope** | M13 Finalization Prep + M14 Storefront Hardening + M15 Launch Readiness |
| **Git Remote** | `https://github.com/rizvee/statement-wp-theme-plugin.git` |
| **Git Branch** | `main` |
| **Git Commit** | `HEAD` |
| **Core Plugin Candidate** | `0.13.0-rc.9` |
| **Theme Candidate** | `0.13.0-rc.4` |
| **Fixture Tool Candidate** | `0.3.3` |
| **Deployment Authorization** | `deployment_authorized = false` (Operator manual upload required) |

---

## 2. Authoritative Candidate Artifacts

| Component | Package Artifact Filename | Version | Size (Bytes) | SHA-256 Checksum | Packaged Files | Verification Status |
| --- | --- | --- | --- | --- | --- | --- |
| **Theme** | `statement-collector-theme-0.13.0-rc.4.zip` | `0.13.0-rc.4` | 44,858 | `d3053c00d3674666cfd870b8557cfde90e70d47be2a6c0af25a0183c31d3e1bf` | 47 (34 PHP) | **PASS** |
| **Core Plugin** | `statement-collector-core-0.13.0-rc.9.zip` | `0.13.0-rc.9` | 66,782 | `6e1ab1ea2571c757852c299631834f4aa5f59040e7afe021e18cb0d803e4c3d4` | 40 (40 PHP) | **PASS** |
| **Integration Fixtures** | `statement-integration-fixtures-0.3.3.zip` | `0.3.3` | 27,311 | `97fbb481613fc619434e87b5d81fb3815ab7b690bd2d1e80e94dbf547ec70850` | 8 (8 PHP) | **PASS** |

---

## 3. Atomic Hosting Deployment State

- **Environment**: `https://mystatement.store/` (WordPress.com Atomic)
- **Active Core Plugin**: `0.13.0-rc.9` ACTIVE
- **Active Theme**: `0.13.0-rc.2` ACTIVE (Ready to be upgraded to `0.13.0-rc.4`)
- **Active Fixture Tool**: `0.3.2` ACTIVE (Ready to be upgraded to `0.3.3`)
- **Secret Vault**: Initialized (`xchacha20-poly1305`)
- **Private Fixture**: `CREATED` (`test-private-drop-01` / `TEST-PD01-PAJ`)

---

## 4. Remaining Post-Deployment Runtime Gates (One Operator Batch)

1. Replace Theme on Atomic with `dist/statement-collector-theme-0.13.0-rc.4.zip`.
2. Replace Fixtures on Atomic with `dist/statement-integration-fixtures-0.3.3.zip`.
3. Open Statement Fixtures admin screen (`/wp-admin/admin.php?page=statement-integration-fixtures`).
4. Execute `RUN EXPIRY TEST` -> Target: `RUNTIME_PASS`.
5. Execute `RUN ACCESS EMAIL TEST` -> Target: `RUNTIME_PASS`.
6. Execute `REVALIDATE TERMINAL LIFECYCLE` -> Target: `RUNTIME_PASS`.
7. Execute controlled QA order via `scripts/test-private-access-order.mjs` or checkout -> Target: `RUNTIME_PASS`.
8. Execute `VERIFY QA ORDER` -> Target: `RUNTIME_PASS`.
9. Execute `TEST PROVENANCE IMMUTABILITY` -> Target: `RUNTIME_PASS`.
10. Smoke test frontend routes.
