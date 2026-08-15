# Release Candidate Record — M13 / M14 Consolidated Integration

## Overview

| Attribute | Value |
| --- | --- |
| Candidate Version | `Core 0.13.0-rc.9 / Theme 0.13.0-rc.3 / Fixture 0.3.3` |
| Historical Candidate Version | `0.13.0-rc.8`, `0.13.0-rc.7`, `0.13.0-rc.6`, `0.13.0-rc.5`, `0.13.0-rc.4`, `0.13.0-rc.3`, `0.13.0-rc.2`, `0.13.0-rc.1` |
| Git Commit | `HEAD` |
| Git Branch | `main` |
| Theme Version | `0.13.0-rc.3` (Packaged in `dist/`, Ready for manual upload) |
| Plugin Version | `0.13.0-rc.9` (Active on Atomic, Packaged in `dist/`) |
| Plugin Header Version | `0.13.0-rc.9` (Verified matching) |
| Plugin Constant Version | `0.13.0-rc.9` (Verified matching) |
| Fixture Plugin Version | `0.3.3` (Packaged in `dist/`, Ready for manual upload) |
| Theme ZIP Checksum (SHA-256) | `bb10562d7aac431219aed4cb1317f2d85b4f8ef12b867906779a569a1085a68d` |
| Plugin ZIP Checksum (SHA-256) | `13beee9332297fb90f3c8c6b1cd20f90ed63c1d9e839152e6af1c0c7a78868a6` |
| Fixture ZIP Checksum (SHA-256) | `351683dcebc50d99e3c51a24a5d671b0aef308680817e94d416b9d020efda72f` |

| Package Verification Status | PASS (47 files in Theme; 40 files in Core; 8 files in Fixture) |
| Local Test Suite Status | PASS (126 Node subtests across 17 test files, 82 PHP files linted, QA contract & bootstrap tests, foundation & tracking verifiers clean) |

---

## Secret Provider Architecture

- **Primary Provider**: `wp_config` constants (when available).
- **Hosting Fallback Provider**: Encrypted Secret Vault (`statement_access_secret_vault_v1`, `autoload = false`). Wrapping key derived via HMAC-SHA256 from `wp_salt('auth')`. Zero plaintext stored in DB.
- **Fail Closed**: Partial `wp-config` constants or uninitialized vault result in `Secrets::is_configured() === false`.

---

## Authorization Gate

- **Approved for Local Packaging & Remote Git Synchronization:** **YES**
- **Approved for Operator Manual Upload:** **YES** (Theme `0.13.0-rc.3` and Fixtures `0.3.3` ready for user upload in WordPress Admin)
- **Approved for Auto-Deployment:** **NO** (Strictly forbidden)
