# Statement — Client Visual Review Guide (Visual Sprint 02)

**Document Version:** 2.0.0
**Candidate Release:** Theme `0.13.0-rc.6` | Client Demo Tool `0.1.0` | Core `0.13.0-rc.9`
**Brand Principle:** CRAFTED. NOT MASS MADE.

---

## 1. Overview of Visual & Editorial Enhancements

Visual Sprint 02 transitions the Statement storefront from an early theme shell to a fully realized, populated luxury fashion store using the client's actual photography and brand artifacts.

### Key Visual Milestones in This Release:
1. **Light-First Editorial Hierarchy:** 85% gallery ivory (`#F3F0EA`) and warm white (`#FAF9F6`) canvas with ink navy accents, matching reference aesthetics (Maroo Clo, Mertra).
2. **Real Brand Assets & Editorial Lookbook:** 18 curated derivative photographs imported and integrated across the Homepage, Drops page, Lookbook grid, Product galleries, and Brand Object section.
3. **Dedicated Drops Directory (`/drops/`):** Clean architectural index dividing releases by Drop, showcasing the current active release (*Drop 001 — Monogram Study*) and past archival releases.
4. **Interactive Size Guide Dialog:** Native, accessible modal on variable product PDPs with CM measurements (Chest, Length, Sleeve) taken flat.
5. **P0 Legacy Front-Page Isolation:** Third-party page builders and legacy Elementor containers are completely blocked from hijacking the front page via high-priority template ownership guards.
6. **Purged Restock Claims:** All public *"Never Restocked"* or total edition numbers have been permanently retired in favor of the refined brand principle: *"CRAFTED. NOT MASS MADE."*

---

## 2. Step-by-Step Customer Journey Walkthrough

| Step | Page / Destination | URL | Key Elements to Review |
|---|---|---|---|
| **1** | **Homepage** | `/` | &bull; 85svh Campaign Hero with model front<br>&bull; Active Drop Banner (*Drop 001*)<br>&bull; Visual Lookbook rhythm (Model &rarr; Flat lay &rarr; Macro &rarr; Hood)<br>&bull; Selected Pieces grid<br>&bull; The Object (Packaging & Provenance)<br>&bull; Brand Principle Manifesto (*Crafted. Not Mass Made.*) |
| **2** | **Drops Directory** | `/drops/` | &bull; Header with release philosophy<br>&bull; Current Drop Feature Card with direct link<br>&bull; Past Drops section |
| **3** | **Drop 001 Collection** | `/drop/drop-001-monogram-study/` | &bull; Curated editorial collection grid<br>&bull; Real campaign cards for Monogram Jacket and Panelled Hood Jacket |
| **4** | **Product 01: Monogram Jacket** | `/product/monogram-jacquard-jacket/` | &bull; 4:5 Multi-image gallery (Front, Back, Concrete flat lay, Collar macro, Slate flat lay)<br>&bull; Size Selector (S, M, L)<br>&bull; Interactive Size Guide trigger and accessible modal<br>&bull; Add to Bag action |
| **5** | **Product 02: Panelled Hood Jacket** | `/product/panelled-hood-jacket/` | &bull; Multi-angle photography (Front, Cathedral architecture, Gold/Navy embroidery macro, Night editorial)<br>&bull; S, M, L size selection<br>&bull; Flat CM size guide |
| **6** | **Shopping Bag** | `/cart/` | &bull; Clean luxury table layout with thumbnail, quantity controls, and order totals |
| **7** | **Secure Checkout** | `/checkout/` | &bull; Single-column distraction-free checkout with clear express/card payment gateways |
| **8** | **Permanent Archive** | `/archive/` | &bull; Historical archive entries for previous editions |

---

## 3. Viewport Verification Checklist

### Desktop (1440px+)
- [x] Primary Navigation: `SHOP` | `DROPS` | `ARCHIVE` aligned with luxury spacing.
- [x] Lookbook grid displays asymmetric 4-column magazine layout.
- [x] PDP sticky summary column stays pinned while scrolling through high-res gallery images.
- [x] Size Guide modal opens centrally with blurred backdrop.

### Tablet (768px - 1024px)
- [x] Header switches cleanly between full nav and compact layout.
- [x] Lookbook grid adapts to 2-column balanced flow.
- [x] PDP gallery displays 2-column stacked or scrollable presentation.

### Mobile (375px - 430px)
- [x] Full-bleed edge-to-edge photography.
- [x] Horizontal scroll-snap gallery on PDP.
- [x] Drawer navigation: `SHOP` | `DROPS` | `ARCHIVE` | `ACCOUNT`.
- [x] Add to Bag button and Size Guide triggers easily reachable via thumb zone.

---

## 4. Admin Seeder Tool Reference

For manual testing and demo population, the **Statement Client Demo Plugin (v0.1.0)** is packaged under `dist/statement-client-demo-0.1.0.zip`.
- Accessible via **WooCommerce &rarr; Client Demo** in WP Admin.
- Provides **Dry Run Analysis** and **Seed / Update Client Demo** actions.
- Automatically preserves previous front-page settings in option `statement_client_demo_rollback`.
