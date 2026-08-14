# WordPress.com Atomic Rollback Runbook

## Overview

Emergency rollback procedures for runtime issues encountered during integration testing on WordPress.com Atomic.

---

## 1. Core Plugin Activation Failure

If `statement-collector-core` fails during activation or causes fatal PHP errors:

1. Deactivate `statement-collector-core` via WordPress Admin or WP-CLI if accessible.
2. If dashboard is unreachable, rename plugin directory or deactivate via Atomic admin control panel.
3. Preserve error stack traces and PHP logs for diagnostic reporting.
4. **DO NOT** delete database tables (`wp_statement_*`); operational schema contains versioned state.
5. Return to local development repository to fix the defect, increment RC candidate version, and re-verify package.

---

## 2. Theme Activation Failure

If `statement-collector-theme` fails upon activation or causes template rendering errors:

1. Immediately reactivate the previous known-working theme (e.g. `Twenty Twenty-Four`).
2. Preserve legacy Elementor/homepage content and original pages.
3. Collect the failing URL route and error trace.
4. Fix the issue locally in `wp-content/themes/statement-collector-theme`.
5. Re-run local test suite, generate a new RC package, and re-test.

---

## 3. Checkout Flow Failure

If cart or checkout errors occur during testing:

1. Keep the site in Coming Soon / Private state.
2. Verify classic WooCommerce Cart and Checkout page shortcode assignments.
3. Restore previous known-working theme/plugin RC package if necessary.
4. Preserve order IDs, log notices, and checkout console errors for diagnostic logging.
5. Do NOT improvise live PHP/JS patches directly on the server. All fixes flow from local source.

---

## 4. Private Response Cache Leak (LAUNCH-BLOCKING)

If unauthorized visitors receive cached private drop HTML from an edge cache:

1. **IMMEDIATELY** verify site privacy stays enabled (Coming Soon / Private).
2. Deactivate `statement-collector-core` or revert private access routing if necessary to prevent exposure.
3. Capture exact HTTP response headers (`Cache-Control`, `Vary`, `Age`, `X-Cache`, `Set-Cookie`).
4. Reproduce and fix cache control headers locally in `CacheHardening.php` and `PrivateAccessGate.php`.
5. Re-verify locally before requesting explicit approval for re-upload.
