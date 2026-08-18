# Statement Collector's Piece — New Assets Forensic Inventory (Sprint 07)

**Date**: 2026-08-19
**Source Directory**: `new assets/`
**Target Ingestion**: `tools/statement-client-demo/assets/` & `wp-content/themes/statement-collector-theme/assets/`

---

## 1. Executive Summary

A full forensic inventory and normalization pass was conducted on all newly delivered client media assets in `new assets/`.

Key findings:
1. **Desktop / Wide Campaign Imagery**: Three 1920×1080 cinematic architectural photographs were delivered, featuring both the Monogram Jacquard Jacket (archway & golden hour) and the Panelled Hood Jacket (dark cathedral/architectural space). These form the primary wide desktop hero suite.
2. **Mobile Video Asset**: An 8-second 720×1280 (9:16 portrait) studio video of the Monogram Jacket was delivered. This is mapped directly as the lead mobile hero experience.
3. **Product Media Packages**: Two ZIP / directory packages were delivered (`statement-web-images-drop-001 jacket` and `statement-web-images-drop-001 hoodie`).
4. **Metadata Defect Remediation**: The hoodie package metadata incorrectly labeled the Panelled Hood Jacket files as "black-monogram-jacket". All filenames, slugs (`panelled-hood-jacket`), product titles (`Panelled Hood Jacket`), and SEO alt texts were normalized and corrected to prevent contamination.
5. **Brand Logo**: The authentic high-resolution Statement logo was extracted and formatted into a transparent PNG (`statement-logo.png`) for both desktop and mobile headers.

---

## 2. Comprehensive Asset Inventory & Mapping Matrix

