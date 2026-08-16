# Transactional & Private Access Email Readiness — M15

## 1. System Architecture

Statement operates two distinct email layers:
1. **Core Domain & Private Access Transactional Emails**: Managed by `statement-collector-core` (Private Access Grants, Single-Use Return Tokens, Action Scheduler Reminders, Consent Withdrawals).
2. **WooCommerce Commerce Transactional Emails**: Managed by WooCommerce with M12 Collector Provenance integration (Order Processing, Order Completed, Refunds, Invoices).

---

## 2. Complete Email Matrix

| Email Type | Sender Trigger | Recipient | Subject / Purpose | Scarcity / Privacy Rules |
| --- | --- | --- | --- | --- |
| **Private Access Granted** | `EmailAccessGranted::trigger()` | Granted Collector | `Your Private Access to {Drop Name}` | Contains single-use return link; zero public product facts before authorization |
| **Private Access Return Link** | Self-serve return gate request | Granted Collector | `Your Return Access Link for {Drop Name}` | Single-use token created in `wp_statement_access_tokens`; valid until drop closes or expires |
| **Private Access Reminder** | Action Scheduler (`statement_private_access_send_reminder`) | Consented Collector | `Reminder: {Drop Name} Access Closing Soon` | Sent only if active marketing consent exists; auto-cancelled upon Add to Bag |
| **Marketing Consent Withdrawal** | Action Scheduler / Token link | Collector | `Subscription Preferences Updated` | Confirms marketing opt-out while preserving underlying access grant |
| **Order Processing (Thank You)** | WooCommerce Payment Complete | Customer | `Your Statement Order #{order_id} is Confirmed` | Enriched with M12 frozen provenance table (Edition, Drop, Release State); no internal tokens or secrets |
| **Order Completed (Shipped)** | WooCommerce Order Status -> Completed | Customer | `Your Statement Collector's Piece Has Shipped` | Includes Australia Post tracking link and delivery details |
| **Order Refunded** | WooCommerce Order Status -> Refunded | Customer | `Your Statement Order #{order_id} Has Been Refunded` | Standard transactional refund notice |
| **New Order (Admin Notification)** | WooCommerce Payment Complete | Store Operator | `[Statement] New Order #{order_id}` | Operator fulfillment notice |

---

## 3. Email Privacy & Unsubscribe Separation Invariant

1. **Transactional Boundary**: Access links and Order confirmation emails are strictly operational/transactional and do NOT require marketing consent.
2. **Marketing Boundary**: Reminder emails require explicit, affirmative marketing consent recorded in `wp_statement_marketing_consents`.
3. **Unsubscribe Isolation**: Clicking unsubscribe withdraws marketing consent immediately without revoking private drop access or suppressing order confirmation receipts.
4. **Secret Scrubbing**: Emails NEVER contain database hashes, grant IDs, secret tokens, or internal meta keys. Return tokens use one-time cryptographic values consumed on first use.

---

## 4. Pre-Launch Deliverability Checklist

- [ ] Configure custom domain sender: `concierge@mystatement.store` or `orders@mystatement.store`.
- [ ] Verify SPF, DKIM, and DMARC DNS records for `mystatement.store` to guarantee inbox placement.
- [ ] Test WooCommerce transactional emails with M12 provenance table rendering in dark and light email clients.
- [ ] Test Private Access return link token generation and single-use invalidation.
- [ ] Test marketing unsubscribe flow and confirm database consent withdrawal logging.
