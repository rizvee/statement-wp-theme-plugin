# Release Candidate Record — M13 Phase 2

## Overview

| Attribute | Value |
| --- | --- |
| Candidate Version | `0.13.0-rc.1` |
| Git Commit | `e573fb5` |
| Git Branch | `main` |
| Theme Version | `0.13.0-rc.1` (NOT UPLOADED) |
| Plugin Version | `0.13.0-rc.1` |
| Theme ZIP Checksum (SHA-256) | `d40f2eca3fd42f3a1fe9bde99f59759f50fd6f2ffa7f909746798aae0200b077` |
| Plugin ZIP Checksum (SHA-256) | `c58278a3bbd677d38b670db279e32d798b6d28de3ae0c96b330fdc7af7b2628c` (VERIFIED MATCH) |
| Package Verification Status | PASS (37 files, 37 PHP files, zero secrets) |
| Local Test Suite Status | PASS (98 Node subtests, 40 PHP assertions, 71 PHP files linted) |

---

## Atomic Validation Status

- Atomic Preflight Completed? **YES** (site private, Assembler theme active, Woo 11.0.1 active)
- Atomic Plugin Uploaded? **BLOCKED** (requires WP Admin / WordPress.com login session)
- Atomic Plugin Activated? **PENDING**
- Atomic Plugin Bootstrap Validated? **PENDING**
- Atomic Theme Validated? **NO** (Theme upload strictly excluded in Phase 2)
- Private Cache Isolation Validated? **PENDING**
- Sandbox Checkout Validated? **PENDING**
- Purchase Provenance Validated? **PENDING**
- Transactional Emails Validated? **PENDING**
- Action Scheduler Cron Jobs Validated? **PENDING**
- Terminal Archive States Validated? **PENDING**
- Known Blockers: `M13-ISSUE-01` (WP Admin login session needed to upload plugin ZIP)

---

## Authorization Gate

- **Approved for Upload:** **YES** (for `dist/statement-collector-core-0.13.0-rc.1.zip` only)
- **Approved for Launch:** **NO**

*STOP GATE: Core plugin upload is approved; theme upload and public launch remain strictly prohibited.*
