# Drop 001 Launch Runbook — Operational Lifecycle Management

## 1. Overview & Timeline

This runbook defines the end-to-end operational procedure for managing **Statement Drop 001** from pre-launch private access through live public availability, stock depletion, and permanent historical archiving.

```
PHASE 1: Private Access Window (7 Days)
   ↓ (Controlled access gate, return tokens, grant sessions)
PHASE 2: Public LIVE Transition (Remaining Stock)
   ↓ (Catalog & Homepage availability, normal commerce)
PHASE 3: Depletion to SOLD_OUT (Terminal Transition)
   ↓ (Zero stock triggers permanent lock; purchasing disabled)
PHASE 4: Permanent ARCHIVE
   ↓ (Historical presentation on /archive/, permanent provenance)
```

---

## 2. Phase-by-Phase Operational Execution

### Phase 1: Private Access Launch
1. **Preflight Checklist**:
   - Confirm WooPayments in Live Mode with AUD settlements active.
   - Confirm Australia Post flat rate / express shipping methods active.
   - Confirm transactional email delivery (MailPoet or transactional SMTP) active.
   - Verify `_statement_release_state` is `PRIVATE_ACCESS` on all Drop 001 products.
2. **Access Dispatch**:
   - Send private access invitations via email marketing list with direct links to `https://mystatement.store/drop/drop-001/`.
   - Monitor real-time access grant issuance and browser session creation in `wp_statement_access_grants`.
   - Confirm return tokens permit returning patrons seamless re-entry.

---

### Phase 2: Public LIVE Transition (Optional)
If private access window expires with remaining inventory:
1. **Transition Step**:
   - In WP Admin -> Edit Product: Update `_statement_release_state` from `PRIVATE_ACCESS` to `LIVE`.
   - Products instantly appear on Homepage featured loop, `/shop/` catalog, Store API, and search endpoints.
2. **Scarcity Invariant Check**:
   - Confirm no fake urgency timers or restock notices appear.
   - Confirm card typography displays "Crafted. Limited. Never Restocked."

---

### Phase 3: Terminal Transition to SOLD OUT
When inventory reaches `0`:
1. **Automated Stock Depletion**:
   - Core plugin intercepts inventory reduction to `0` and enforces commerce locks.
   - Product state transitions to `SOLD_OUT`.
   - Native Add to Bag UI is completely replaced with luxury "SOLD OUT" badge.
2. **Permanent Invariant**:
   - **Never Restocked**: Even if additional physical units exist later, the edition is closed permanently. No inventory increments or backorders permitted.

---

### Phase 4: Permanent Archive
1. **Transition to Archive**:
   - Set `_statement_release_state` to `ARCHIVED`.
   - Product is removed from active `/shop/` catalog and moved to `/archive/`.
   - Permanent single product page remains accessible for collectors with frozen specifications and acquisition provenance.
   - All past orders preserve permanent frozen purchase metadata.
