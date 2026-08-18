# Full-Site Acceptance Matrix & Device Verification (Visual Sprint 07)

**Project:** Statement Collector's Piece (`mystatement.store`)
**Sprint:** 07 Client-Directed Visual Realization & Media Integration
**Baseline Date:** August 19, 2026
**Artifact Status:** Theme `0.13.0-rc.14` | Core `0.13.0-rc.13` | Client Demo `0.2.6` | Child Theme `0.1.0`

---

## 1. Route Verification & Acceptance Table

| Route Identifier | URL / Endpoint | HTTP | Title Contract | Primary H1 / Brand | Responsive Viewports Verified | Visual Status & Remediation |
|---|---|---|---|---|---|---|
| **HOME** | `/` | 200 | `Statement` | Statement Brand Logo (`statement-logo.png`) | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Authentic Statement Brand Logo, 1920×1080 wide campaign slides on desktop, 720×1280 portrait MP4 video on mobile, 5-part hierarchy (Hero -> Drop 001 with 2 products -> Drops/Upcoming list -> Footer). |
| **SHOP** | `/shop/` | 200 | `Shop – Statement` | `Shop` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — High-impact centered 2-column luxury spread for Drop 001 pieces with studio model photography. |
| **DROPS_INDEX** | `/drops/` | 200 | `Drops – Statement` | `DROPS` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Editorial text-first releases directory (Drop 001 active link + Drop 002/003 upcoming badges). |
| **CURRENT_DROP** | `/drop/drop-001-monogram-study/` | 200 | `Drop 001 — Monogram Study – Statement` | `Drop 001 — Monogram Study` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Curated editorial description, side-by-side product cards with WebP studio photography. |
| **MONOGRAM_PDP** | `/product/monogram-jacquard-jacket/` | 200 | `Monogram Jacquard Jacket – Statement` | `Monogram Jacquard Jacket` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — 5-shot studio WebP gallery package, elevated `SIZE GUIDE ↗` trigger, size selector (S, M, L), sticky summary. |
| **PANELLED_HOOD_PDP** | `/product/panelled-hood-jacket/` | 200 | `Panelled Hood Jacket – Statement` | `Panelled Hood Jacket` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — 6-shot off-white hoodie WebP suite, metadata contamination eliminated, size **XL** supported (S, M, L, XL), `SIZE GUIDE ↗` trigger. |
| **ARCHIVE** | `/archive/` | 200 | `Archive – Statement` | `ARCHIVE` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Permanent historical scarcity archive, past releases record, zero back-in-stock / waitlist language. |
| **ABOUT** | `/about/` | 200 | `About – Statement` | `STATEMENT` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Strictly zero images, centered editorial typography prose, clean natural punctuation without em-dashes. |
| **CONTACT** | `/contact/` | 200 | `Contact – Statement` | `CONTACT` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Strictly zero images, confirmed email (`info@mystatement.store`), Instagram (`@statement.au`), optional Facebook channel, zero fake hours/numbers. |
| **CART** | `/cart/` | 200 | `Cart – Statement` | `YOUR BAG` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Clean luxury bag table, responsive totals card, 320px mobile protection with word break safety. |
| **CHECKOUT** | `/checkout/` | 200 | `Checkout – Statement` | `CHECKOUT` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Two-column distraction-free desktop layout, single-column mobile stack, secure gateway compatibility. |
| **MY_ACCOUNT** | `/my-account/` | 200 | `My account – Statement` | `MY ACCOUNT` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Clean authentication & order provenance portal. |
| **SEARCH_RESULTS** | `/?s=monogram` | 200 | `Search Results for "monogram"` | `RESULTS FOR "MONOGRAM"` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Dynamic search results grid with fixture isolation. |
| **NOT_FOUND_404** | `/404-test-route/` | 404 | `Page not found – Statement` | `NOTHING HERE.` | Desktop (1920px/1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Minimalist 404 canvas with Return Home CTA. |

---

## 2. Device Viewport Matrix & Visual Checks

### Desktop (1440px & 1920px)
- **Header:** Authentic Statement Brand Logo centered; left links: `SHOP / DROPS / ARCHIVE`; right links: `ABOUT / SEARCH / ACCOUNT / BAG`.
- **Hero:** Full-bleed 16:9 cinematic campaign photography (`clamp(34rem, 78svh, 54rem)`), restrained floating editorial badge (`DROP 001`, `EXPLORE RELEASE →`).
- **Active Drop:** Drop 001 editorial summary leading directly into 2 side-by-side 4:5 fashion product cards.
- **Drops Directory:** Clean text-first directory listing current release and upcoming drops.
- **PDP:** 60/40 balanced split (studio gallery / sticky summary with size selector and elevated `SIZE GUIDE ↗`).

### Laptop / Small Desktop (1024px)
- **Header:** Preserves desktop links and brand logo with responsive padding.
- **Hero:** Proportional fluid height scaling.
- **PDP:** Fluid typography and sticky buy box.

### Tablet (768px)
- **Header:** Mobile drawer toggle (`MENU | LOGO | BAG`).
- **Hero:** Responsive image scaling.
- **PDP:** Stacked layout with gallery above summary.

### Mobile (375px & 320px)
- **Header:** Touch-friendly mobile bar (`MENU` button, centered Statement Brand Logo, live `BAG (N)` counter).
- **Hero:** 720×1280 9:16 portrait video (`statement-hero-mobile-monogram.mp4`, autoplay, loop, muted, playsinline) with poster fallback and `prefers-reduced-motion` compliance.
- **Active Drop:** 2 stacked full-width luxury product cards with generous touch targets.
- **About & Contact:** Centered clean typography without images or horizontal overflow.
- **Zero Horizontal Overflow:** Strictly verified across all viewports down to 320px.
