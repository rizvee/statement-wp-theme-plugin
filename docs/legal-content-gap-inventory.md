# Legal & Content Gap Inventory — M15 Launch Readiness

## 1. Overview

This document inventories all required public pages, commercial endpoints, and statutory legal policies for Statement Collector's Piece (`mystatement.store`), classifying what is implemented in code versus what requires business/legal content input before public launch.

---

## 2. Page & Policy Inventory

| Route / Destination | Implementation Status | Content Classification | Required Action |
| --- | --- | --- | --- |
| **Homepage (`/`)** | **IMPLEMENTED** | Editorial / Brand Layout | Populate bespoke editorial hero images & copy in WP Admin |
| **Shop Catalog (`/shop/`)** | **IMPLEMENTED** | Dynamic Product Grid | Fully operational via native WooCommerce & Core queries |
| **Dedicated Archive (`/archive/`)** | **IMPLEMENTED** | Permanent Historical Archive | Fully operational via `page-archive.php` and `PublicApi` |
| **Product Detail Page (`/product/*`)** | **IMPLEMENTED** | Dynamic PDP Composition | Add high-resolution product photography & descriptions |
| **Your Bag (`/cart/`)** | **IMPLEMENTED** | Responsive Cart Table | Fully operational with M8 Cart Integrity |
| **Checkout (`/checkout/`)** | **IMPLEMENTED** | Responsive Checkout Override | Fully operational with M9 Checkout Integrity & WooPayments |
| **My Account (`/my-account/`)** | **IMPLEMENTED** | Account Dashboard & Orders | Fully operational via WooCommerce native endpoints |
| **Order Received (`/checkout/order-received/*`)** | **IMPLEMENTED** | M12 Provenance Presentation | Fully operational |
| **Privacy Policy (`/privacy-policy/`)** | **DRAFT / MISSING** | **BUSINESS / LEGAL REQUIRED** | Publish Australian Privacy Principles (APP) compliant privacy policy covering email encryption and marketing consent |
| **Terms of Service (`/terms/`)** | **DRAFT / MISSING** | **BUSINESS / LEGAL REQUIRED** | Publish commercial terms covering limited editions, scarcity invariant, payment terms, and intellectual property |
| **Refund & Returns Policy (`/refund-policy/`)** | **DRAFT / MISSING** | **BUSINESS / LEGAL REQUIRED** | Publish Australian Consumer Law (ACL) compliant policy specifying collector piece return windows and damaged-goods remediation |
| **Shipping & Delivery (`/shipping/`)** | **DRAFT / MISSING** | **BUSINESS / LEGAL REQUIRED** | Publish Australia Post / MyPost Business delivery timelines, dispatch windows, signature requirements, and transit insurance details |
| **Contact / Concierge (`/contact/`)** | **OPTIONAL / DRAFT** | **BUSINESS CONTENT REQUIRED** | Create simple contact or concierge email point (`concierge@mystatement.store`) |

---

## 3. Mandatory Pre-Launch Legal Actions

1. **Australian Consumer Law Compliance**: All terms must explicitly state statutory consumer guarantees under the Competition and Consumer Act 2010 (Cth).
2. **Scarcity & No-Restock Policy**: Formalize the core business invariant in customer-facing terms: "All collector pieces are strictly limited editions. Once sold out, editions are never restocked or re-manufactured."
3. **Privacy Notice on Private Access**: Ensure privacy policy describes the single-use token and encrypted email identity model used for private access grants.
