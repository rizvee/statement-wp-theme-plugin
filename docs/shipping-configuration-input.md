# Statement — Production Shipping Configuration Input

## Instructions for Operator
Provide actual carrier account, shipping rates, and fulfillment parameters below to configure WooCommerce Shipping Zones on Atomic.

---

## 1. Australia Domestic Shipping

| Parameter | Recommended Standard | Operator Input |
| --- | --- | --- |
| **Origin Postcode / Suburb** | Dispatch location | `[TBD: Postcode, State]` |
| **Carrier / Service** | Australia Post / MyPost Business | `[TBD: Australia Post Parcel Post]` |
| **Standard Domestic Rate (AUD)** | Flat rate charge | `[TBD: e.g. $15.00 AUD]` |
| **Express Domestic Rate (AUD)** | Australia Post Express Post | `[TBD: e.g. $25.00 AUD]` |
| **Complimentary Shipping Threshold** | Free standard shipping above | `[TBD: e.g. Free over $500 AUD / No free threshold]` |
| **Estimated Handling Time** | Order processing window | `[TBD: 1-2 Business Days]` |
| **Estimated Delivery Window** | Metro / Regional transit | `[TBD: 2-5 Business Days (Standard), 1-2 Days (Express)]` |
| **Signature on Delivery** | Required for luxury apparel | `[TBD: Yes / Recommended for high-value orders]` |

---

## 2. International Shipping (If Enabled at Launch)

| Parameter | Specification | Operator Input |
| --- | --- | --- |
| **International Launch Status** | Active at Drop 001? | `[TBD: No (Australia Only initially) / Yes]` |
| **Supported Countries** | Allowed destination list | `[TBD: New Zealand, US, UK, EU / None]` |
| **International Flat Rate** | Standard air shipping charge | `[TBD: AUD Amount]` |
| **Duties & Taxes Notice** | DDU / DDP terms | `[TBD: Recipient responsible for local import duties and taxes]` |

---

## 3. WooCommerce Configuration Checklist

- [ ] In WooCommerce -> Settings -> Shipping -> Shipping Zones: Add "Australia".
- [ ] Add Flat Rate method named "Standard Delivery (Australia Post)".
- [ ] Add Flat Rate method named "Express Delivery (Australia Post)".
- [ ] Enable shipment tracking numbers on WooCommerce customer completion emails.
