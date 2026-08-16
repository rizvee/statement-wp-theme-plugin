# Statement Launch Gate Matrix — Objective Pre-Release Assessment

## 1. Launch Gate Overview

This matrix provides the single authoritative criteria checklist required before public launch. Every gate is classified with strict objective evidence:
- `PASS`: Verifiably satisfied and proven with observable evidence.
- `BLOCKED`: Dependency or defect preventing progression.
- `INPUT_REQUIRED`: Awaiting operator business/legal/content inputs.
- `NOT_STARTED`: Scheduled in sequential cutover sequence.

---

## 2. Definitive Launch Gate Status

| Gate ID | Gate Name | Description / Requirement | Current Status | Objective Evidence / Prerequisite |
| --- | --- | --- | --- | --- |
| **GATE A** | **M13 Runtime Verification** | Full verification of Core, Theme, and Fixtures on Atomic | `PASS` | Theme `0.13.0-rc.4`, Core `0.13.0-rc.9`, Fixtures `0.3.3` active; Expiry & Reminder tests `RUNTIME_PASS` |
| **GATE B** | **Post-RC Full Backup** | Complete point-in-time backup on WordPress.com hosting | `BLOCKED` | Operator must trigger manual backup in WordPress.com dashboard prior to fixture purge |
| **GATE C** | **Post-RC Security Scan** | Clean Jetpack security scan post-dating current RC code | `BLOCKED` | Latest audit scan started 2026-08-15; operator must confirm clean scan post-dating Aug 16 RC code |
| **GATE D** | **Fixture Clean Purge** | Test products, drop terms, and fixture plugin uninstalled | `NOT_STARTED` | Awaiting Gate B and Gate C completion |
| **GATE E** | **Storefront Smoke Test** | Post-purge smoke test of Home, Shop, Archive, Cart, Checkout | `NOT_STARTED` | Verified ready via `scripts/test-production-readiness.mjs` |
| **GATE F** | **Legal Policies Published** | Statutory Australian Consumer Law pages live on storefront | `INPUT_REQUIRED` | Awaiting business entity details via `docs/legal-content-input.md` |
| **GATE G** | **Shipping Configured** | Australia Post flat rate / express methods active in WooCommerce | `INPUT_REQUIRED` | Awaiting dispatch details via `docs/shipping-configuration-input.md` |
| **GATE H** | **Live Payment Onboarded** | WooPayments live Stripe mode active with AUD settlements | `INPUT_REQUIRED` | Awaiting Stripe KYC connection via `docs/payment-configuration-input.md` |
| **GATE I** | **Drop 001 Imported** | Genuine production products, 4:5 media, and Private Drop created | `INPUT_REQUIRED` | Awaiting piece specifications via `docs/drop-001-production-input.md` |
| **GATE J** | **Live Card Test Checkout** | Exactly 1 real card $1 transaction + full refund verification | `NOT_STARTED` | Scheduled following Gate H and Gate I |
| **GATE K** | **SEO & Indexing Activated** | Canonical tags active, search engine visibility enabled | `NOT_STARTED` | Scheduled following Gate I and Gate J |
| **GATE L** | **Stable Release Decision** | Git tag created and candidate promoted to stable v1.0.0 | `NOT_STARTED` | Final release milestone closeout |

---

## 3. Decision Rules
- Zero production mutations or fixture deletions are permitted while **GATE B** and **GATE C** are unresolved.
- No public launch or marketing communications permitted until all 12 Gates are marked `PASS`.
