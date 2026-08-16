# Statement — Production Legal Content Input Specification

## Instructions for Operator
Provide authentic corporate, statutory, and operational parameters below to generate statutory Australian Consumer Law compliant legal policies for live storefront deployment.

---

## 1. Legal Entity & Corporate Information

| Parameter | Required Specification | Operator Input |
| --- | --- | --- |
| **Legal Business Name** | Registered entity name | `[TBD: e.g. Statement Pty Ltd]` |
| **Australian Business Number (ABN)** | 11-digit ABN | `[TBD: XX XXX XXX XXX]` |
| **Trading Name** | Public brand identifier | `Statement` |
| **Registered Business Address** | Physical / official address | `[TBD: Street, Suburb, State, Postcode, Australia]` |
| **Customer Support Email** | Official inquiries email | `[TBD: concierge@mystatement.store]` |
| **Privacy Inquiries Contact** | Privacy officer email/contact | `[TBD: privacy@mystatement.store]` |
| **Governing Jurisdiction** | State / Territory | `[TBD: New South Wales, Australia / Victoria, Australia]` |

---

## 2. Statutory Policy Parameters

### A. Refund & Returns Policy (Australian Consumer Law Compliant)
- **Change of Mind Policy**: `[TBD: Allowed within 14 days in original unworn condition with tags / Final Sale due to limited edition nature subject to ACL guarantees]`.
- **Faulty / Damaged Goods**: Australian Consumer Law non-excludable guarantees apply (repair, replacement, or full refund).
- **Return Shipping Cost**: `[TBD: Paid by customer for change of mind / Complimentary prepaid label provided by Statement]`.
- **Return Address**: `[TBD: Dedicated Returns Facility Address]`.

### B. Terms of Service
- **Limited Edition Scarcity**: All pieces are limited releases, never restocked once sold out.
- **Order Acceptance**: Orders subject to verification and stock confirmation prior to dispatch.
- **Pricing & Currency**: All transactions processed in Australian Dollars (AUD) including GST where applicable.

### C. Privacy Policy (Australian Privacy Principles)
- **Data Collected**: Name, email, shipping address, billing address, IP address (hashed for rate limiting).
- **Payment Processing**: Zero card details stored; processed directly by Stripe / WooPayments.
- **Private Access Emails**: Transactional access links and return tokens; opt-out consent respected.
