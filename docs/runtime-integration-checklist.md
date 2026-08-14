# Statement Collector's Piece — Runtime Integration Checklist

## Overview

Structured integration test checklist for controlled testing on WordPress.com Atomic (`mystatement.store`).

---

## Integration Test Cases

| Test ID | Category | Setup | Action | Expected Result | Severity | Result |
| --- | --- | --- | --- | --- | --- | --- |
| `M13-PA-01` | Bootstrap | Core ZIP uploaded | Activate plugin | Plugin boots clean with 0 warnings | BLOCKER | PENDING |
| `M13-DB-01` | Database | Plugin active | Inspect database | 5 `wp_statement_*` operational tables exist | BLOCKER | PENDING |
| `M13-LC-01` | Domain | Product admin | Edit release state | Forward-only transitions enforced; Woo stock mutation cannot reopen terminal item | BLOCKER | PENDING |
| `M13-PR-01` | Private Access | Unauthorized browser | Access `/drop/private-drop/` | Redirected to token entry gate with `noindex` and `no-store` headers | BLOCKER | PENDING |
| `M13-PR-02` | Private Access | Enter valid access token | Submit access token | POST/303 PRG sets cookie and grants access to private drop | BLOCKER | PENDING |
| `M13-CK-01` | Security | Session active | Inspect browser cookie | Cookie set with `HttpOnly`, `SameSite=Lax`, and `Secure` flags | HIGH | PENDING |
| `M13-CA-01` | Cache Isolation | Browser A authorized | Open incognito Browser B | Browser B NEVER receives cached private HTML from Browser A | BLOCKER | PENDING |
| `M13-API-01` | REST Privacy | Unauthenticated request | Query `/wp-json/wp/v2/product` | `PRIVATE_ACCESS` and `UPCOMING` products excluded | HIGH | PENDING |
| `M13-API-02` | Store API | Unauthenticated request | Query `/wp-json/wc/store/v1/products` | `PRIVATE_ACCESS` products excluded | HIGH | PENDING |
| `M13-CT-01` | Cart / Bag | LIVE product page | Click Add to Bag | Item added to cart; server-rendered Bag count updates | HIGH | PENDING |
| `M13-CT-02` | Cart Integrity | Cart has private item | Session expires / non-LIVE state | Cart Integrity removes item with 1 generic notice | HIGH | PENDING |
| `M13-CO-01` | Checkout | Cart with LIVE product | Proceed to checkout | Order placed successfully using classic checkout flow | BLOCKER | PENDING |
| `M13-CO-02` | Checkout | Private drop item in cart | Enter billing email | Checkout requires billing email to match authorizing grant identity | BLOCKER | PENDING |
| `M13-PV-01` | Provenance | Order placed | Inspect order line item meta | Frozen Statement provenance captured write-once | HIGH | PENDING |
| `M13-EM-01` | Email | Processing order created | Inspect customer email | Transactional email renders frozen provenance; no HTML tags in plain text | HIGH | PENDING |
| `M13-AS-01` | Action Scheduler | Grant nearing expiry | Run Action Scheduler | Reminder email queued and sent; auto-cancellation handles expired cart lines | MEDIUM | PENDING |
| `M13-AR-01` | Archive | Direct product URL | Access `/product/archived-jacket/` | Page returns HTTP 200 public page showing status badge (**ARCHIVED**) | HIGH | PENDING |
| `M13-AR-02` | Archive | Past Drop URL | Access `/drop/past-drop/` | Fully historical Drop renders its `ARCHIVED` products | HIGH | PENDING |
| `M13-RS-01` | Responsive | Mobile/Desktop viewports | View Home/Shop/Product/Cart/Checkout | Layout renders responsively according to Statement design tokens | MEDIUM | PENDING |
