# Release Candidate Record — M13 / M14 / M15 Consolidated Integration

## Overview

| Attribute | Value |
| --- | --- |
| Candidate Version | `Core 0.13.0-rc.9 / Theme 0.13.0-rc.4 / Fixture 0.3.3` |
| Historical Candidate Version | `0.13.0-rc.8`, `0.13.0-rc.7`, `0.13.0-rc.6`, `0.13.0-rc.5`, `0.13.0-rc.4`, `0.13.0-rc.3`, `0.13.0-rc.2`, `0.13.0-rc.1` |
| Git Commit | `HEAD` |
| Git Branch | `main` |
| Theme Version | `0.13.0-rc.4` (Packaged in `dist/`, Ready for operator upload) |
| Plugin Version | `0.13.0-rc.9` (Active on Atomic, Packaged in `dist/`) |
| Plugin Header Version | `0.13.0-rc.9` (Verified matching) |
| Plugin Constant Version | `0.13.0-rc.9` (Verified matching) |
| Fixture Plugin Version | `0.3.3` (Packaged in `dist/`, Ready for operator upload) |
| Theme ZIP Checksum (SHA-256) | `d3053c00d3674666cfd870b8557cfde90e70d47be2a6c0af25a0183c31d3e1bf` |
| Plugin ZIP Checksum (SHA-256) | `6e1ab1ea2571c757852c299631834f4aa5f59040e7afe021e18cb0d803e4c3d4` |
| Fixture ZIP Checksum (SHA-256) | `97fbb481613fc619434e87b5d81fb3815ab7b690bd2d1e80e94dbf547ec70850` |

| Package Verification Status | PASS (47 files in Theme; 40 files in Core; 8 files in Fixture) |
| Local Test Suite Status | PASS (All Node subtests across 17 test suites, 82 PHP files linted, QA contract & bootstrap tests, foundation & tracking verifiers clean) |

---

## Secret Provider Architecture

- **Primary Provider**: `wp_config` constants (when available).
- **Hosting Fallback Provider**: Encrypted Secret Vault (`statement_access_secret_vault_v1`, `autoload = false`). Wrapping key derived via HMAC-SHA256 from `wp_salt('auth')`. Zero plaintext stored in DB.
- **Fail Closed**: Partial `wp-config` constants or uninitialized vault result in `Secrets::is_configured() === false`.

---

## Authorization Gate

- **Approved for Local Packaging & Remote Git Synchronization:** **YES**
- **Approved for Operator Manual Upload:** **YES** (Theme `0.13.0-rc.4` and Fixtures `0.3.3` ready for user upload in WordPress Admin)
- **Approved for Auto-Deployment:** **NO** (Strictly forbidden)
