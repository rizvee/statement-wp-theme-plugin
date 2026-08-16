# Statement — Production Payment Configuration Input

## Instructions for Operator
Provide merchant onboarding, payment gateway, and bank settlement parameters below prior to activating live charging on WooPayments / Stripe.

---

## 1. WooPayments / Stripe Merchant Details

| Parameter | Specification | Operator Input |
| --- | --- | --- |
| **Merchant Name** | Business identifier in Stripe | `[TBD: Statement]` |
| **Settlement Currency** | Operating bank currency | `AUD` (Australian Dollar) |
| **Bank Account Link** | Australian BSB & Account Number | `[TBD: Connected via Stripe Express]` |
| **Card Brands Enabled** | Visa, Mastercard, AMEX | `[TBD: Visa, Mastercard, AMEX enabled]` |
| **Digital Wallets** | Apple Pay, Google Pay | `[TBD: Yes, enabled for mobile checkout]` |
| **Statement Descriptor** | 22-character text on buyer bank statement | `[TBD: STATEMENT STORE]` |
| **Short Descriptor** | City / phone descriptor | `[TBD: SYDNEY AU]` |
| **Fraud Protection (3D Secure)** | Radar rules | `[TBD: Dynamic 3DS enabled for elevated risk]` |

---

## 2. Gateway Activation Protocol

1. **Pre-Activation**:
   - In WP Admin -> WooCommerce -> Payments: Verify `Statement QA Gateway` is deactivated / uninstalled with fixture tool.
   - Confirm store currency is strictly `AUD`.
2. **Onboarding**:
   - Complete Stripe KYC verification in WooPayments dashboard.
   - Verify payouts are scheduled to confirmed Australian bank account.
3. **Live Verification**:
   - Perform 1 controlled test purchase with a real Australian credit card ($1.00 AUD test product or genuine cart item).
   - Confirm successful order creation, provenance line item capture, and receipt generation.
   - Issue immediate full refund via WooCommerce Orders / WooPayments dashboard.
