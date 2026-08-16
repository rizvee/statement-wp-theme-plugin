# Production Product Import Plan — Statement Drop 001

## 1. Overview & Purpose

This document defines the schema, mapping rules, and execution procedure for creating and importing genuine production products for the debut launch: **Statement Drop 001**.

All production product creation strictly adheres to the core domain architecture and business invariants:
- **Core Scarcity Invariant**: `ONE RELEASE → LIMITED AVAILABILITY → SOLD OUT → NEVER RESTOCKED → PERMANENT ARCHIVE`
- **Zero Urgency Fabrication**: No fake countdown timers, no restock notifications, no waitlist buttons, no artificially generated stock count alerts.
- **Durable Provenance Snapshotting**: Complete frozen metadata capture at the moment of checkout.
- **Business Input Separation**: All commercial values (exact titles, prices, inventory counts, size matrices, photography assets) must be supplied via `docs/drop-001-production-input.md` before live import.

---

## 2. Drop 001 Specification Schema

| Field | Setting | Value / Status |
| --- | --- | --- |
| **Drop Name** | Name | `Statement Drop 001` (Or brand-specified title) |
| **Drop Slug** | Slug | `drop-001` (Or brand-specified slug) |
| **Taxonomy** | Taxonomy | `statement_drop` |
| **Private Access Window** | Duration | `BUSINESS_INPUT_REQUIRED` (e.g., 3, 5, or 7 Days) |
| **Closes At (UTC)** | Target Datetime | `BUSINESS_INPUT_REQUIRED` (Explicit UTC ISO string, e.g., `YYYY-MM-DD HH:MM:SS UTC`) |
| **Send Access Email** | Email Delivery | `yes` (dispatches branded `EmailAccessGranted` with return token) |
| **Reminder Email** | Lifecycle Delay | `yes` / `no` (`BUSINESS_INPUT_REQUIRED`) |

---

## 3. Product Catalog Specification Schema (Per Piece)

Each piece in Drop 001 must define the following required fields before creation:

```
Piece [N]: [PRODUCT_TITLE]
--------------------------------------------------------------------------------
1. Product Title:       [BUSINESS_INPUT_REQUIRED] (e.g. "Statement Monogram Overshirt")
2. Product Slug:        [BUSINESS_INPUT_REQUIRED] (e.g. "statement-monogram-overshirt")
3. Product Type:        simple | variable
4. Initial State:       PRIVATE_ACCESS (_statement_release_state = 'PRIVATE_ACCESS')
5. Edition Label:       [BUSINESS_INPUT_REQUIRED] (e.g. "Drop 001 Edition")
6. Drop Association:    Assigned to statement_drop term (e.g. "drop-001")
7. Price (AUD):         [BUSINESS_INPUT_REQUIRED] (e.g. $340.00 AUD)
8. Stock Management:    yes (Physical allocation managed at product or variation level)
9. Total Inventory:     [BUSINESS_INPUT_REQUIRED] (Total units allocated to this release)
10. Variations:         If variable: attribute pa_size (e.g. Small, Medium, Large, X-Large)
11. Variation SKUs:     If variable: [SKU-S, SKU-M, SKU-L, SKU-XL]
12. Featured Image:     4:5 portrait high-resolution photography asset
13. Gallery Images:     Close-up texture, silhouette, back view photography assets
14. Short Description:  [BUSINESS_INPUT_REQUIRED] (Editorial composition & craft details)
15. SEO Title:          [BUSINESS_INPUT_REQUIRED]
16. SEO Description:    [BUSINESS_INPUT_REQUIRED]
17. Alt Text:           Descriptive image alt text
--------------------------------------------------------------------------------
```

---

## 4. Import & Setup Procedure

1. **Pre-Import Verification**:
   - Ensure `statement-integration-fixtures` has been completely purged and uninstalled.
   - Confirm WooCommerce currency is set to `AUD` ($).
   - Ensure Secret Vault is initialized and functional on Atomic hosting.
2. **Create Drop Taxonomy Term**:
   - In WP Admin -> Products -> Drops (or via WP CLI): Create term for Drop 001.
   - Set validated term meta: `_statement_private_access_duration`, `_statement_private_access_duration_unit`, `_statement_private_access_closes_at`, `_statement_send_access_email`.
3. **Create Products**:
   - Upload high-resolution 4:5 portrait media assets to WP Media Library.
   - Create products with exact SKUs, variation attributes, AUD prices, and inventory counts.
   - Set custom field `_statement_release_state` to `PRIVATE_ACCESS`.
   - Set custom field `_statement_edition_label` to the release edition string.
   - Assign to the Drop taxonomy term.
4. **Verification Gate**:
   - Verify products are completely invisible to anonymous visitors on `/shop/`, `/`, REST API, and Store API.
   - Verify `/drop/[slug]/` renders the clean, branded Private Access Gate.
