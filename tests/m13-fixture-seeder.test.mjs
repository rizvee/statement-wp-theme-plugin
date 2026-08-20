import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import test from 'node:test';
import { generateSecrets } from '../scripts/generate-private-access-secrets.mjs';

const root = resolve(import.meta.dirname, '..');
const pluginDir = resolve(root, 'tools', 'statement-integration-fixtures');
const coreDir = resolve(root, 'wp-content', 'plugins', 'statement-collector-core');

test('Temporary Fixture Plugin files exist in tools/statement-integration-fixtures/', () => {
  const requiredFiles = [
    'statement-integration-fixtures.php',
    'src/AdminPage.php',
    'src/FixtureService.php',
    'src/PrivateFixtureService.php',
    'src/VerificationService.php',
    'src/FinalCleanupService.php',
    'src/CleanupService.php',
  ];

  for (const relFile of requiredFiles) {
    const fullPath = resolve(pluginDir, relFile);
    assert.ok(existsSync(fullPath), `Required fixture file must exist: ${relFile}`);
  }
});

test('Fixture Plugin version is 0.3.5 and activation is strictly side-effect free with zero auto-seeding', () => {
  const mainPhp = readFileSync(resolve(pluginDir, 'statement-integration-fixtures.php'), 'utf8');

  assert.match(mainPhp, /Plugin Name:\s*Statement Integration Fixtures/);
  assert.match(mainPhp, /Version:\s*0\.3\.5/);
  assert.match(mainPhp, /STATEMENT_INTEGRATION_FIXTURES_VERSION['"]\s*,\s*['"]0\.3\.5['"]/);


  // Must not call FixtureService::create or seed automatically on activation or plugins_loaded
  assert.doesNotMatch(mainPhp, /FixtureService::create/i, 'Main plugin file must not auto-create fixtures on boot/activation');
  assert.doesNotMatch(mainPhp, /PrivateFixtureService::create/i, 'Main plugin file must not auto-create private fixtures on boot/activation');
  assert.doesNotMatch(mainPhp, /register_activation_hook.*create/i, 'Activation hook must not auto-seed fixtures');
});