| Source File | Format | Dimensions | Orientation | Canonical Product | Shot Type | Role | Target Suitability | Normalized Destination File |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `WhatsApp Image 2026-08-19 at 1.17.33 AM.jpeg` | JPEG | 1920×1080 | Landscape (16:9) | Monogram Jacquard Jacket | Campaign / Arched Entrance | Hero Slide 01 | Desktop / Large Tablet Hero | `statement-hero-slide-monogram-arch.jpg` |
| `WhatsApp Image 2026-08-19 at 1.17.34 AM (1).jpeg` | JPEG | 1920×1080 | Landscape (16:9) | Monogram Jacquard Jacket | Campaign / Golden Light | Hero Slide 02 | Desktop / Large Tablet Hero | `statement-hero-slide-monogram-golden.jpg` |
| `WhatsApp Image 2026-08-19 at 1.17.34 AM.jpeg` | JPEG | 1920×1080 | Landscape (16:9) | Panelled Hood Jacket | Campaign / Architectural Night | Hero Slide 03 | Desktop / Large Tablet Hero | `statement-hero-slide-hood-arch.jpg` |
| `WhatsApp Video 2026-08-18 at 1.36.59 PM.mp4` | MP4 (H.264) | 720×1280 | Portrait (9:16) | Monogram Jacquard Jacket | Studio Video (8s, 24fps) | Lead Mobile Media | Mobile Hero (Autoplay, Muted, Loop) | `statement-hero-mobile-monogram.mp4` |
| `jacket/images/...model-front.webp` | WebP | 922×1152 | Portrait (4:5) | Monogram Jacquard Jacket | Studio Model Front | Featured / Primary Image | Shop Card, Drop Card, PDP Featured | `statement-monogram-jacket-model-front.webp` |
| `jacket/images/...product-front.webp` | WebP | 922×1152 | Portrait (4:5) | Monogram Jacquard Jacket | Studio Product Flat | Gallery Position 2 | PDP Gallery | `statement-monogram-jacket-product-front.webp` |
| `jacket/images/...model-side.webp` | WebP | 922×1152 | Portrait (4:5) | Monogram Jacquard Jacket | Model Side Profile | Gallery Position 3 | PDP Gallery | `statement-monogram-jacket-model-side.webp` |
| `jacket/images/...model-back.webp` | WebP | 922×1152 | Portrait (4:5) | Monogram Jacquard Jacket | Model Rear Studio | Gallery Position 4 | PDP Gallery | `statement-monogram-jacket-model-back.webp` |
| `jacket/images/...product-front-02.webp` | WebP | 922×1152 | Portrait (4:5) | Monogram Jacquard Jacket | Studio Product Construction | Gallery Position 5 | PDP Gallery | `statement-monogram-jacket-product-front-02.webp` |
| `hoodie/images/...product-front-03.webp` | WebP | 1122×1402 | Portrait (4:5) | Panelled Hood Jacket | Model Front Full | Featured / Primary Image | Shop Card, Drop Card, PDP Featured | `statement-panelled-hood-jacket-model-front.webp` |
| `hoodie/images/...product-front.webp` | WebP | 922×1152 | Portrait (4:5) | Panelled Hood Jacket | Studio Product Flat | Gallery Position 2 | PDP Gallery | `statement-panelled-hood-jacket-product-front.webp` |
| `hoodie/images/...model-side.webp` | WebP | 922×1152 | Portrait (4:5) | Panelled Hood Jacket | Model Side Profile | Gallery Position 3 | PDP Gallery | `statement-panelled-hood-jacket-model-side.webp` |
| `hoodie/images/...product-front-02.webp` | WebP | 922×1152 | Portrait (4:5) | Panelled Hood Jacket | Studio Product Alt | Gallery Position 4 | PDP Gallery | `statement-panelled-hood-jacket-product-front-02.webp` |
| `hoodie/images/...branding-detail.webp` | WebP | 3200×1800 | Landscape (16:9) | Panelled Hood Jacket | Insignia Macro Embroidery | Gallery Position 5 | PDP Gallery Macro Detail | `statement-panelled-hood-jacket-branding-detail.webp` |
| `hoodie/images/...product-front-04.webp` | WebP | 1920×1936 | Square (1:1) | Panelled Hood Jacket | High-res Product Cut | Gallery Position 6 | PDP Gallery High-Res Cut | `statement-panelled-hood-jacket-product-front-04.webp` |
| `more image video assets/Logo and designs/logo color.png` | PNG | 1473×2041 | Portrait | Statement Brand | Insignia & Wordmark | Brand Identity | Header Logo (Desktop & Mobile) | `statement-logo.png` |

---

## 3. SEO Metadata Normalization

### Monogram Jacquard Jacket (`monogram-jacquard-jacket`)
- **Primary Keyword**: `statement monogram jacket`
- **Alt Text (Featured)**: `Male model wearing the Statement Monogram Jacquard Jacket in a studio setting.`
- **Alt Text (Product Front)**: `Front view of the Statement Monogram Jacquard Jacket showing woven jacquard pattern.`
- **Alt Text (Model Side)**: `Side profile view of a model wearing the Statement Monogram Jacquard Jacket.`
- **Alt Text (Model Back)**: `Rear view of a model wearing the Statement Monogram Jacquard Jacket.`
- **Alt Text (Detail)**: `Close-up construction view of the Statement Monogram Jacquard Jacket.`

### Panelled Hood Jacket (`panelled-hood-jacket`)
- **Primary Keyword**: `statement panelled hood jacket`
- **Alt Text (Featured)**: `Model wearing the off-white Statement Panelled Hood Jacket with patterned sleeves.`
- **Alt Text (Product Front)**: `Studio front view of the Statement Panelled Hood Jacket with patterned sleeves and hood.`
- **Alt Text (Model Side)**: `Full-length side profile of a model wearing the Statement Panelled Hood Jacket.`
- **Alt Text (Detail)**: `Close-up detail of the embroidered geometric emblem on the Statement Panelled Hood Jacket.`
- **Alt Text (Construction)**: `Detailed construction cut of the Statement Panelled Hood Jacket.`
