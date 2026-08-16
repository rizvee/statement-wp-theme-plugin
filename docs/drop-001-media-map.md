# Statement Drop 001 — Production Media Asset Mapping

## 1. Overview & Aspect Ratio Invariants

All storefront photography must satisfy the theme's core visual presentation standards:
- **Card & Hero Standard**: 4:5 portrait ratio (e.g. `1600 × 2000 px` or `1200 × 1500 px`).
- **Object Fit**: `object-fit: cover` with centered composition.
- **Optimization**: WebP / JPEG with progressive rendering and descriptive, accessible alt text.
- **Color Grading**: Neutral luxury background tones harmonizing with `gallery-ivory` (`#F7F6F2`) and `warm-white` (`#FCFBF9`).

---

## 2. Storefront Media Role Mapping

| Placement Role | Viewport / Context | Aspect Ratio | Target Dimensions | Required Alt Text Pattern | Asset Filename |
| --- | --- | --- | --- | --- | --- |
| **Homepage Hero** | Desktop Hero Feature | 4:5 / Responsive | `1600 × 2000 px` | `Statement Drop 001 Campaign hero featuring [Piece Name]` | `[TBD: hero-drop-001.webp]` |
| **Homepage Mobile Crop** | Mobile Viewports (≤768px) | 4:5 | `1080 × 1350 px` | `Statement Drop 001 Campaign mobile hero` | `[TBD: hero-drop-001-mobile.webp]` |
| **Piece 01: Featured Image** | Shop Loop & Catalog Card | 4:5 | `1200 × 1500 px` | `[Piece 01 Title] in [Material/Color] front studio view` | `[TBD: piece-01-featured.webp]` |
| **Piece 01: Gallery 01 (Detail)** | Single PDP Gallery | 4:5 | `1200 × 1500 px` | `[Piece 01 Title] fabric weave and button craftsmanship detail` | `[TBD: piece-01-gallery-detail.webp]` |
| **Piece 01: Gallery 02 (Silhouette)** | Single PDP Gallery | 4:5 | `1200 × 1500 px` | `[Piece 01 Title] back silhouette and tailored fit` | `[TBD: piece-01-gallery-back.webp]` |
| **Piece 02: Featured Image** | Shop Loop & Catalog Card | 4:5 | `1200 × 1500 px` | `[Piece 02 Title] front view` | `[TBD: piece-02-featured.webp]` |
| **Piece 02: Gallery 01** | Single PDP Gallery | 4:5 | `1200 × 1500 px` | `[Piece 02 Title] craftsmanship detail` | `[TBD: piece-02-gallery-1.webp]` |
| **Piece 03: Featured Image** | Shop Loop & Catalog Card | 4:5 | `1200 × 1500 px` | `[Piece 03 Title] flat artwork presentation` | `[TBD: piece-03-featured.webp]` |
| **Social Share (OG Image)** | OpenGraph / Twitter Cards | 1.91:1 | `1200 × 630 px` | `Statement — Crafted. Limited. Never Restocked.` | `[TBD: og-statement-brand.webp]` |

---

## 3. Media Upload & Attachment Process

1. In WordPress Admin -> Media -> Add New: Upload compressed WebP/JPEG assets.
2. In Attachment Details: Populate English title and descriptive Alternative Text.
3. In WooCommerce Product Editor: Assign Featured Image and Product Gallery images.
