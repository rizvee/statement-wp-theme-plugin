# Final Fixture Cleanup Plan — M15 Launch Readiness

## 1. Overview & Purpose

The `statement-integration-fixtures` plugin and its associated seed dataset are temporary tools created strictly for M13 integration testing and runtime verification on WordPress.com Atomic.

Before production launch, all synthetic test entities, test database records, and the fixture plugin itself must be cleanly removed to ensure zero test footprint, zero security leakage, and zero operational baggage on the live storefront.

---

## 2. Test Entity & Database Inventory

### A. WordPress & WooCommerce Entities

| Entity Type | Identifier / Meta | Name / Details | Safe Action |
| --- | --- | --- | --- |
| **Test Product (Private Access)** | SKU: `TEST-PD01-PAJ` | `TEST — Private Access Jacket` (`_statement_fixture = 1`) | Trash & Delete permanently |
| **Test Product (Terminal)** | SKU: `TEST-TJ01-ARC` | `TEST — Terminal Jacket` (`_statement_fixture = 1`) | Trash & Delete permanently |
| **Test Taxonomy Term** | `statement_drop`: `test-private-drop-01` | Slug: `test-private-drop-01` (or `test-live-drop-01`) | Delete taxonomy term & term meta |
| **Controlled QA Order** | `_statement_is_qa_order = 'yes'` | Option: `statement_qa_last_order_id` | Retain during staging QA; Trash before public launch |

> [!IMPORTANT]
> **Product 213 & Production Content Preservation**: Product ID 213 represents the live Monogram Jacquard Jacket client presentation (`STMT-CD-D001-01`). It is classified as `CLIENT_DEMO` / `PRODUCTION` and MUST NOT be deleted. Only entities with `_statement_fixture = 1` or `TEST-*` SKUs are targeted.

### B. M10 Private Access Operational Database Tables (Derived from Core Schema)

| Table Name | Test Data Rows | Cleanup Method |
| --- | --- | --- |
| `wp_statement_access_grants` | QA grant records (`drop_term_id = 1376` or test drop) | `DELETE FROM wp_statement_access_grants WHERE drop_term_id = [test_drop_id]` |
| `wp_statement_access_sessions` | QA session rows for test grant IDs | `DELETE FROM wp_statement_access_sessions WHERE grant_id IN (...)` |
| `wp_statement_access_tokens` | QA return & unsubscribe tokens | `DELETE FROM wp_statement_access_tokens WHERE grant_id IN (...)` |
| `wp_statement_access_rate_limits` | QA test rate limit hashes | `DELETE FROM wp_statement_access_rate_limits WHERE drop_term_id = [test_drop_id]` |
| `wp_statement_consent_events` | QA consent and withdrawal events | `DELETE FROM wp_statement_consent_events WHERE source = 'qa_test'` |

### C. Options & Action Scheduler

| Component | Identifier | Details |
| --- | --- | --- |
| **Fixture Manifest Option** | `statement_fixture_manifest`, `statement_private_integration_fixture_manifest` | Delete options |
| **QA Last Order Option** | `statement_qa_last_order_id` | Delete option |
| **Action Scheduler Hooks** | `statement_private_access_reminder_action` | Unschedule all actions matching test grant IDs |

### D. Fixture Plugin Code & Gateway

| Component | Class / Hook | Action |
| --- | --- | --- |
| **Fixture Plugin** | `statement-integration-fixtures` | Deactivate in WP Admin -> Delete plugin |
| **Statement QA Gateway** | `StatementQaGateway` (`statement_qa_gateway`) | Automatically deactivated with plugin removal |

---

## 3. Safe Execution Sequence

 ```
 1. Pre-Cleanup Verification Gate:
    - BACKUP_REQUIRED_BEFORE_CLEANUP = YES
    - Open Jetpack -> Backup in WordPress.com Dashboard
    - Confirm an automatic point-in-time restore point exists post-dating latest deployment/QA
    - Required evidence at execution time: successful restore point timestamp later than current deployed production candidate
    - Record backup timestamp and rollback readiness
    ↓
 2. Staging Audit Verification (Verify Order & Provenance Metadata)
    ↓
 3. Execute Fixture Administrative Cleanup (Dry Run first -> Purge Test Records)
    ↓
 4. Permanently Delete Test Products & Terms in WP Admin
    ↓
 5. Deactivate statement-integration-fixtures Plugin
    ↓
 6. Smoke Test Frontend Routes (Home, Shop, Archive, PDP, Cart, Checkout, My Account)
    ↓
 7. Uninstall statement-integration-fixtures Plugin
    ↓
 8. Final Clean Storefront Verification
 ```

---

## 4. Controlled QA Order Retention Decision

- **During Pre-Launch Verification**: The controlled QA order created with `statement_qa_gateway` remains in the database temporarily as verifiable evidence for M10 Order Audit (`_statement_grant_id`, `_statement_authorized_at`) and M12 Frozen Purchase Provenance (`_statement_provenance_version`, `_statement_product_id_at_purchase`, etc.).
- **Prior to Live Production Launch**: The controlled QA order is permanently trashed and deleted in WooCommerce Orders, and option `statement_qa_last_order_id` is purged.

---

## 5. Production Isolation Guarantee

The Core plugin (`statement-collector-core`) and Theme (`statement-collector-theme`) maintain zero runtime dependencies on `statement-integration-fixtures`. When the fixture plugin is inactive:
- `StatementQaGateway` is completely absent from checkout and WooCommerce settings.
- No QA test action hooks or admin menus exist.
- Database operational tables function purely for genuine customer private access drops.
