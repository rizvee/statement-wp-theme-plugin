# M16 — Production Content Layer Architecture & Content Map

## 1. Executive Summary

Milestone M16 defines the operational implementation and template mapping for genuine brand editorial content following fixture cleanup. All content slots are structured around the core brand principle:

> **Statement** — *Crafted. Limited. Never Restocked.*

---

## 2. Storefront Content Slot Mapping

| Route / Slot | Content Component | Description / Specification | Source of Content |
| --- | --- | --- | --- |
| **Root `/` Hero** | Drop Debut Banner | Full-width 4:5 portrait hero visual, Drop title, editorial subhead, and CTA button. | `front-page.php` (`docs/drop-001-production-input.md`) |
| **Root `/` Showcase** | Active LIVE Pieces Grid | Bounded 4-product loop presenting available pieces in current active Drop. | WooCommerce Catalog (`statement_drop`) |
| **Root `/` Principle** | Brand Manifesto Section | Editorial paragraph establishing scarcity, bespoke tailoring, and zero restocks. | Theme Template (`front-page.php`) |
| **`/shop/` Loop** | Active Catalog Archive | Clean catalog grid: LIVE pieces first, SOLD OUT pieces second. | WooCommerce Catalog (`is_shop()`) |
| **`/archive/`** | Historical Archive Intro | Editorial introduction to the permanent archive with historical drop records. | `page-archive.php` |
| **Single PDP** | Editorial Craft Tabs | Short description, fabrication specs, sizing guidance, and edition provenance. | Single Product Override (`docs/drop-001-production-input.md`) |
| **Footer Navigation** | Legal & Policy Link Map | Direct links to Terms of Service, Refund Policy, Privacy Policy, Shipping. | `footer.php` (`docs/legal-content-input.md`) |
| **About / Contact** | Concierge Inquiries | Minimalist editorial contact page with concierge support email. | WordPress Page (`concierge@mystatement.store`) |

---

## 3. Scarcity & Editorial Tone Guidelines

1. **Zero Fake Urgency**: Never include countdown clocks, "Only X Left" badges, or artificial restock alert forms.
2. **Permanent Record**: Archived products remain permanently viewable on `/product/[slug]/` and `/archive/` with Add to Bag disabled.
3. **Statutory Clarity**: All refund and consumer terms must strictly conform to Australian Consumer Law without deceptive exclusion clauses.
