# Statement Visual Sprint 07 — Release Notes & Technical Manifest

**Date**: August 19, 2026
**Theme Candidate Version**: `0.13.0-rc.14`
**Core Plugin Candidate Version**: `0.13.0-rc.13`
**Client Demo Plugin Version**: `0.2.6`
**Child Theme Starter Version**: `0.1.0`
**Status**: PACKAGED, VERIFIED, DETERMINISTIC INTEGRATION CANDIDATE (ZERO LIVE MUTATIONS)

---

## 1. Executive Summary & Client Directives Reconciliation

Visual Sprint 07 delivers the comprehensive implementation of the latest client feedback, real campaign photography, and brand media assets:

1. **Authentic Statement Brand Logo**:
   - Replaced pure text wordmark fallback with authentic transparent PNG logo (`statement-logo.png`, 1473×2041 px source) supporting WordPress `custom_logo` integration.
   - Balanced responsive navigation layout across mobile (320px–430px) and desktop (1024px–1920px).

2. **Cinematic Hero Slider Rebuild**:
   - Rebuilt with 1920×1080 high-fashion campaign photography (`statement-hero-slide-monogram-arch.jpg`, `statement-hero-slide-monogram-golden.jpg`, `statement-hero-slide-hood-arch.jpg`).
   - Fixed desktop cropping completely; set viewport-relative height (`clamp(34rem, 78svh, 54rem)`) and composition-aware focal alignment (`center 20%`, `center 25%`).
   - Integrated mobile 720×1280 9:16 portrait video (`statement-hero-mobile-monogram.mp4`, muted, autoplay, loop, playsinline, metadata preload, poster fallback) with conditional media loading and `prefers-reduced-motion` accessibility support.

3. **Strict 5-Part Homepage Architecture**:
   - Simplified hierarchy into 5 clean modules:
     1. Site Header (with Statement Logo)
     2. Cinematic Hero Showcase
     3. Drop 001 — Monogram Study (Editorial header + 2 side-by-side product cards)
     4. Editorial Drops & Upcoming Drops text-first directory
     5. Minimal Site Footer
   - Completely eliminated redundant "Current Pieces" heading and separate duplicate product grids.
   - Omitted Journal section from navigation menus, mobile drawer, homepage, and footer.

4. **Text-Only About & Contact Pages**:
   - **About Page**: Strictly zero images; centered typography reading layout; client copy with all em-dashes removed.
   - **Contact Page**: Strictly zero images; confirmed email (`info@mystatement.store`), Instagram (`@statement.au`), and configurable Facebook channel (`statement_facebook_url` in Customizer). Removed fake studio hours and phone numbers.

5. **Product Media & Metadata Remediation**:
   - Ingested normalized WebP product suites for Monogram Jacquard Jacket (5 shots) and Panelled Hood Jacket (6 shots).
   - Fixed white hoodie metadata defect (removed defective "Monogram Jacket" title/slug).
   - Added size **XL** (S, M, L, XL) with SKU `STMT-CD-D001-PHJ-XL`.

6. **PDP Conversion Polish & Noticeable Size Guide**:
   - Prominent `SIZE GUIDE ↗` trigger adjacent to variation selector.
   - Sizing modal table supporting S, M, L, XL with CM flat garment measurements.

---

## 2. Packaged Artifacts & Checksums

| Artifact File | Component | Version | Verification |
| :--- | :--- | :--- | :--- |
| `dist/statement-collector-theme-0.13.0-rc.14.zip` | Theme | `0.13.0-rc.14` | **PASS** |
| `dist/statement-collector-core-0.13.0-rc.13.zip` | Core Plugin | `0.13.0-rc.13` | **PASS** |
| `dist/statement-client-demo-0.2.6.zip` | Client Demo | `0.2.6` | **PASS** |
| `dist/statement-collector-child-0.1.0.zip` | Starter Child Theme | `0.1.0` | **PASS** |

---

## 3. Verification & Test Evidence

- **PHP Syntax & Linting**: 120/120 PHP files passed syntax checks without warnings (`node scripts/php-lint.mjs`).
- **Visual Sprint 07 Suite**: 5/5 subtests passed (`tests/visual-sprint-07.test.mjs`).
- **PHP Acceptance Suites**: Passed isolation, HPOS, collision, and security tests.
- **Node Test Matrix**: Full test suite verified.
- **Git State**: Clean working tree on `main` branch.
