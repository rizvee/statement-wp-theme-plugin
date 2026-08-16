# Technical SEO Launch Checklist — M15 Launch Readiness

## 1. Overview

Statement uses standard WordPress and WooCommerce native facilities for technical SEO. To maintain maximum performance, zero asset bloat, and complete control over scarce product exposure, no heavyweight third-party SEO plugins (e.g. Yoast, RankMath, All-in-One SEO) should be installed.

---

## 2. Technical Architecture & Invariants

### A. Document Title Hierarchy

- **Implementation**: Theme supports native `add_theme_support( 'title-tag' )`.
- **Homepage**: `Statement Collector's Piece — Crafted. Limited. Never Restocked.`
- **Shop**: `Shop — Statement Collector's Piece`
- **Drop**: `{Drop Name} — Statement Collector's Piece`
- **Product**: `{Piece Name} — Statement Collector's Piece`
- **Archive**: `Archive — Statement Collector's Piece`

### B. Semantic Heading Structure

- **Single `<h1>` per Page**:
  - Homepage: Brand / Hero title or visually hidden primary `<h1>`
  - Catalog / Shop: `<h1>SHOP</h1>` or `<h1>{Drop Name}</h1>`
  - Single Product: `<h1 class="statement-product__title">{Piece Name}</h1>`
  - Cart / Bag: `<h1>YOUR BAG</h1>`
  - Checkout: `<h1>CHECKOUT</h1>`
  - Archive: `<h1>Archive</h1>`
- **Subheadings (`<h2>` - `<h3>`)**: Strictly hierarchical for product cards, summary sections, and order details.

### C. Canonical URLs & Indexing Boundaries

| Route Type | Canonical URL Rule | Indexing Robots Directive |
| --- | --- | --- |
| **Homepage** | `https://mystatement.store/` | `index, follow` |
| **Shop Catalog** | `https://mystatement.store/shop/` | `index, follow` |
| **Active Drop** | `https://mystatement.store/drop/{slug}/` | `index, follow` |
| **Public LIVE Piece** | `https://mystatement.store/product/{slug}/` | `index, follow` |
| **SOLD OUT / ARCHIVED Piece** | `https://mystatement.store/product/{slug}/` | `index, follow` (Permanent viewability preserved) |
| **Dedicated Archive** | `https://mystatement.store/archive/` | `index, follow` |
| **Private Access Gate** | `https://mystatement.store/drop/{private-slug}/` | `noindex, nofollow, noarchive` (Enforced via HTTP header & meta) |
| **Unauthorized Private PDP** | `https://mystatement.store/product/{private-slug}/` | `HTTP 404` (Clean non-indexed error page) |
| **Cart / Checkout / Account** | WooCommerce standard paths | `noindex, follow` (WooCommerce default) |

### D. Structured Data (Schema.org)

- **Organization / WebSite**: Standard WordPress schema.
- **Product Schema**: Native WooCommerce JSON-LD output on single product pages.
  - **LIVE Products**: `ItemAvailability: InStock` or `LimitedAvailability`.
  - **SOLD OUT / ARCHIVED Products**: `ItemAvailability: OutOfStock` (Enforced via Core structured data filter).
  - **Private Access Products**: Suppressed completely until authorized.

### E. Media & Image Optimization

- All product images enforce standard 4:5 or 1:1 aspect ratios with CSS `object-fit: cover`.
- Descriptive `alt` attributes derived from product title and edition metadata.
- Native `loading="lazy"` on all off-screen catalog images.

---

## 3. Launch Verification Tasks

- [ ] Verify `noindex, nofollow` HTTP headers on Private Access Drop gates.
- [ ] Verify private products are absent from Google Search Console sitemaps.
- [ ] Verify public Shop and Archive URLs return HTTP 200 with valid canonical links.
- [ ] Verify single product structured data passes Google Rich Results test.
- [ ] Confirm robots.txt allows public crawling while disallowing `/wp-admin/` and cart/checkout endpoints.
