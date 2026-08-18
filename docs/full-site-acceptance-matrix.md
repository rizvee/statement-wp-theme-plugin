# Full-Site Acceptance Matrix & Device Verification

**Project:** Statement Collector's Piece (`mystatement.store`)
**Sprint:** 06.3 Post-Deployment Live Acceptance QA & Visual Refinement
**Baseline Date:** August 18, 2026
**Artifact Status:** Theme `0.13.0-rc.13` | Core `0.13.0-rc.13` | Client Demo `0.2.5` | Child Theme `0.1.0`

---

## 1. Route Verification & Acceptance Table

| Route Identifier | URL / Endpoint | HTTP | Title Contract | Primary H1 | Responsive Viewports Verified | Visual Status & Remediation |
|---|---|---|---|---|---|---|
| **HOME** | `/` | 200 | `Statement` | `N/A` (Brand Logotype) | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Image-first hero slider active, no raw text overlays, 4:5 fashion product cards. |
| **SHOP** | `/shop/` | 200 | `Shop – Statement` | `Shop` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS (Fixed)** — Root cause resolved in `catalog.css` & `product-card.css`. Restored `.statement-piece` token styles, eliminated browser list bullets, luxury 2/3/4-col grid. |
| **DROPS_INDEX** | `/drops/` | 200 | `Drops – Statement` | `DROPS` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Editorial layout with release state tags and piece inventory count. |
| **CURRENT_DROP** | `/drop/drop-001-monogram-study/` | 200 | `Drop 001 — Monogram Study – Statement` | `Drop 001 — Monogram Study` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Release date, curated description, drop catalog grid. |
| **MONOGRAM_PDP** | `/product/monogram-jacquard-jacket/` | 200 | `Monogram Jacquard Jacket – Statement` | `Monogram Jacquard Jacket` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS (Fixed)** — Duplicate drop metadata removed from `summary.php`. Clean edition provenance badge, styled size selector, 100% full-bleed gallery. |
| **PANELLED_HOOD_PDP** | `/product/panelled-hood-jacket/` | 200 | `Panelled Hood Jacket – Statement` | `Panelled Hood Jacket` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS (Fixed)** — Removed duplicate taxonomy echo in `summary.php`. Gallery composition with horizontal thumb rail on mobile. |
| **ARCHIVE** | `/archive/` | 200 | `Archive – Statement` | `ARCHIVE` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Permanent scarcity historical archive, past drops record, internal test fixtures strictly excluded by `PublicApi` and `Visibility`. |
| **ABOUT** | `/about/` | 200 | `About – Statement` | `CRAFTED. NOT MASS MADE.` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Brand pillars, leather patch photography, typography hierarchy. |
| **CONTACT** | `/contact/` | 200 | `Contact – Statement` | `CONTACT` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Luxury client services inquiry cards, concierge studio layout, responsive single-col mobile stack. |
| **JOURNAL** | `/journal/` | 200 | `Journal – Statement` | `JOURNAL` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Luxury editorial card grid, 16:10 aspect ratio media, typography tokens. |
| **CART** | `/cart/` | 200 | `Cart – Statement` | `Cart` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Restrained typography, no coupon bloat, responsive sticky summary on desktop, 320px viewport overflow safe. |
| **CHECKOUT** | `/checkout/` | 200 | `Checkout – Statement` | `Checkout` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Distraction-free two-column flow on desktop, single-column stack on mobile, clean form controls. |
| **MY_ACCOUNT** | `/my-account/` | 200 | `My account – Statement` | `My account` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Restrained login/dashboard navigation, luxury border-grey inputs. |
| **SEARCH_RESULTS** | `/?s=monogram` | 200 | `Search Results for "monogram"` | `RESULTS FOR "MONOGRAM"` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS (Fixed)** — Upgraded `index.php` template to detect `is_search()`, dynamic H1 search query heading, fixture isolation. |
| **SEARCH_EMPTY** | `/?s=nonexistentxyz` | 200 | `Search Results for "nonexistentxyz"` | `RESULTS FOR "NONEXISTENTXYZ"` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Restrained empty message: "No matching pieces or journal entries found." |
| **NOT_FOUND_404** | `/404-test-route/` | 404 | `Page not found – Statement` | `NOTHING HERE.` | Desktop (1440px), Laptop (1024px), Tablet (768px), Mobile (375px/320px) | **PASS** — Editorial 404 canvas with Return Home CTA. |

---

## 2. Device Viewport Matrix & Visual Checks

### Desktop (1440px & 1920px)
- **Header:** 3-zone luxury split layout (`SHOP / DROPS / ARCHIVE` | `STATEMENT` | `ABOUT / SEARCH / ACCOUNT / BAG`).
- **Hero:** Full-bleed cinematic 16:9 carousel with subtle pagination dots and previous/next arrows.
- **Catalog Grids:** 4-column balanced grid with `gap: 2.5rem`, uniform 4:5 image cards, hover micro-scaling (`scale(1.025)`).
- **PDP:** 2-column asymmetric split (left sticky gallery, right sticky product summary and size/purchase controls).

### Laptop / Small Desktop (1024px)
- **Header:** Preserves desktop links with refined padding.
- **Catalog Grids:** 3-column balanced grid.
- **PDP:** Two-column layout with fluid sizing (`clamp(...)` typography).

### Tablet (768px)
- **Header:** Switches smoothly to mobile drawer toggle (`MENU | STATEMENT | BAG`).
- **Catalog Grids:** 2-column balanced grid.
- **PDP:** Single-column stacked layout with gallery above summary.

### Mobile (375px & 320px)
- **Header:** Minimal mobile bar (`MENU` button, centered `STATEMENT` wordmark, live `BAG (N)` count).
- **Hero:** 4:5 aspect ratio responsive slider with high-resolution mobile asset crop.
- **Catalog Grids:** 1-column / 2-column fluid cards with full-width tap targets.
- **PDP Gallery:** Horizontal swipe thumbnail rail with touch scrolling.
- **Zero Horizontal Overflow:** Verified strictly at 320px viewport across all 16 routes.

