# Release Candidate Record — M13 Phase 2A

## Overview

| Attribute | Value |
| --- | --- |
| Candidate Version | `0.13.0-rc.2` |
| Historical Candidate Version | `0.13.0-rc.1` (Historical evidence of header version defect `M13-ISSUE-02`) |
| Git Commit | Local pending commit |
| Git Branch | `main` |
| Theme Version | `0.13.0-rc.2` (NOT UPLOADED) |
| Plugin Version | `0.13.0-rc.2` |
| Plugin Header Version | `0.13.0-rc.2` (Verified matching) |
| Plugin Constant Version | `0.13.0-rc.2` (Verified matching) |
| Theme Header Version | `0.13.0-rc.2` (Verified matching) |
| Theme Constant Version | `0.13.0-rc.2` (Verified matching) |
| Theme ZIP Checksum (SHA-256) | Computed upon `packageAll()` execution |
| Plugin ZIP Checksum (SHA-256) | Computed upon `packageAll()` execution |
| Package Verification Status | PASS (37 files, 37 PHP files, header/constant matching verified) |
| Local Test Suite Status | PASS |

---

## Forensic Analysis — Artifact 0.13.0-rc.1 Defect

- **Artifact Name:** `statement-collector-core-0.13.0-rc.1.zip`
- **ZIP Filename & Manifest:** Claimed `0.13.0-rc.1`
- **Internal `statement-collector-core.php` Header:** `Version: 0.1.0`
- **Internal `STATEMENT_COLLECTOR_CORE_VERSION` Constant:** `'0.1.0'`
- **Why Verifier Passed `rc.1`:** `verify-package.mjs` verified ZIP filename, root directory, file existence, secret absence, and PHP syntax, but failed to parse and assert the literal WordPress `Version:` header line or PHP constant against the candidate version.
- **Atomic Runtime Observation:** WordPress.com correctly parsed `Version: 0.1.0` from `statement-collector-core.php` upon installation and display on Plugins screen.
- **Resolution:** Hotfixed in candidate `0.13.0-rc.2` by aligning source headers/constants and hardening `package-plugin.mjs`, `package-theme.mjs`, `package-all.mjs`, and `verify-package.mjs` with explicit header/constant assertion checks and negative regression test.

---

## Atomic Validation Status

- Atomic Preflight Completed? **YES** (site reachable, `blog_public = 0`, `noindex, nofollow`, Assembler theme active, Woo 11.0.1 active)
- Atomic Plugin Uploaded? **YES (rc.1 installed; rc.2 pending manual replacement approval)**
- Atomic Plugin Activated? **YES** (Auto-activated by WordPress.com platform)
- Atomic Plugin Bootstrap Validated? **YES** (Statement Access, Drops taxonomy, Product editor controls render cleanly)
- Atomic Theme Validated? **NO** (Theme upload strictly excluded in Phase 2/2A)
- Private Cache Isolation Validated? **PENDING**
- Sandbox Checkout Validated? **PENDING**
- Purchase Provenance Validated? **PENDING**
- Transactional Emails Validated? **PENDING**
- Action Scheduler Cron Jobs Validated? **PASS** (Platform Action Scheduler operational)
- Terminal Archive States Validated? **PENDING**
- Known Blockers: `M13-CONFIG-01` (Store currency USD vs target AUD)

---

## Authorization Gate

- **Approved for Upload / Replacement:** **NO** (Requires explicit user authorization before uploading `rc.2` artifact to replace `0.1.0`)
- **Approved for Launch:** **NO**

*STOP GATE: `0.13.0-rc.2` packaging and verification is complete locally; replacement of the installed plugin on WordPress.com Atomic requires EXPLICIT HUMAN OPERATOR APPROVAL.*
