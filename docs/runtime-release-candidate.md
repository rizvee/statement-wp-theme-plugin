# Release Candidate Record — M13 Phase 2B

## Overview

| Attribute | Value |
| --- | --- |
| Candidate Version | `0.13.0-rc.2` |
| Historical Candidate Version | `0.13.0-rc.1` (Historical evidence of header version defect `M13-ISSUE-02`, resolved by `rc.2`) |
| Git Commit | `89431ce` (*"fix: enforce runtime RC version integrity"*) |
| Git Branch | `main` |
| Theme Version | `0.13.0-rc.2` (NOT UPLOADED) |
| Plugin Version | `0.13.0-rc.2` |
| Plugin Header Version | `0.13.0-rc.2` (Verified matching) |
| Plugin Constant Version | `0.13.0-rc.2` (Verified matching) |
| Theme Header Version | `0.13.0-rc.2` (Verified matching) |
| Theme Constant Version | `0.13.0-rc.2` (Verified matching) |
| Theme ZIP Checksum (SHA-256) | `989e1dd3e9522ee8b955335b9219ae39d06df916f737608bb7f7c72b1e9ac88f` |
| Plugin ZIP Checksum (SHA-256) | `3fc333b64e1951ca3db2fb6e4edbe5fecb686317b112ad0bd4c3d8341e2abe2d` (VERIFIED MATCH) |
| Package Verification Status | PASS (37 files, 37 PHP files, header/constant matching verified) |
| Local Test Suite Status | PASS (99 Node subtests, 40 PHP assertions, 71 PHP files linted) |

---

## Atomic Validation Status

- Atomic Preflight Completed? **YES** (site reachable, `blog_public = 0`, `noindex, nofollow`, Assembler theme active, Woo 11.0.1 active)
- Atomic Plugin Uploaded? **YES** (`statement-collector-core-0.13.0-rc.2.zip` installed)
- Atomic Plugin Activated? **YES** (Active at version `0.13.0-rc.2`)
- Atomic Plugin Bootstrap Validated? **YES** (Statement Access, Drops taxonomy, Product editor controls render cleanly)
- Atomic Theme Validated? **NO** (Theme upload strictly excluded in Phase 2/2A/2B)
- Private Cache Isolation Validated? **PENDING**
- Sandbox Checkout Validated? **PENDING**
- Purchase Provenance Validated? **PENDING**
- Transactional Emails Validated? **PENDING**
- Action Scheduler Cron Jobs Validated? **PASS** (Platform Action Scheduler operational)
- Terminal Archive States Validated? **PENDING**
- Known Blockers / Open Issues: `M13-CONFIG-01` (Store currency USD vs target AUD), `M13-SAFETY-01` (Site publicly reachable; fixture creation blocked until access-restricted)

---

## Authorization Gate

- **Approved for Upload:** **YES** (for `dist/statement-collector-core-0.13.0-rc.2.zip` only; upload verified complete)
- **Approved for Launch:** **NO**

*STOP GATE: Core plugin `0.13.0-rc.2` bootstrap validation is complete; theme upload, product fixture creation, and public launch remain strictly prohibited.*
