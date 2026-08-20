# Statement Collector — Sprint 10 Premium UX Benchmark & Design Systems

## Executive Summary

Sprint 10 elevates the Statement digital flagship from a functional custom WooCommerce storefront into a world-class luxury digital piece. Benchmarked against the world's most disciplined fashion houses (The Row, Lemaire, Acne Studios, Bottega Veneta), this document codifies the design, typography, spacing, motion, and interaction standards executed in Theme `0.13.0-rc.20`, Core `0.13.0-rc.15`, and Fixtures `0.3.5`.

---

## 1. Aesthetic Benchmark & Brand Hierarchy

### 1.1 Visual Philosophy: Monolithic Restraint & Tactile Materiality
Statement operates on an absolute scarcity model:
$$\text{ONE RELEASE} \longrightarrow \text{LIMITED ALLOCATION} \longrightarrow \text{SOLD OUT} \longrightarrow \text{PERMANENT ARCHIVE}$$

The digital environment reflects this invariant through:
- **Zero generic e-commerce clutter**: No coupon popups, no "You May Also Like" cross-sell carousels, no fake urgency timers ("Hurry! 2 left in stock!"), and no generic star ratings.
- **Architectural Typography**: Clean typographic hierarchy powered by system sans-serif (`-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial`) with deliberate kerning (`0.12em` to `0.22em` on metadata codes).
- **Tactile Color Palette**: Defined in `theme.json`:
  - `near-black` (`#0d0f12`): Monolithic typography and primary accents.
  - `gallery-ivory` (`#f8f7f4`): Warm gallery canvas background.
  - `border-grey` (`#d8d4cc`): Hairline dividers and structured bounding boxes.
  - `soft-graphite` (`#55575c`): Secondary specification metadata.
  - `warm-white` (`#ffffff`): High-contrast campaign overlays.

---

## 2. Signature Component Benchmarks

### 2.1 The Signature Hero Slider
- **Framing & Aspect Ratio**:
  - Desktop: Wide 16:9 / 21:9 campaign landscape framing with safe-crop focal positioning (`center 20%`), height clamped dynamically between `clamp(34rem, 78svh, 54rem)`.
  - Mobile: Art-directed portrait video (`statement-hero-mobile-monogram.mp4`) with inline autoplay, muted, looped, metadata preloading, and high-res poster fallback.
- **Micro-Controls**:
  - Fine-line progress rail (`.statement-hero-slider__rail-segment`) with 350ms cubic-bezier fill animation (`cubic-bezier(0.16, 1, 0.3, 1)`).
  - Numerical counter: `01 / 03` in monospace formatting.
  - Tactile hairline arrow micro-buttons with glassmorphic backdrop filter (`blur(4px)`).
- **Motion & Accessibility**:
  - Crossfades executed over 800ms cubic-bezier curve.
  - Strict compliance with `prefers-reduced-motion: reduce`.
  - Autoplay pause on hover, keyboard focus, and background tab state (`document.visibilityState === 'hidden'`).

### 2.2 Drop Lookbook & Release Dossier
- **Taxonomy Architecture** (`taxonomy-statement_drop.php`):
  - Monolithic Drop Masthead (`DROP / 001`, `CURRENT RELEASE` / `RELEASE ARCHIVE` pill).
  - Two-Column Spec Sheet: Narrative overview on left, technical release specifications (Status, Edition, Pieces) on right.
  - Lookbook Diptych Grid: High-impact 4:5 vertical product cards side-by-side with secondary hover reveal.
  - Collection Register: Monospace inventory index with title, AUD price, and direct acquisition links.

### 2.3 Product Detail Page (PDP) Commerce Pass
- **65/35 Asymmetric Layout**: 65% width editorial gallery with stacked full-bleed imagery and 35% sticky purchase column.
- **Bespoke S/M/L/XL Swatch Buttons**: Custom button group synchronized with native WooCommerce hidden variation selects for bulletproof checkout compatibility.
- **Accessible Size Guide Modal**:
  - Triggered via prominent `SIZE GUIDE ↗` link in the size selector header.
  - Rendered using semantic HTML5 `<dialog id="statement-size-guide-dialog">` with zero inline JavaScript.
  - Full keyboard accessibility: Closes on `Escape`, backdrop tap, or close button; restores focus to trigger upon dismissal.
  - Precise body measurement table in centimeters (Chest, Waist, Height).
- **Mobile Sticky CTA Bar**:
  - Automatically revealed when the primary purchase button scrolls past viewport top.
  - Contains live product price and full-width Add to Bag trigger respecting iOS safe areas (`env(safe-area-inset-bottom)`).

### 2.4 Typographic Storytelling (About & Contact)
- **100% Pure Typographic Execution**: Zero decorative photography, zero unoptimized stock imagery.
- **About Page**: 4-part numbered narrative (01 Philosophy, 02 Craft, 03 Exclusivity, 04 Responsibility) concluding with the house signature: *CRAFTED. LIMITED. NEVER RESTOCKED.*
- **Contact Page**: Luxury concierge interface highlighting primary correspondence `info@mystatement.store`, Instagram `@statement.au`, and verified Facebook channel.

---

## 3. Responsive Breakpoint & Performance Matrix

| Breakpoint | Target Devices | Hero Height | Grid System | Commerce Column |
| :--- | :--- | :--- | :--- | :--- |
| **Mobile S/M** (<480px) | iPhone SE, iPhone 14/15, Pixel | `82svh` (Portrait Video) | Single Column (100% width) | Sticky Full-Width Bar |
| **Tablet** (481px–1024px) | iPad, iPad Pro, Android Tablets | `75svh` | 2-Column Grid (`1rem` gap) | Stacked Purchase Panel |
| **Desktop** (>1025px) | MacBook, Studio Display, 1080p/4K | `78svh` (`max 54rem`) | 2-Column Diptych (`3.5rem` gap) | Sticky 35% Column |

---

## 4. Verification & Hardening Compliance
- **Zero Live Mutations**: No destructive remote database or filesystem alterations.
- **Clean Separation of Concerns**:
  - Presentation & Styling $\longrightarrow$ `statement-collector-theme` (`0.13.0-rc.20`)
  - Domain Business Logic $\longrightarrow$ `statement-collector-core` (`0.13.0-rc.15`)
  - Integration Test Fixtures $\longrightarrow$ `statement-integration-fixtures` (`0.3.5`)
  - Client Presentation Demo $\longrightarrow$ `statement-client-demo` (`0.2.7`)
