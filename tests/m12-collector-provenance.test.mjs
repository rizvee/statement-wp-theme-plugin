import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const pluginRoot = resolve(root, 'wp-content', 'plugins', 'statement-collector-core');
const themeRoot = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');

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
});

test('Completion helper evaluates order status deterministically without certificate generation', () => {
  const compPhp = readFileSync(resolve(pluginRoot, 'src', 'Order', 'Completion.php'), 'utf8');
  assert.ok(compPhp.includes('is_commercially_completed'), 'Completion.php must define is_commercially_completed.');
  assert.ok(compPhp.includes('processing'), 'Completion.php must consider processing completed.');
  assert.ok(compPhp.includes('completed'), 'Completion.php must consider completed completed.');
  assert.ok(!compPhp.includes('certificate'), 'Completion.php must not implement certificate generation.');
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
