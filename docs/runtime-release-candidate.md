# Release Candidate Record — M13 Phase 5B1.1

## Overview

| Attribute | Value |
| --- | --- |
| Candidate Version | `Core 0.13.0-rc.3 / Theme 0.13.0-rc.2 / Fixture 0.2.1` |
| Historical Candidate Version | `0.13.0-rc.2`, `0.13.0-rc.1` |
| Git Commit | `HEAD` (*"feat: add secure Private Access secret vault fallback"*) |
| Git Branch | `main` |
| Theme Version | `0.13.0-rc.2` (Active on Atomic) |
| Plugin Version | `0.13.0-rc.3` (Packaged locally in `dist/`) |
| Plugin Header Version | `0.13.0-rc.3` (Verified matching) |
| Plugin Constant Version | `0.13.0-rc.3` (Verified matching) |
| Fixture Plugin Version | `0.2.1` (Packaged locally in `dist/`) |
| Theme ZIP Checksum (SHA-256) | `ad12e8c699aa8657e929779813dad38e2113d95c6018da2a565481b209ec0054` |
| Plugin ZIP Checksum (SHA-256) | `ef4edaaea0c385a819970c8a56c0ec5a679ec1b23ff01fc66934cf8d5a290028` (VERIFIED MATCH) |
| Fixture ZIP Checksum (SHA-256) | `5cfbe5a3a6cfdcc4ec6ef863b284e3718f9715d6c90581006cbb2a2fc82f459e` (VERIFIED MATCH) |
| Package Verification Status | PASS (38 files, 38 PHP files in Core; 6 PHP files in Fixture) |
| Local Test Suite Status | PASS (116 Node subtests, 26 PHP SecretVault assertions, 78 PHP files linted) |

---

## Secret Provider Architecture

- **Primary Provider**: `wp_config` constants (when available).
- **Hosting Fallback Provider**: Encrypted Secret Vault (`statement_access_secret_vault_v1`, `autoload = false`). Wrapping key derived via HMAC-SHA256 from `wp_salt('auth')`. Zero plaintext stored in DB.
- **Fail Closed**: Partial `wp-config` constants or uninitialized vault result in `Secrets::is_configured() === false`.

---

## Authorization Gate

- **Approved for Local Packaging & Remote Git Synchronization:** **YES**
- **Approved for Operator Manual Upload:** **YES** (Core `0.13.0-rc.3` and Fixture `0.2.1` ready for user upload in WordPress Admin)
- **Approved for Auto-Deployment:** **NO** (Strictly forbidden)