test('AdminPage requires manage_woocommerce capability and checks nonces on all POST mutations', () => {
  const adminPhp = readFileSync(resolve(pluginDir, 'src', 'AdminPage.php'), 'utf8');

  assert.match(adminPhp, /manage_woocommerce/, 'AdminPage must require manage_woocommerce capability');
  assert.match(adminPhp, /check_admin_referer\(\s*['"]statement_fixtures_create['"]\s*\)/, 'Create action must check nonce');
  assert.match(adminPhp, /check_admin_referer\(\s*['"]statement_fixtures_adopt['"]\s*\)/, 'Adopt action must check nonce');
  assert.match(adminPhp, /check_admin_referer\(\s*['"]statement_fixtures_cleanup['"]\s*\)/, 'Cleanup action must check nonce');
  assert.match(adminPhp, /check_admin_referer\(\s*['"]statement_fixtures_restore_currency['"]\s*\)/, 'Restore currency action must check nonce');
  assert.match(adminPhp, /check_admin_referer\(\s*['"]statement_fixtures_init_vault['"]\s*\)/, 'Init vault action must check nonce');
  assert.match(adminPhp, /check_admin_referer\(\s*['"]statement_fixtures_reset_vault['"]\s*\)/, 'Reset vault action must check nonce');
  assert.match(adminPhp, /check_admin_referer\(\s*['"]statement_fixtures_create_private['"]\s*\)/, 'Create private action must check nonce');
  assert.match(adminPhp, /check_admin_referer\(\s*['"]statement_fixtures_cleanup_private['"]\s*\)/, 'Cleanup private action must check nonce');
});

test('VerificationService uses WC_Product::is_purchasable() and does NOT reference nonexistent Purchasability::is_purchasable()', () => {
  const verifyPhp = readFileSync(resolve(pluginDir, 'src', 'VerificationService.php'), 'utf8');

  // Must NOT call Purchasability::is_purchasable()
  assert.doesNotMatch(
    verifyPhp,
    /Purchasability::is_purchasable/,
    'VerificationService MUST NOT call nonexistent method Purchasability::is_purchasable()'
  );

  // Must call $product->is_purchasable() to test real WooCommerce + Statement Core filter
  assert.match(
    verifyPhp,
    /\$product->is_purchasable\(\)/,
    'VerificationService must call $product->is_purchasable() to invoke real woocommerce_is_purchasable filter'
  );

  // Must contain try/catch Throwable containment
  assert.match(verifyPhp, /try\s*\{[\s\S]*\}[\s\S]*catch\s*\(\s*\\Throwable/m, 'VerificationService must catch Throwable to prevent white-screens');
});

test('PrivateFixtureService defines preflight diagnostics and canonical Private Access fixture spec', () => {
  const privatePhp = readFileSync(resolve(pluginDir, 'src', 'PrivateFixtureService.php'), 'utf8');

  assert.match(privatePhp, /function get_crypto_diagnostics/, 'Must have get_crypto_diagnostics method');
  assert.match(privatePhp, /function get_secret_diagnostics/, 'Must have get_secret_diagnostics method');
  assert.match(privatePhp, /function get_db_diagnostics/, 'Must have get_db_diagnostics method');
  assert.match(privatePhp, /function init_vault/, 'Must have init_vault method');
  assert.match(privatePhp, /function reset_vault/, 'Must have reset_vault method');
  assert.match(privatePhp, /function create_private_fixture/, 'Must have create_private_fixture method');
  assert.match(privatePhp, /function cleanup_private_fixture/, 'Must have cleanup_private_fixture method');

  // Exact private drop & product spec
  assert.match(privatePhp, /test-private-drop-01/, 'Must specify slug test-private-drop-01');
  assert.match(privatePhp, /TEST-PD01-PAJ/, 'Must specify SKU TEST-PD01-PAJ');
  assert.match(privatePhp, /310/, 'Must specify price 310');
  assert.match(privatePhp, /PRIVATE_ACCESS/, 'Must transition lifecycle to PRIVATE_ACCESS');

  // DB diagnostic table names (M13-DB-01)
  assert.match(privatePhp, /statement_access_grants/, 'DB diagnostic must check statement_access_grants');
  assert.match(privatePhp, /statement_access_sessions/, 'DB diagnostic must check statement_access_sessions');
  assert.match(privatePhp, /statement_access_tokens/, 'DB diagnostic must check statement_access_tokens');
  assert.match(privatePhp, /statement_access_rate_limits/, 'DB diagnostic must check statement_access_rate_limits');
  assert.match(privatePhp, /statement_consent_events/, 'DB diagnostic must check statement_consent_events');
  assert.match(privatePhp, /statement_access_db_version/, 'DB diagnostic must check option statement_access_db_version');
});

test('Contract-drift check: Fixture Tool references only real static methods and properties in Statement Core', () => {
  const fixtureSourceFiles = [
    resolve(pluginDir, 'src', 'FixtureService.php'),
    resolve(pluginDir, 'src', 'PrivateFixtureService.php'),
    resolve(pluginDir, 'src', 'VerificationService.php'),
    resolve(pluginDir, 'src', 'AdminPage.php'),
    resolve(pluginDir, 'src', 'CleanupService.php'),
  ];

  const coreMetadataPhp = readFileSync(resolve(coreDir, 'src', 'Product', 'Metadata.php'), 'utf8');
  const corePurchasabilityPhp = readFileSync(resolve(coreDir, 'src', 'Release', 'Purchasability.php'), 'utf8');
  const coreReleaseStatePhp = readFileSync(resolve(coreDir, 'src', 'Release', 'ReleaseState.php'), 'utf8');
  const coreSecretsPhp = readFileSync(resolve(coreDir, 'src', 'Access', 'Secrets.php'), 'utf8');
  const coreVaultPhp = readFileSync(resolve(coreDir, 'src', 'Access', 'SecretVault.php'), 'utf8');
  const coreDropConfigPhp = readFileSync(resolve(coreDir, 'src', 'Access', 'DropConfig.php'), 'utf8');

  for (const file of fixtureSourceFiles) {
    const content = readFileSync(file, 'utf8');

    // Check DropConfig calls
    if (content.includes('DropConfig::save_config')) {
      assert.ok(coreDropConfigPhp.includes('function save_config'), 'Core DropConfig::save_config must exist');
    }
    if (content.includes('DropConfig::get_config')) {
      assert.ok(coreDropConfigPhp.includes('function get_config'), 'Core DropConfig::get_config must exist');
    }
    if (content.includes('DropConfig::is_config_valid')) {
      assert.ok(coreDropConfigPhp.includes('function is_config_valid'), 'Core DropConfig::is_config_valid must exist');
    }

    // Check Metadata calls
    if (content.includes('Metadata::set_release_state')) {
      assert.ok(coreMetadataPhp.includes('function set_release_state'), 'Core Metadata::set_release_state must exist');
    }
    if (content.includes('Metadata::get_release_state')) {
      assert.ok(coreMetadataPhp.includes('function get_release_state'), 'Core Metadata::get_release_state must exist');
    }
    if (content.includes('Metadata::set_edition_label')) {
      assert.ok(coreMetadataPhp.includes('function set_edition_label'), 'Core Metadata::set_edition_label must exist');
    }
    if (content.includes('Metadata::get_edition_label')) {
      assert.ok(coreMetadataPhp.includes('function get_edition_label'), 'Core Metadata::get_edition_label must exist');
    }

    // Verify Secrets calls
    if (content.includes('Secrets::has_identity_key')) {
      assert.ok(coreSecretsPhp.includes('function has_identity_key'), 'Core Secrets::has_identity_key must exist');
    }
    if (content.includes('Secrets::has_rate_limit_key')) {
      assert.ok(coreSecretsPhp.includes('function has_rate_limit_key'), 'Core Secrets::has_rate_limit_key must exist');
    }
    if (content.includes('Secrets::has_encryption_config')) {
      assert.ok(coreSecretsPhp.includes('function has_encryption_config'), 'Core Secrets::has_encryption_config must exist');
    }
    if (content.includes('Secrets::get_active_key_version')) {
      assert.ok(coreSecretsPhp.includes('function get_active_key_version'), 'Core Secrets::get_active_key_version must exist');
    }
    if (content.includes('SecretVault::create_vault')) {
      assert.ok(coreVaultPhp.includes('function create_vault'), 'Core SecretVault::create_vault must exist');
    }

    // Verify Purchasability has no is_purchasable method
    assert.ok(!corePurchasabilityPhp.includes('function is_purchasable'), 'Core Purchasability class does NOT have function is_purchasable');

    // Check ReleaseState calls
    if (content.includes('ReleaseState::is_terminal')) {
      assert.ok(coreReleaseStatePhp.includes('function is_terminal'), 'Core ReleaseState::is_terminal must exist');
    }
  }
});

test('Secret Generator generates cryptographically strong keys in Git-ignored path without stdout leaks', () => {
  const result = generateSecrets({ targetPath: resolve(root, '.local-runtime', 'test-secrets-wp-config.php'), rotate: true });

  assert.ok(result.generated, 'Secret generator must generate secrets when requested');
  assert.ok(existsSync(result.path), 'Generated file must exist on disk');

  const fileContent = readFileSync(result.path, 'utf8');
  assert.match(fileContent, /define\(\s*['"]STATEMENT_ACCESS_IDENTITY_KEY['"]\s*,\s*['"][a-f0-9]{64}['"]\s*\)/);
  assert.match(fileContent, /define\(\s*['"]STATEMENT_ACCESS_RATE_LIMIT_KEY['"]\s*,\s*['"][a-f0-9]{64}['"]\s*\)/);
  assert.match(fileContent, /define\(\s*['"]STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION['"]\s*,\s*['"]v1['"]\s*\)/);
  assert.match(fileContent, /define\(\s*['"]STATEMENT_ACCESS_ENCRYPTION_KEYS['"]\s*,\s*['"]\{"v1":"[a-f0-9]{64}"\}['"]\s*\)/);

  // Check gitignore includes .local-runtime/
  const gitignore = readFileSync(resolve(root, '.gitignore'), 'utf8');
  assert.ok(gitignore.includes('.local-runtime/'), '.gitignore MUST include .local-runtime/');

  // Non-overwrite test
  const secondRun = generateSecrets({ targetPath: result.path, rotate: false });
  assert.strictEqual(secondRun.status, 'EXISTS', 'Second run without --rotate MUST refuse to overwrite');
});

test('Fixture Seeder source code preserves scarcity invariant with zero serial numbers or certificates', () => {
  const files = [
    resolve(pluginDir, 'statement-integration-fixtures.php'),
    resolve(pluginDir, 'src', 'AdminPage.php'),
    resolve(pluginDir, 'src', 'FixtureService.php'),
    resolve(pluginDir, 'src', 'PrivateFixtureService.php'),
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
