# Shipping Launch Readiness — Australia Post / MyPost Business

## 1. Shipping Strategy & Architecture

Statement fulfills scarce, luxury physical collector pieces domestically within Australia and internationally as determined by release strategy.

- **Primary Carrier**: Australia Post / **MyPost Business**.
- **Domestic Baseline**: Australia Standard Parcel Post & Australia Express Post.
- **Handling**: Signature on Delivery & Transit Cover (Insurance) for high-value limited editions.
- **Packaging**: Custom archival packaging with tamper-evident seals.

---

## 2. Integration Pathways

### Pathway A: Native WooCommerce Shipping Zones + Manual MyPost Business Label Generation (Recommended for Initial Launch)

- **Pros**: Zero third-party plugin bloat, zero credential exposure in WordPress DB, 100% reliable, direct commercial discount tracking in MyPost Business portal.
- **Setup**:
  1. WooCommerce -> Settings -> Shipping -> Shipping Zones:
     - **Zone: Australia** (All states / postcodes)
       - **Method 1**: Standard Insured Delivery (Flat rate or Free Shipping threshold)
       - **Method 2**: Express Signature Delivery (Flat rate calculation)
  2. Order Fulfillment Workflow:
     - New order arrives in WooCommerce with customer address.
     - Operator exports/inputs address into MyPost Business portal.
     - Label generated, payment charged directly on business account.
     - Tracking number attached to WooCommerce Order -> Order marked `Completed`.
     - Transactional dispatch email with tracking link triggered automatically.

### Pathway B: Automated API Integration (Future Milestone / High Volume)

- **Potential Solution**: Official Australia Post / ReachShip WooCommerce integration plugin.
- **Considerations**: Requires API Key, Secret, and Merchant Account Number stored in WordPress database. Only enable when order volume exceeds manual processing capacity.

---

## 3. Pre-Launch Configuration Checklist

- [ ] Configure Australia Shipping Zone in WooCommerce Settings.
- [ ] Set flat rates for Standard Delivery (e.g. AUD $15.00) and Express Delivery (e.g. AUD $25.00).
- [ ] Configure free domestic delivery for high-value orders if desired.
- [ ] Verify checkout shipping calculation and tax calculation (10% GST on domestic shipping).
- [ ] Conduct test order to verify shipping address fields (Address 1, Address 2, Suburb/City, State, Postcode, Country: Australia).
- [ ] Verify order completion email template incorporates shipment tracking link (`https://auspost.com.au/mypost/track/#/details/{tracking_number}`).
- [ ] Document emergency fulfillment fallback process (over-the-counter Post Office lodgement).
