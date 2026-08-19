import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import { join } from 'node:path';
import { execSync } from 'node:child_process';

const WORKSPACE = process.cwd();

test('Cleanup Safety: FinalCleanupService source exists and enforces deterministic safety contracts', (t) => {
  const file = join(WORKSPACE, 'tools', 'statement-integration-fixtures', 'src', 'FinalCleanupService.php');
  assert.ok(existsSync(file), 'FinalCleanupService.php must exist.');

  const content = readFileSync(file, 'utf8');

  // Dry run requirement
  assert.match(content, /public static function dry_run\(\)/, 'Must define dry_run() method.');
  assert.match(content, /is_safe_to_execute/, 'Must evaluate is_safe_to_execute.');

  // Capability check
  assert.match(content, /manage_woocommerce/, 'Must require manage_woocommerce capability.');

  // Product 213 & 271 preservation
  assert.match(content, /STMT-CD-/, 'Must check for STMT-CD- prefix for preservation.');
  assert.match(content, /_statement_client_demo/, 'Must check _statement_client_demo meta for preservation.');
  assert.match(content, /Monogram Jacquard Jacket/, 'Must explicitly recognize and preserve Monogram Jacquard Jacket.');

  // Drop 001 preservation
  assert.match(content, /drop-001-monogram-study/, 'Must verify Drop 001 is preserved.');

  // HPOS-compatible QA Order deletion
  assert.match(content, /_statement_is_qa_order/, 'Must strictly filter orders by _statement_is_qa_order meta.');
  assert.match(content, /wc_get_orders/, 'Must use wc_get_orders() API.');

  // Canonical M10 operational tables
  assert.match(content, /AccessSchema::get_table_names/, 'Must derive table names from AccessSchema.');
  assert.match(content, /statement_access_grants/, 'Must target statement_access_grants.');
  assert.match(content, /statement_access_sessions/, 'Must target statement_access_sessions.');
  assert.match(content, /statement_access_tokens/, 'Must target statement_access_tokens.');
  assert.match(content, /statement_access_rate_limits/, 'Must target statement_access_rate_limits.');
  assert.match(content, /statement_consent_events/, 'Must target statement_consent_events.');

  // Zero forbidden stale table names
  assert.doesNotMatch(content, /statement_private_grants|statement_private_sessions|statement_marketing_consents/, 'Must not reference stale table names.');
});

test('Cleanup Safety: CleanupService.php exposes dry_run and final_cleanup delegates', (t) => {
  const file = join(WORKSPACE, 'tools', 'statement-integration-fixtures', 'src', 'CleanupService.php');
  assert.ok(existsSync(file), 'CleanupService.php must exist.');

  const content = readFileSync(file, 'utf8');
  assert.match(content, /public static function dry_run\(\)/, 'Must expose dry_run() delegate.');
  assert.match(content, /public static function final_cleanup\(\)/, 'Must expose final_cleanup() delegate.');
});

test('QA Gateway Isolation: StatementQaGateway is strictly owned by Fixtures plugin and restricted to test SKU', (t) => {
  const gwFile = join(WORKSPACE, 'tools', 'statement-integration-fixtures', 'src', 'StatementQaGateway.php');
  assert.ok(existsSync(gwFile), 'StatementQaGateway.php must exist in fixtures tool.');

  const content = readFileSync(gwFile, 'utf8');
  assert.match(content, /public const GATEWAY_ID = 'statement_qa_gateway'/, 'Must define statement_qa_gateway ID.');
  assert.match(content, /public const TARGET_SKU = 'TEST-PD01-PAJ'/, 'Must restrict to TEST-PD01-PAJ SKU.');
  assert.match(content, /is_available\(\)/, 'Must implement is_available() gate.');

  // Verify Core and Theme never register or mention StatementQaGateway
  const themeFunctions = readFileSync(join(WORKSPACE, 'wp-content', 'themes', 'statement-collector-theme', 'functions.php'), 'utf8');
  const coreEntry = readFileSync(join(WORKSPACE, 'wp-content', 'plugins', 'statement-collector-core', 'statement-collector-core.php'), 'utf8');

  assert.doesNotMatch(themeFunctions, /StatementQaGateway|statement_qa_gateway/, 'Theme must not contain StatementQaGateway references.');
  assert.doesNotMatch(coreEntry, /StatementQaGateway|statement_qa_gateway/, 'Core must not contain StatementQaGateway references.');
});

test('Client Demo Independence: Theme and Core maintain zero runtime dependency on Client Demo', (t) => {
  const themeSetup = readFileSync(join(WORKSPACE, 'wp-content', 'themes', 'statement-collector-theme', 'inc', 'setup.php'), 'utf8');
  const coreEntry = readFileSync(join(WORKSPACE, 'wp-content', 'plugins', 'statement-collector-core', 'statement-collector-core.php'), 'utf8');

  assert.doesNotMatch(themeSetup, /Statement\\ClientDemo/, 'Theme must not depend on ClientDemo namespace.');
  assert.doesNotMatch(coreEntry, /Statement\\ClientDemo/, 'Core must not depend on ClientDemo namespace.');
});

test('Documentation Truth: Docs reference canonical M10 Schema table names and Jetpack Backup', (t) => {
  const cleanupDoc = readFileSync(join(WORKSPACE, 'docs', 'final-fixture-cleanup.md'), 'utf8');
  const emailDoc = readFileSync(join(WORKSPACE, 'docs', 'email-launch-readiness.md'), 'utf8');

  assert.match(cleanupDoc, /wp_statement_consent_events/, 'Cleanup doc must reference wp_statement_consent_events.');
  assert.doesNotMatch(cleanupDoc, /wp_statement_marketing_consents/, 'Cleanup doc must not reference wp_statement_marketing_consents.');
  assert.match(cleanupDoc, /Jetpack -> Backup/, 'Cleanup doc must reference Jetpack -> Backup.');

  assert.match(emailDoc, /wp_statement_consent_events/, 'Email doc must reference wp_statement_consent_events.');
  assert.doesNotMatch(emailDoc, /wp_statement_marketing_consents/, 'Email doc must not reference wp_statement_marketing_consents.');
});

test('Cleanup Safety: Execute PHP test-final-cleanup-safety.php unit suite', (t) => {
  const output = execSync('.local-tools\\php\\php.exe tests/php/test-final-cleanup-safety.php', { cwd: WORKSPACE }).toString();
  assert.match(output, /PASS:/, 'test-final-cleanup-safety.php must pass cleanly');
});
