# Runtime Integration Log

## Environment

- Site: `https://mystatement.store/`
- Candidate: `0.13.0-rc.2`
- WordPress: `6.7.x` (WordPress.com Atomic)
- WooCommerce: `11.0.1`
- PHP: `8.3.x`
- Updated at: 2026-08-15

---

## Issue Log

| ID | Test ID | Feature | Expected | Actual | Classification | Severity | Local Fix Commit | RC | Retest | Status |
|----|---------|---------|----------|--------|----------------|----------|------------------|----|--------|--------|
| `M13-ISSUE-01` | `M13-PA-01` | Core Plugin Upload Access | WP Admin upload access available via CLI / automated tool | Upload requires manual WordPress.com Dashboard session | PLATFORM | BLOCKER | N/A | rc.1 | Retested | RESOLVED (Manual Upload) |
| `M13-ISSUE-02` | `M13-PA-01` | RC Version Traceability | Packaged plugin header Version equals candidate version (`0.13.0-rc.1`) | `statement-collector-core.php` header contained `Version: 0.1.0`; Atomic reported version 0.1.0 | CODE / PACKAGING | HIGH | Pending local commit | rc.2 | Pending Upload | RESOLVED IN LOCAL RC-2 |
| `M13-CONFIG-01` | `M13-WOO-01` | Store Currency | Store currency configured as `AUD` | Atomic store currency configured as `USD` | CONFIGURATION | MEDIUM | N/A | rc.2 | Pending | OPEN |

*Note: Classifications: CODE, CONFIGURATION, PLATFORM, CONTENT, UNKNOWN. Severities: BLOCKER, HIGH, MEDIUM, LOW.*
