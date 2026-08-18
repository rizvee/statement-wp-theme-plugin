# Statement Collector's Piece — Client Feedback Directives (Sprint 07)

**Date**: 2026-08-18 / 2026-08-19
**Source**: Client Review & Direct Visual Feedback
**Status**: Confirmed & Approved for Immediate Implementation

---

## 1. Summary of Client Directives

1. **About Page**:
   - Strictly NO images. Pure typography / text only.
   - Generous whitespace, luxury editorial line length.
   - Punctuation requirement: Remove long dashes (em-dashes) and replace with clean, natural punctuation.
   - Keep business/exclusivity/sustainability claims editable in WordPress admin (no technical inventory locks).

2. **Contact Page**:
   - Strictly NO images. Pure typography / text only.
   - Confirmed primary email: `info@mystatement.store`.
   - Instagram channel: `@statement.au`.
   - Facebook channel: Configurable via Customizer (`statement_facebook_url`). Rendered only when populated.
   - Zero fabricated phone numbers, postal addresses, or operating hours.

3. **Header**:
   - Must use the actual Statement Brand Logo (`statement-logo.png` / `custom_logo` support).
   - Do NOT display only a text wordmark when the logo is present.
   - Retain elegant balance across desktop (Left: SHOP, DROPS, ARCHIVE; Center: LOGO; Right: ABOUT, SEARCH, ACCOUNT, BAG) and mobile (Left: MENU; Center: LOGO; Right: BAG).

4. **Journal Removal**:
   - Client does NOT want the Journal section.
   - Remove Journal from: Desktop navigation, mobile navigation drawer, footer navigation, homepage, and public primary UX.
   - Retain existing post data safely without destructive deletion.

5. **Homepage Information Architecture**:
   - Remove the separate redundant "Current Pieces" section.
   - Current release sequence:
     1. Header (with authentic Statement logo)
     2. Cinematic Responsive Hero (1920×1080 wide desktop campaign photographs / 720×1280 mobile video + portrait stills)
     3. Drop 001 — Monogram Study (with 2 product cards side-by-side)
     4. Drops / Upcoming Drops Editorial List (Drop 001 + Upcoming: Drop 002, Drop 003)
     5. Minimal Footer (Shop, Drops, Archive, Account, About, Contact)
   - Omit separate email capture / newsletter from default homepage flow.

6. **Drops & Upcoming Drops**:
   - Text-first editorial list format.
   - Current release: `Drop 001 — Monogram Study` (links to `/drop/drop-001-monogram-study/` or Drop 001 view).
   - Upcoming releases: `Drop 002`, `Drop 003` marked with understated `UPCOMING` status.
   - Zero fabricated products, dates, or prices for upcoming drops.

7. **Hero Section Complete Rebuild (#1 Visual Priority)**:
   - Desktop: Wide 1920×1080 campaign images, aspect-ratio and focal awareness (height ~70–85svh), full-bleed cinematic crossfade/slider with minimal text ("DROP 001", "EXPLORE RELEASE &rarr;").
   - Mobile: 720×1280 9:16 portrait video (`statement-hero-mobile-monogram.mp4`), muted, autoplay, loop, playsinline, poster fallback, `prefers-reduced-motion` compliance.
   - Conditional media delivery to avoid bandwidth waste across devices.
   - Full Customizer support for admin slide configuration.

8. **Product Media Replacement & Hoodie Metadata Remediation**:
   - Replace earlier placeholder media with the real client jacket and hoodie photography suites.
   - Completely eradicate mislabeled "Monogram Jacket" metadata from the Panelled Hood Jacket assets (`panelled-hood-jacket`).
   - Panelled Hood Jacket MUST include size **XL** (S, M, L, XL).

9. **PDP Conversion Polish & Size Guide**:
   - Re-proportion PDP layout (60% gallery / 40% sticky summary).
   - Size Guide: Elevated visual presence (`SIZE GUIDE ↗`), placed adjacent to size selector, accessible touch targets (>=44px), clean modal drawer.

10. **Shop Catalog**:
    - Centered, high-impact 2-column editorial spread for the 2 Drop 001 products.
