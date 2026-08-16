# Inventory & Release Lifecycle v2: Transition & Hardening Plan

**Document Version:** 1.0.0
**Context:** Statement Collector Architecture & Business Rules Hardening
**Target Milestone:** M15 / M16 Production Readiness

---

## 1. Executive Summary & Client Intent Gap Analysis

### Original v1 Domain Assumptions
The foundational v1 business rules established an immutable, irreversible release invariant:
1. One release per edition.
2. Positive stock allows purchasing while `LIVE`.
3. Once stock reaches zero, state automatically locks to `SOLD_OUT`.
4. Backward state transitions (e.g. `SOLD_OUT` &rarr; `LIVE` or `ARCHIVED` &rarr; `LIVE`) were strictly rejected.
5. Public storefront promised: *"Never Restocked"*.

### Client Feedback & Real-World Operating Requirements
Client feedback collected in Visual Sprint 01 & 02 clarified critical business realities:
1. **Demand-Based Production Flexibility:** High-demand collector pieces may receive limited additional production runs or pre-order allocations based on market reception.
2. **Public Messaging Update:** Public promises of *"Never Restocked"* or fixed public production totals have been permanently retired from customer-facing presentation. The active brand principle is **"CRAFTED. NOT MASS MADE."**
3. **Full Admin Control:** The merchant / store administrator must retain full operational control over products, inventory adjustments, price updates, and Drop assignments via native WooCommerce admin interfaces.
4. **Safety & Invariant Protection:** While admin control is expanded, the core guarantees—preventing race conditions, double-orders, unauthorized private Drop leakage, and cart overselling—must remain strictly protected.

---

## 2. Core Architecture Differences: v1 vs v2

| Dimension | Lifecycle v1 (Strict Terminal) | Lifecycle v2 (Admin Controlled / Demand Driven) |
|---|---|---|
| **Public Messaging** | "Crafted. Limited. Never Restocked." | "CRAFTED. NOT MASS MADE." |
| **Stock Replenishment** | Rejected once `SOLD_OUT` / `ARCHIVED`. | Allowed via authorized Admin actions; recalculates purchasability dynamically. |
| **State Transitions** | Unidirectional: `PREVIEW` &rarr; `LIVE` &rarr; `SOLD_OUT` &rarr; `ARCHIVED`. | Flexible Admin States: `DRAFT` / `PREVIEW` &rarr; `LIVE` &rarr; `SOLDOUT_TEMP` / `ARCHIVED`, with Admin Reopen capability. |
| **Drop Membership** | Fixed single Drop assignment. | Multi-Drop or Sequential Edition tags supported under admin discretion. |
| **Public Production Counts** | Displayed (e.g. "Edition of 50"). | Concealed from public view; operational stock managed in WooCommerce. |
| **Cart Enforcement** | Terminal check rejects zero-stock items. | Dynamic inventory reservation and real-time stock check at checkout boundary. |

---

## 3. Implementation Roadmap for Core v2 Transition

### Phase 1: Presentation & Copy Decoupling (Completed in M14 / rc.6)
- Public theme templates completely purged of *"Never Restocked"*, *"No Restocks"*, or total edition claims.
- Front-end badges standardized to: `LIVE`, `PREVIEW`, `SOLD OUT`, and `ARCHIVED`.
- Brand manifesto standardized to `CRAFTED. NOT MASS MADE.`

### Phase 2: Core State Engine Relaxing (Planned for Core 0.14.0)
- Update `Statement\Collector\Core\Release\ReleaseState` to permit explicit admin-initiated state changes (e.g. manual restock or additional run allocation).
- Add capability-gated filters `statement_allow_admin_restock` to protect automated cron jobs while empowering store operators.
- Ensure audit trails record all manual stock additions with timestamp and operator user ID.

### Phase 3: Data Migration & Backward Compatibility
- Existing products marked with `_statement_release_state = 'SOLD_OUT'` will transition to dynamic inventory checks.
- Zero breaking changes to existing order historical records or private access token hashes.
