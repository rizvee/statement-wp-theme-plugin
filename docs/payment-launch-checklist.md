# Payment Launch Checklist — WooPayments Configuration

## 1. Overview & Provider Architecture

Statement uses **WooPayments** (powered by Stripe) as its primary payment processor for all live commercial transactions.

- **Store Base Currency**: **AUD (Australian Dollar)**
- **Supported Payment Methods**:
  - Credit / Debit Cards (Visa, Mastercard, American Express)
  - Apple Pay (Mobile Safari / macOS)
  - Google Pay (Chrome / Android)
- **Scarcity & Stock Guarantee**:
  - Inventory reduced **exactly once** upon payment completion.
  - M12 Frozen Purchase Provenance captured permanently on line item creation.
- **Zero Test Footprint**:
  - `StatementQaGateway` (`statement_qa_gateway`) is purely a temporary fixture tool and **must be inactive/removed** before production transactions commence.

---

## 2. Live Transition Matrix

| Step | Action | Verification |
| --- | --- | --- |
| **1. Currency Configuration** | WooCommerce -> Settings -> General -> Currency: `Australian dollar ($)` | Currency symbol `$` / `AUD` displayed on catalog, cart, checkout |
| **2. WooPayments Account Connection** | Connect verified Australian business Stripe account via WooPayments | Webhook endpoints active, payouts enabled |
| **3. Test Mode Validation** | Run test transactions using Stripe 4242 test cards | Order placed in `processing`, stock decremented by 1, email dispatched |
| **4. Express Checkout (Apple / Google Pay)** | Enable Apple Pay domain verification in WooPayments settings | One-touch pay button active on mobile checkout |
| **5. Live Mode Activation** | Toggle WooPayments from Test Mode to Live Mode | Live API keys active, real transactions enabled |
| **6. Live Smoke Transaction** | Execute 1 real small-value transaction ($1.00 or test SKU) with real card | Charge captured in Stripe/WooPayments dashboard, immediate refund processed |
| **7. Refund Flow Verification** | Process full refund in WooCommerce Order dashboard | Refund reflected in Stripe/bank statement within standard processing window |
| **8. Gateway Cleanup** | Verify Statement QA Gateway is absent from checkout | Only live payment methods rendered on `/checkout/` |

---

## 3. Webhook & Asynchronous Resilience

- Ensure WooCommerce Webhook listener `/wp-json/wc/v3/` or WooPayments internal webhook listener receives charge events without hosting firewall blocks.
- Configure automatic retry on failed payment notifications.
- Verify checkout handles 3D Secure / SCA authentication seamlessly without losing cart context.
