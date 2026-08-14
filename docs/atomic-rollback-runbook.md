# WordPress.com Atomic Integration Emergency Rollback Runbook

## Scope

Emergency operational runbook for Statement Collector's Piece deployment and runtime integration on WordPress.com Atomic (`mystatement.store`).

> [!IMPORTANT]
> **Platform Auto-Activation Notice:** WordPress.com Atomic may automatically activate custom plugins upon upload. Therefore, rollback readiness must be established before uploading any ZIP package.

---

## 1. Plugin Activation Failure / Fatal Rollback

### Trigger Conditions
- PHP Fatal error on plugin activation.
- `wp-admin` becomes inaccessible.
- WooCommerce Admin screens fail to load.
- Unhandled database migration crash during `Statement\Collector\Core\Access\Schema::install()`.

### Rollback Execution Steps
1. **Access WP Admin Plugins Screen:**
   Navigate to `https://mystatement.store/wp-admin/plugins.php` or the WordPress.com Dashboard Plugins screen (`https://wordpress.com/plugins/mystatement.store`).
2. **Deactivate Statement Collector Core:**
   Click **Deactivate** on Statement Collector Core.
3. **Verify Admin Recovery:**
   Confirm `wp-admin`, WooCommerce Admin, and site frontend return to normal operation.
4. **Preserve Error Evidence:**
   Capture error log output or stack traces from WordPress.com Site Health / Error Logs or PHP error display.
5. **DO NOT Edit Live Code:**
   Never patch PHP files directly on the server. Fix locally, add a regression test, package a new release candidate (`rc.X`), and obtain explicit approval before re-uploading.

---

## 2. Theme Activation Failure Rollback

### Trigger Conditions
- Frontend white-screen or 500 Internal Server Error upon Statement theme activation.
- Failure of WooCommerce templates (Cart, Checkout, Single Product) to render.
- Asset loading failures breaking site navigation.

### Rollback Execution Steps
1. **Navigate to Themes Screen:**
   Go to `https://mystatement.store/wp-admin/themes.php` or WordPress.com Dashboard Appearance -> Themes.
2. **Activate Fallback Theme:**
   Activate `Assembler` (or Twenty Twenty-Four / Twenty Twenty-Three).
3. **Verify Homepage Restoration:**
   Confirm Page ID 53 (`Real Home` / Elementor layout at `/`) renders correctly.
4. **Verify Storefront Safety:**
   Confirm default WooCommerce templates render safely.

---

## 3. Checkout / Commerce Breakdown Rollback

### Trigger Conditions
- Customers unable to place orders for `LIVE` products.
- Payment gateway initialization failure on `/checkout/`.
- Cart integrity hook throwing fatal exceptions during checkout line revalidation.

### Rollback Execution Steps
1. **Deactivate Statement Collector Core:**
   Deactivate `statement-collector-core` plugin to revert WooCommerce checkout to standard WooCommerce behavior.
2. **Verify Standard Checkout:**
   Confirm standard WooCommerce checkout functions.
3. **Log Commerce Defect:**
   Log defect in `docs/runtime-integration-log.md` with severity `BLOCKER`.

---

## 4. Private Edge Cache Leak Rollback

### Trigger Conditions
- Non-grant holders able to view or purchase `PRIVATE_ACCESS` products without valid tokens due to edge proxy caching.
- Dynamic grant responses returning HTTP 200 with public cache headers (`Cache-Control: public`).

### Rollback Execution Steps
1. **Purge Platform Cache:**
   Trigger platform cache purge via WordPress.com Hosting Configuration -> Cache -> Clear Cache.
2. **Set Private Access State to UPCOMING:**
   If a Drop is live under private access, transition Drop release state from `PRIVATE_ACCESS` back to `UPCOMING` to enforce fail-closed access control.
3. **Deactivate Core Access Module:**
   If edge caching persists, deactivate Statement Collector Core until cache headers are hardened.
