# Production Product Import Plan — Statement Drop 001

## 1. Overview & Purpose

This document defines the schema, mapping rules, and execution procedure for creating and importing genuine production products for the debut launch: **Statement Drop 001**.

All production product creation strictly adheres to the core domain architecture and business invariants:
- **Core Scarcity Invariant**: `ONE RELEASE → LIMITED AVAILABILITY → SOLD OUT → NEVER RESTOCKED → PERMANENT ARCHIVE`
- **Zero Urgency Fabrication**: No fake countdown timers, no restock notifications, no waitlist buttons, no artificially generated stock count alerts.
- **Durable Provenance Snapshotting**: Complete frozen metadata capture at the moment of checkout.

---

## 2. Drop 001 Classification & Configuration

| Field | Setting | Value / Format |
| --- | --- | --- |
| **Drop Name** | Name | `Statement Drop 001` |
| **Drop Slug** | Slug | `drop-001` |
| **Taxonomy** | Taxonomy | `statement_drop` |
| **Private Access Window** | Duration | 7 Days (e.g., `duration = 7`, `unit = days`) |
| **Closes At (UTC)** | Target Datetime | Explicit UTC ISO string (e.g., `2026-09-01 00:00:00 UTC`) |
| **Send Access Email** | Email Delivery | `yes` (dispatches branded `EmailAccessGranted` with return token) |
| **Reminder Email** | Lifecycle Delay | `yes`, 24 hours prior to access window close |

---

## 3. Product Catalog Specification & Mapping

### Piece 01: Statement Monogram Overshirt (Variable Piece)

| Attribute | Value / Specification |
| --- | --- |
| **Product Title** | `Statement Monogram Overshirt` |
| **Product Slug** | `statement-monogram-overshirt` |
| **Product Type** | Variable Product (`variable`) |
| **Initial Release State** | `PRIVATE_ACCESS` (`_statement_release_state = 'PRIVATE_ACCESS'`) |
| **Edition Label** | `Drop 001 Edition` (`_statement_edition_label = 'Drop 001 Edition'`) |
| **Drop Association** | Assigned to `statement_drop`: `drop-001` |
| **Base Price (AUD)** | `$340.00` (`_price = 340`, `_regular_price = 340`) |
| **Manage Stock** | `yes` (Strict physical inventory allocation) |
| **Total Allocation** | 50 Units total across sizes |
| **Variations** | Attribute: `pa_size` (Values: `Small` [10], `Medium` [18], `Large` [15], `X-Large` [7]) |
| **Variation SKUs** | `STMT-D01-MOS-S`, `STMT-D01-MOS-M`, `STMT-D01-MOS-L`, `STMT-D01-MOS-XL` |
| **Featured Image** | 4:5 portrait high-resolution photography with descriptive alt text (`Statement Monogram Overshirt in Japanese heavy cotton twill`) |
| **Gallery Images** | Close-up texture, cuff detailing, back silhouette |
| **Short Description** | "Structured Japanese cotton twill with bespoke monogram jacquard weave. Crafted for a relaxed architectural silhouette." |
| **SEO Meta** | Title: `Statement Monogram Overshirt — Drop 001 | Statement`, Description: `Crafted in limited quantity. Piece 01 of Statement Drop 001.` |

---

### Piece 02: Statement Tailored Trouser (Variable Piece)

| Attribute | Value / Specification |
| --- | --- |
| **Product Title** | `Statement Tailored Trouser` |
| **Product Slug** | `statement-tailored-trouser` |
| **Product Type** | Variable Product (`variable`) |
| **Initial Release State** | `PRIVATE_ACCESS` (`_statement_release_state = 'PRIVATE_ACCESS'`) |
| **Edition Label** | `Drop 001 Edition` (`_statement_edition_label = 'Drop 001 Edition'`) |
| **Drop Association** | Assigned to `statement_drop`: `drop-001` |
| **Base Price (AUD)** | `$280.00` (`_price = 280`, `_regular_price = 280`) |
| **Manage Stock** | `yes` |
| **Total Allocation** | 40 Units total across sizes |
| **Variations** | Attribute: `pa_size` (Values: `30` [8], `32` [14], `34` [12], `36` [6]) |
| **Variation SKUs** | `STMT-D01-STR-30`, `STMT-D01-STR-32`, `STMT-D01-STR-34`, `STMT-D01-STR-36` |
| **Featured Image** | 4:5 portrait clean studio photography |
| **Short Description** | "High-twist virgin wool tailored trouser with clean single pleat and deep tab closure." |

---

### Piece 03: Statement Collector Silk Scarf (Simple Piece)

| Attribute | Value / Specification |
| --- | --- |
| **Product Title** | `Statement Collector Silk Scarf` |
| **Product Slug** | `statement-collector-silk-scarf` |
| **Product Type** | Simple Product (`simple`) |
| **Initial Release State** | `PRIVATE_ACCESS` (`_statement_release_state = 'PRIVATE_ACCESS'`) |
| **Edition Label** | `Drop 001 Edition` (`_statement_edition_label = 'Drop 001 Edition'`) |
| **Drop Association** | Assigned to `statement_drop`: `drop-001` |
| **Price (AUD)** | `$160.00` (`_price = 160`, `_regular_price = 160`) |
| **Manage Stock** | `yes` |
| **Total Allocation** | 30 Units |
| **SKU** | `STMT-D01-CSS-01` |
| **Featured Image** | 4:5 portrait artwork photography |
| **Short Description** | "100% Mulberry silk twill hand-rolled scarf featuring architectural line illustrations from Drop 001." |

---

## 4. Import & Setup Procedure

1. **Pre-Import Verification**:
   - Ensure `statement-integration-fixtures` is completely uninstalled.
   - Confirm WooCommerce currency is set to `AUD` ($).
   - Ensure Secret Vault is initialized and functional.
2. **Create Drop Taxonomy Term**:
   - In WP Admin -> Products -> Drops (or via WP CLI): Create term `Statement Drop 001` (slug: `drop-001`).
   - Set term meta: `_statement_private_access_duration = 7`, `_statement_private_access_duration_unit = days`, `_statement_private_access_closes_at = [DATETIME UTC]`, `_statement_send_access_email = yes`.
3. **Create Products**:
   - Upload high-resolution 4:5 portrait media assets to WP Media Library.
   - Create products with exact SKUs, variation attributes, AUD prices, and inventory counts.
   - Set custom field `_statement_release_state` to `PRIVATE_ACCESS`.
   - Set custom field `_statement_edition_label` to `Drop 001 Edition`.
   - Assign to Drop `Statement Drop 001`.
4. **Verification Gate**:
   - Verify products are completely invisible to anonymous visitors on `/shop/`, `/`, REST API, and Store API.
   - Verify `/drop/drop-001/` renders the clean, branded Private Access Gate.
