import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const pluginRoot = resolve(root, 'wp-content', 'plugins', 'statement-collector-core');

test('M12 focused Order domain files exist', () => {
  const files = [
    resolve(pluginRoot, 'src', 'Order', 'Provenance.php'),
    resolve(pluginRoot, 'src', 'Order', 'Completion.php'),
    resolve(pluginRoot, 'src', 'Order', 'AdminOrderView.php'),
    resolve(pluginRoot, 'src', 'Order', 'CustomerOrderView.php'),
    resolve(pluginRoot, 'src', 'Order', 'EmailIntegration.php'),
  ];

  for (const f of files) {
    assert.ok(existsSync(f), `File must exist: ${f}`);
  }
});

test('Provenance captures immutable snapshot metadata during order line item creation', () => {
  const provPhp = readFileSync(resolve(pluginRoot, 'src', 'Order', 'Provenance.php'), 'utf8');
  assert.ok(provPhp.includes('woocommerce_checkout_create_order_line_item'), 'Provenance.php must hook into order line item creation.');
  assert.ok(provPhp.includes('_statement_provenance_version'), 'Provenance.php must define schema version key.');
  assert.ok(provPhp.includes('_statement_product_id_at_purchase'), 'Provenance.php must capture product ID at purchase.');
  assert.ok(provPhp.includes('_statement_drop_name_at_purchase'), 'Provenance.php must capture Drop name at purchase.');
  assert.ok(provPhp.includes('_statement_edition_label_at_purchase'), 'Provenance.php must capture edition label at purchase.');
  assert.ok(provPhp.includes('is_captured'), 'Provenance.php must enforce write-once idempotency check.');
  assert.ok(provPhp.includes('get_snapshot_status'), 'Provenance.php must implement snapshot status validation.');
  assert.ok(provPhp.includes('is_valid'), 'Provenance.php must implement completeness check.');
});

test('Completion helper evaluates order status deterministically without certificate generation', () => {
  const compPhp = readFileSync(resolve(pluginRoot, 'src', 'Order', 'Completion.php'), 'utf8');
  assert.ok(compPhp.includes('is_commercially_completed'), 'Completion.php must define is_commercially_completed.');
  assert.ok(compPhp.includes('processing'), 'Completion.php must consider processing completed.');
  assert.ok(compPhp.includes('completed'), 'Completion.php must consider completed completed.');
  assert.ok(!compPhp.includes('certificate'), 'Completion.php must not implement certificate generation.');
});

test('AdminOrderView labels capture timestamp and handles invalid snapshots safely', () => {
  const adminPhp = readFileSync(resolve(pluginRoot, 'src', 'Order', 'AdminOrderView.php'), 'utf8');
  assert.ok(adminPhp.includes('Captured At:'), 'AdminOrderView.php must label capture timestamp as Captured At.');
  assert.ok(adminPhp.includes('Incomplete Snapshot'), 'AdminOrderView.php must handle incomplete snapshots safely.');
});

test('CustomerOrderView provides status-aware headers without false ownership claims', () => {
  const custPhp = readFileSync(resolve(pluginRoot, 'src', 'Order', 'CustomerOrderView.php'), 'utf8');
  assert.ok(custPhp.includes('Your piece has been secured.'), 'CustomerOrderView.php must state secured only for completed orders.');
  assert.ok(custPhp.includes('Payment Not Completed'), 'CustomerOrderView.php must handle failed orders status-aware.');
  assert.ok(custPhp.includes('Order Cancelled'), 'CustomerOrderView.php must handle cancelled orders status-aware.');
  assert.ok(custPhp.includes('Order Refunded'), 'CustomerOrderView.php must handle refunded orders status-aware.');
});

test('EmailIntegration handles plain text and HTML branches safely', () => {
  const emailPhp = readFileSync(resolve(pluginRoot, 'src', 'Order', 'EmailIntegration.php'), 'utf8');
  assert.ok(emailPhp.includes('render_email_item_provenance'), 'EmailIntegration.php must define rendering callback.');
  assert.ok(emailPhp.includes('if ( $plain )'), 'EmailIntegration.php must check plain text flag.');
  assert.ok(emailPhp.includes('is_valid'), 'EmailIntegration.php must check snapshot validity.');
});

test('Coexistence with M10 Private Access audit metadata', () => {
  const auditPhp = readFileSync(resolve(pluginRoot, 'src', 'Access', 'OrderAudit.php'), 'utf8');
  assert.ok(auditPhp.includes('_statement_private_access_grant_id'), 'OrderAudit.php must retain M10 grant ID meta key.');
  assert.ok(auditPhp.includes('_statement_private_access_authorized_at'), 'OrderAudit.php must retain M10 authorized at meta key.');

  const provPhp = readFileSync(resolve(pluginRoot, 'src', 'Order', 'Provenance.php'), 'utf8');
  assert.ok(!provPhp.includes('_statement_private_access_grant_id'), 'Provenance.php must keep M12 provenance separate from M10 audit.');
});

test('Data minimization: Provenance metadata contains no customer PII or secret tokens', () => {
  const provPhp = readFileSync(resolve(pluginRoot, 'src', 'Order', 'Provenance.php'), 'utf8');
  const forbiddenMetaPatterns = [
    /customer_email/i,
    /billing_address/i,
    /shipping_address/i,
    /ip_address/i,
    /payment_token/i,
    /secret_key/i,
  ];

  for (const pattern of forbiddenMetaPatterns) {
    assert.ok(!pattern.test(provPhp), `Forbidden PII pattern ${pattern} found in Provenance.php`);
  }
});

test('Codebase strictly obeys scarcity invariant without collector serials or certificates', () => {
  const forbiddenPatterns = [
    /collector_number/i,
    /serial_number/i,
    /certificate_number/i,
    /nft/i,
    /blockchain/i,
    /200 pieces/i,
  ];

  const sourceFiles = [
    resolve(pluginRoot, 'src', 'Order', 'Provenance.php'),
    resolve(pluginRoot, 'src', 'Order', 'Completion.php'),
    resolve(pluginRoot, 'src', 'Order', 'AdminOrderView.php'),
    resolve(pluginRoot, 'src', 'Order', 'CustomerOrderView.php'),
    resolve(pluginRoot, 'src', 'Order', 'EmailIntegration.php'),
  ];

  for (const file of sourceFiles) {
    const content = readFileSync(file, 'utf8');
    for (const pattern of forbiddenPatterns) {
      assert.ok(!pattern.test(content), `Forbidden pattern ${pattern} found in ${file}`);
    }
  }
});
