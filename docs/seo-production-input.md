# Statement — Production SEO Configuration Input

## Instructions for Operator
Provide canonical brand names, homepage metadata, and indexing preferences below prior to public search index launch.

---

## 1. Global Brand & Site Metadata

| Parameter | Specification | Operator Input |
| --- | --- | --- |
| **Site Title** | WordPress Site Title | `Statement` |
| **Tagline** | Brand principle / tagline | `Crafted. Limited. Never Restocked.` |
| **Homepage `<title>`** | Title tag for root `/` | `[TBD: Statement — Crafted. Limited. Never Restocked.]` |
| **Homepage Meta Description** | Search engine snippet (150-160 chars) | `[TBD: Architectural luxury apparel in strictly limited editions. One release, limited availability, never restocked.]` |
| **Shop Page `<title>`** | Shop catalog title | `Shop — Statement` |
| **Archive Page `<title>`** | Permanent archive title | `Archive — Statement` |
| **Brand Favicon / Site Icon** | 512 × 512 px square icon | `[TBD: statement-icon.png]` |
| **Social Share Image (OG Image)** | 1200 × 630 px OpenGraph image | `[TBD: og-statement-brand.webp]` |

---

## 2. Search Engine Indexing Protocol

1. **Pre-Launch Private Phase**:
   - In WP Admin -> Settings -> Reading: "Discourage search engines from indexing this site" is checked while in staging/testing.
   - Private Access Drop routes (`/drop/*`) automatically emit `<meta name="robots" content="noindex, nofollow" />`.
2. **Public Launch Transition**:
   - In WP Admin -> Settings -> Reading: Uncheck "Discourage search engines from indexing this site".
   - Public routes (`/`, `/shop/`, `/archive/`, public PDPs) are rendered indexed and canonicalized.
   - Private routes remain strictly protected by Core plugin `noindex` headers.
