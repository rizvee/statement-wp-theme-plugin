# Statement Drop 001 — Production Content Intake Specification

## Instructions for Operator
Fill in the `[TBD]` placeholders below with confirmed commercial and editorial data prior to production import. Do not publish until all required fields are complete.

---

## 1. Drop Configuration

| Parameter | Specification | Operator Input |
| --- | --- | --- |
| **Drop Title** | Name of the debut collection | `[TBD: e.g. Statement Drop 001]` |
| **Drop Slug** | URL identifier (`/drop/[slug]/`) | `[TBD: e.g. drop-001]` |
| **Private Access Duration** | Number of days/hours for individual grant | `[TBD: e.g. 7]` |
| **Duration Unit** | `days` / `hours` / `minutes` | `[TBD: days]` |
| **Drop Closes At (UTC)** | Target absolute end time for drop | `[TBD: e.g. 2026-09-01 00:00:00 UTC]` |
| **Send Access Email** | Deliver branded email with return token | `yes` |
| **Reminder Enabled** | Send reminder email before window close | `[TBD: yes / no]` |
| **Reminder Delay** | Time before window close to send reminder | `[TBD: 24 hours]` |

---

## 2. Product Pieces Specification

### Piece 01

| Attribute | Field Name | Operator Input |
| --- | --- | --- |
| **Product Title** | Name | `[TBD: e.g. Statement Monogram Overshirt]` |
| **Product Slug** | Slug | `[TBD: e.g. statement-monogram-overshirt]` |
| **Product Type** | `simple` / `variable` | `[TBD: variable]` |
| **Edition Label** | Canonical edition string | `[TBD: Drop 001 Edition]` |
| **Price (AUD)** | Regular price in AUD ($) | `[TBD: e.g. 340.00]` |
| **Manage Stock** | Enable stock management | `yes` |
| **Total Allocation** | Total piece inventory | `[TBD: Units]` |
| **Size Attribute** | Values for size variation | `[TBD: e.g. Small, Medium, Large, X-Large]` |
| **Size Stock Matrix** | Allocation per size | `[TBD: S: X, M: Y, L: Z, XL: W]` |
| **SKU Prefix** | Base SKU | `[TBD: e.g. STMT-D01-MOS]` |
| **Short Description** | Editorial summary (1-2 sentences) | `[TBD: Description of craftsmanship and cut]` |
| **Full Description** | Detailed specifications, material & care | `[TBD: Detailed fabrication and provenance]` |
| **Featured Media** | Filename in Media Library | `[TBD: piece-01-hero.jpg]` |
| **Gallery Media** | Filenames in Media Library | `[TBD: piece-01-gallery-1.jpg, ...]` |
| **SEO Title** | Page `<title>` tag | `[TBD: Title — Drop 001 | Statement]` |
| **SEO Description** | Meta description | `[TBD: Compelling concise meta description]` |

---

### Piece 02

| Attribute | Field Name | Operator Input |
| --- | --- | --- |
| **Product Title** | Name | `[TBD]` |
| **Product Slug** | Slug | `[TBD]` |
| **Product Type** | `simple` / `variable` | `[TBD]` |
| **Edition Label** | Canonical edition string | `[TBD: Drop 001 Edition]` |
| **Price (AUD)** | Regular price in AUD ($) | `[TBD]` |
| **Manage Stock** | Enable stock management | `yes` |
| **Total Allocation** | Total piece inventory | `[TBD]` |
| **Size Attribute** | Values for size variation | `[TBD]` |
| **Size Stock Matrix** | Allocation per size | `[TBD]` |
| **SKU Prefix** | Base SKU | `[TBD]` |
| **Short Description** | Editorial summary | `[TBD]` |
| **Featured Media** | Filename | `[TBD]` |
| **Gallery Media** | Filenames | `[TBD]` |
| **SEO Title** | Page `<title>` | `[TBD]` |
| **SEO Description** | Meta description | `[TBD]` |

---

### Piece 03

| Attribute | Field Name | Operator Input |
| --- | --- | --- |
| **Product Title** | Name | `[TBD]` |
| **Product Slug** | Slug | `[TBD]` |
| **Product Type** | `simple` / `variable` | `[TBD]` |
| **Edition Label** | Canonical edition string | `[TBD: Drop 001 Edition]` |
| **Price (AUD)** | Regular price in AUD ($) | `[TBD]` |
| **Manage Stock** | Enable stock management | `yes` |
| **Total Allocation** | Total piece inventory | `[TBD]` |
| **SKU Prefix** | Base SKU | `[TBD]` |
| **Short Description** | Editorial summary | `[TBD]` |
| **Featured Media** | Filename | `[TBD]` |
| **Gallery Media** | Filenames | `[TBD]` |
| **SEO Title** | Page `<title>` | `[TBD]` |
| **SEO Description** | Meta description | `[TBD]` |

---

## 3. Scarcity & Commerce Verification Checkpoints

- [ ] All prices entered in Australian Dollars (AUD).
- [ ] No public total edition counters or countdown urgency widgets configured.
- [ ] Physical inventory accurately assigned; zero backorders allowed.
- [ ] Release state on initial creation set strictly to `PRIVATE_ACCESS`.
