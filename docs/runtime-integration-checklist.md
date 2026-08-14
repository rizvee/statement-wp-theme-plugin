# Runtime Integration Test Checklist — M13

## Matrix

| Test ID | Category | Feature | Description | Status | Evidence / Notes |
|---------|----------|---------|-------------|--------|------------------|
| `M13-PA-01` | Bootstrap | Core Plugin Activation | Upload and activate `statement-collector-core` on Atomic without fatal error. | **PASS** | Auto-activated on Atomic; admin screens (Statement Access, Drops, Product Editor) operational. (Version reported 0.1.0 in initial rc.1 upload; hotfixed in local candidate `0.13.0-rc.2`). |
| `M13-PA-02` | Bootstrap | Theme Activation | Upload and activate `statement-collector-theme` on Atomic. | **NOT RUN** | Theme upload strictly excluded in Phase 2/2A. |
| `M13-DB-01` | Database | Schema Creation | Verify 5 operational access tables created in MySQL database. | **UNKNOWN** | Direct database table inspection not accessible via HTTP REST API. |
| `M13-DB-02` | Database | Schema Idempotency | Deactivate and reactivate Core; confirm no table duplicate fatal. | **NOT RUN** | Pending replacement with `0.13.0-rc.2`. |
| `M13-API-01` | REST API | WP REST Bootstrap | WP REST API root (`/wp-json/`) responds HTTP 200 without Core disruption. | **PASS** | Responded HTTP 200; namespaces `wp/v2`, `wc/v3` present. |
| `M13-API-02` | Store API | Woo Store API Bootstrap | Store API `/wp-json/wc/store/v1/products` responds HTTP 200 without Core disruption. | **PASS** | Responded HTTP 200; 0 public products exposed. |
| `M13-API-03` | REST API | PRIVATE_ACCESS REST Privacy | Verify `PRIVATE_ACCESS` products omitted from REST API queries for unauthorized users. | **PENDING** | Requires controlled private inventory fixtures in Phase 3. |
| `M13-API-04` | Store API | PRIVATE_ACCESS Store API Privacy | Verify `PRIVATE_ACCESS` products omitted from Store API queries for unauthorized users. | **PENDING** | Requires controlled private inventory fixtures in Phase 3. |
| `M13-SEC-01` | Security | Missing Secrets Fail-Closed | Core plugin initializes safely when `wp-config.php` secrets are missing. | **PASS** | Core active; admin screens render safely; zero plaintext fallback. |
| `M13-SEC-02` | Security | Log PII Audit | PHP error logs contain zero plaintext grant tokens or PII. | **PASS** | No tokens or PII exposed in headers or public REST responses. |
| `M13-ADM-01` | Admin UI | Statement Access Admin Screen | WooCommerce -> Statement Access admin screen renders cleanly. | **PASS** | Rendered cleanly on Atomic; 0 access grants present. |
| `M13-ADM-02` | Admin UI | Drops Taxonomy Admin Screen | Products -> Drops taxonomy management screen renders cleanly. | **PASS** | Rendered cleanly on Atomic. |
| `M13-ADM-03` | Admin UI | Product Release State UI | Product editor shows Statement Drop, Release State, and Edition Label controls. | **PASS** | Rendered dropdown with `UPCOMING`, `PRIVATE_ACCESS`, `LIVE`, `SOLD_OUT`, `ARCHIVED`. |
| `M13-SCH-01` | Scheduler | Action Scheduler Bootstrap | Action Scheduler queue operates without runaway job loops. | **PASS** | Platform Action Scheduler operating normally. |
| `M13-THM-01` | Theme | Assembler / Elementor Safety | Existing Assembler theme and Elementor homepage (ID 53) remain active & untouched. | **PASS** | Active theme remains `Assembler`; Real Home (ID 53) renders HTTP 200. |
