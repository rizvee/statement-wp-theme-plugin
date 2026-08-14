import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const pluginDir = resolve(root, 'tools', 'statement-integration-fixtures');

test('Temporary Fixture Plugin files exist in tools/statement-integration-fixtures/', () => {
  const requiredFiles = [
    'statement-integration-fixtures.php',
    'src/AdminPage.php',
    'src/FixtureService.php',
    'src/VerificationService.php',
    'src/CleanupService.php',
  ];

  for (const relFile of requiredFiles) {
    const fullPath = resolve(pluginDir, relFile);
    assert.ok(existsSync(fullPath), `Required fixture file must exist: ${relFile}`);
  }
});

test('Fixture Plugin activation is strictly side-effect free with zero auto-seeding', () => {
  const mainPhp = readFileSync(resolve(pluginDir, 'statement-integration-fixtures.php'), 'utf8');

  assert.match(mainPhp, /Plugin Name:\s*Statement Integration Fixtures/);
  assert.match(mainPhp, /Version:\s*0\.1\.0/);

  // Must not call FixtureService::create or seed automatically on activation or plugins_loaded
  assert.doesNotMatch(mainPhp, /FixtureService::create/i, 'Main plugin file must not auto-create fixtures on boot/activation');
  assert.doesNotMatch(mainPhp, /register_activation_hook.*create/i, 'Activation hook must not auto-seed fixtures');
});

test('AdminPage requires manage_woocommerce capability and checks nonces on all POST mutations', () => {
  const adminPhp = readFileSync(resolve(pluginDir, 'src', 'AdminPage.php'), 'utf8');

  assert.match(adminPhp, /manage_woocommerce/, 'AdminPage must require manage_woocommerce capability');
  assert.match(adminPhp, /check_admin_referer\(\s*['"]statement_fixtures_create['"]\s*\)/, 'Create action must check nonce');
  assert.match(adminPhp, /check_admin_referer\(\s*['"]statement_fixtures_cleanup['"]\s*\)/, 'Cleanup action must check nonce');
  assert.match(adminPhp, /check_admin_referer\(\s*['"]statement_fixtures_restore_currency['"]\s*\)/, 'Restore currency action must check nonce');
});

test('FixtureService specifies exact approved taxonomies, slugs, SKUs, and AUD currency', () => {
  const fixturePhp = readFileSync(resolve(pluginDir, 'src', 'FixtureService.php'), 'utf8');

  // Taxonomies
  assert.match(fixturePhp, /'product_cat'/, 'Must use product_cat taxonomy for category');
  assert.match(fixturePhp, /'test-outerwear'/, 'Category slug must be test-outerwear');

  assert.match(fixturePhp, /'product_tag'/, 'Must use product_tag taxonomy for tag');
  assert.match(fixturePhp, /'test-integration'/, 'Tag slug must be test-integration');

  assert.match(fixturePhp, /'statement_drop'/, 'Must use statement_drop taxonomy for Drop');
  assert.match(fixturePhp, /'test-live-drop-01'/, 'Drop slug must be test-live-drop-01');

  // Currency
  assert.match(fixturePhp, /update_option\(\s*['"]woocommerce_currency['"]\s*,\s*['"]AUD['"]\s*\)/, 'Must update woocommerce_currency to AUD');

  // Products & SKUs
  assert.match(fixturePhp, /TEST-LD01-MJ/, 'Must define SKU TEST-LD01-MJ');
  assert.match(fixturePhp, /TEST-LD01-MJ-S/, 'Must define SKU TEST-LD01-MJ-S');
  assert.match(fixturePhp, /TEST-LD01-MJ-M/, 'Must define SKU TEST-LD01-MJ-M');
  assert.match(fixturePhp, /TEST-LD01-MJ-L/, 'Must define SKU TEST-LD01-MJ-L');
  assert.match(fixturePhp, /TEST-LD01-SO/, 'Must define SKU TEST-LD01-SO');
  assert.match(fixturePhp, /TEST-LD01-TJ/, 'Must define SKU TEST-LD01-TJ');

  // States & Boundaries
  assert.match(fixturePhp, /'SOLD_OUT'/, 'Must set SOLD_OUT state for Product 3');
  assert.doesNotMatch(fixturePhp, /'PRIVATE_ACCESS'/, 'FixtureService MUST NOT create PRIVATE_ACCESS products in Phase 3A');
  assert.doesNotMatch(fixturePhp, /'ARCHIVED'/, 'FixtureService MUST NOT create ARCHIVED products in Phase 3A');

  // Stock contract for Product 3 Terminal positive-stock test
  assert.match(fixturePhp, /set_stock_quantity\(\s*5\s*\)/, 'Terminal product must retain stock quantity 5');
});

test('CleanupService deletes strictly IDs recorded in manifest and preserves AUD currency by default', () => {
  const cleanupPhp = readFileSync(resolve(pluginDir, 'src', 'CleanupService.php'), 'utf8');

  assert.match(cleanupPhp, /wp_delete_post/, 'Cleanup must delete post IDs recorded in manifest');
  assert.match(cleanupPhp, /wp_delete_term/, 'Cleanup must delete term IDs recorded in manifest');
  assert.match(cleanupPhp, /delete_option\(\s*FixtureService::MANIFEST_OPTION\s*\)/, 'Cleanup must delete manifest option');

  // Must not auto-restore currency in main cleanup() method
  const cleanupMethodMatch = cleanupPhp.match(/public static function cleanup\(\)[\s\S]*?^  \}/m);
  if (cleanupMethodMatch) {
    assert.doesNotMatch(cleanupMethodMatch[0], /update_option\(\s*['"]woocommerce_currency['"]/, 'cleanup() method MUST NOT automatically restore currency');
  }
});

test('Fixture Seeder source code preserves scarcity invariant with zero serial numbers or certificates', () => {
  const files = [
    resolve(pluginDir, 'statement-integration-fixtures.php'),
    resolve(pluginDir, 'src', 'AdminPage.php'),
    resolve(pluginDir, 'src', 'FixtureService.php'),
    resolve(pluginDir, 'src', 'VerificationService.php'),
    resolve(pluginDir, 'src', 'CleanupService.php'),
  ];

  const forbiddenPatterns = [
    /collector[_-]?number/i,
    /serial[_-]?number/i,
    /certificate[_-]?number/i,
    /200 pieces/i,
    /001\/200/i,
    /restock/i,
  ];

  for (const file of files) {
    const content = readFileSync(file, 'utf8');
    for (const pattern of forbiddenPatterns) {
      assert.ok(!pattern.test(content), `Forbidden pattern ${pattern} found in ${file}`);
    }
  }
});
