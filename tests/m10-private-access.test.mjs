import assert from 'node:assert/strict';
import { execSync } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const pluginRoot = resolve(root, 'wp-content', 'plugins', 'statement-collector-core');
const themeRoot = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');

const requiredAccessFiles = [
  'src/Access/SecretVault.php',
  'src/Access/Secrets.php',
  'src/Access/Crypto.php',
  'src/Access/Schema.php',
  'src/Access/GrantService.php',
  'src/Access/SessionService.php',
  'src/Access/TokenService.php',
  'src/Access/RateLimiter.php',
  'src/Access/ConsentService.php',
  'src/Access/DropConfig.php',
  'src/Access/Precheck.php',
  'src/Access/EligibilityService.php',
  'src/Access/MakeDropLive.php',
  'src/Access/PrivateAccessGate.php',
  'src/Access/OrderAudit.php',
  'src/Access/EmailAccessGranted.php',
  'src/Access/EmailAccessReminder.php',
  'src/Access/ReminderService.php',
  'src/Access/UnsubscribeService.php',
  'src/Access/AdminUi.php',
  'src/Access/RetentionService.php',
  'src/Access/CacheHardening.php',
];

function read(relativePath) {
  return readFileSync(resolve(pluginRoot, relativePath), 'utf8');
}

test('M10 Private Access plugin files exist and are bootstrapped', () => {
  for (const path of requiredAccessFiles) {
    assert.equal(existsSync(resolve(pluginRoot, path)), true, `missing M10 plugin file: ${path}`);
  }

  const entrypoint = read('statement-collector-core.php');
  const plugin = read('src/Plugin.php');

  for (const path of requiredAccessFiles) {
    assert.match(entrypoint, new RegExp(path.replaceAll('/', '[\\\\/]')));
  }

  assert.match(plugin, /Access\\PrivateAccessGate::boot\s*\(/);
  assert.match(plugin, /Access\\OrderAudit::boot\s*\(/);
  assert.match(plugin, /Access\\ReminderService::boot\s*\(/);
  assert.match(plugin, /Access\\UnsubscribeService::boot\s*\(/);
  assert.match(plugin, /Access\\AdminUi::boot\s*\(/);
  assert.match(plugin, /Access\\RetentionService::boot\s*\(/);
  assert.match(plugin, /Access\\CacheHardening::boot\s*\(/);
});

test('Database Schema defines 5 dedicated operational tables with WP prefix', () => {
  const schema = read('src/Access/Schema.php');

  assert.match(schema, /statement_access_grants/);
  assert.match(schema, /statement_access_sessions/);
  assert.match(schema, /statement_access_tokens/);
  assert.match(schema, /statement_access_rate_limits/);
  assert.match(schema, /statement_consent_events/);
  assert.match(schema, /dbDelta\s*\(/);
});

test('Secrets, SecretVault, and Crypto manage authenticated encryption, keyring versioning, provider precedence, and HMAC identities', () => {
  const trackedFiles = execSync('git ls-files', { encoding: 'utf8' }).replace(/\\/g, '/');
  assert.match(trackedFiles, /wp-content\/plugins\/statement-collector-core\/src\/Access\/Secrets\.php/);
  assert.match(trackedFiles, /wp-content\/plugins\/statement-collector-core\/src\/Access\/SecretVault\.php/);

  const secrets = read('src/Access/Secrets.php');
  const vault = read('src/Access/SecretVault.php');
  const crypto = read('src/Access/Crypto.php');

  assert.match(secrets, /STATEMENT_ACCESS_IDENTITY_KEY/);
  assert.match(secrets, /STATEMENT_ACCESS_RATE_LIMIT_KEY/);
  assert.match(secrets, /STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION/);
  assert.match(secrets, /STATEMENT_ACCESS_ENCRYPTION_KEYS/);
  assert.match(secrets, /get_provider/);
  assert.match(secrets, /invalid_wp_config/);
  assert.match(secrets, /encrypted_vault/);

  assert.match(vault, /statement_access_secret_vault_v1/);
  assert.match(vault, /statement-access-secret-vault-v1/);
  assert.match(vault, /wp_salt/);
  assert.match(vault, /create_vault/);
  assert.match(vault, /decrypt_bundle/);

  assert.match(crypto, /hash_hmac\s*\(\s*'sha256'/);
  assert.match(crypto, /normalize_email\s*\(/);
  assert.match(crypto, /key_version/);
  assert.doesNotMatch(crypto, /plaintext_fallback/i);
});

test('Grant, Session, Token, and RateLimiter enforce locked M10 boundaries', () => {
  const grant = read('src/Access/GrantService.php');
  const session = read('src/Access/SessionService.php');
  const token = read('src/Access/TokenService.php');
  const rateLimit = read('src/Access/RateLimiter.php');

  assert.match(grant, /min\s*\(\s*\$indiv_expires\s*,\s*\$drop_close_ts\s*\)/); // immutable grant expiry
  assert.match(grant, /supersedes_grant_id/); // historical re-grant tracking
  assert.match(session, /MAX_ACTIVE_SESSIONS\s*=\s*5/); // 5 active session cap
  assert.match(session, /setcookie\s*\(/);
  assert.match(token, /access_return/);
  assert.match(token, /marketing_unsubscribe/);
  assert.match(rateLimit, /IP_SHORT_LIMIT\s*=\s*5/);
  assert.match(rateLimit, /IP_LONG_LIMIT\s*=\s*20/);
  assert.match(rateLimit, /EMAIL_SHORT_LIMIT\s*=\s*3/);
  assert.match(rateLimit, /EMAIL_LONG_LIMIT\s*=\s*10/);
});

test('Central EligibilityService is reused across commerce boundaries', () => {
  const eligibility = read('src/Access/EligibilityService.php');
  const productAccess = read('src/Product/Access.php');
  const cartIntegrity = read('src/Cart/Integrity.php');
  const orderAudit = read('src/Access/OrderAudit.php');

  assert.match(eligibility, /is_commerce_eligible/);
  assert.match(productAccess, /EligibilityService::is_commerce_eligible/);
  assert.match(cartIntegrity, /EligibilityService::is_commerce_eligible/);
  assert.match(orderAudit, /_statement_private_access_grant_id/);
  assert.match(orderAudit, /_statement_private_access_drop_id/);
  assert.match(orderAudit, /_statement_private_access_authorized_at/);
  assert.match(orderAudit, /_statement_private_access_context_version/);
  assert.doesNotMatch(orderAudit, /_statement_private_access_email|_statement_private_access_ip|_statement_private_access_token/i);
});

test('MakeDropLive handles preflight and atomic transition without touching UPCOMING', () => {
  const makeLive = read('src/Access/MakeDropLive.php');

  assert.match(makeLive, /get_preflight_summary/);
  assert.match(makeLive, /PRIVATE_ACCESS/);
  assert.match(makeLive, /LIVE/);
  assert.match(makeLive, /manage_woocommerce/);
});

test('CacheHardening and PrivateAccessGate apply strict privacy & SEO headers', () => {
  const hardening = read('src/Access/CacheHardening.php');
  const gate = read('src/Access/PrivateAccessGate.php');

  assert.match(hardening, /Cache-Control:\s*private,\s*no-store/i);
  assert.match(hardening, /noindex,\s*nofollow/i);
  assert.match(gate, /Cache-Control:\s*private,\s*no-store/i);
  assert.match(gate, /statement_private_access_/);
  assert.match(gate, /wp_safe_redirect\s*\(\s*.*,\s*303\s*\)/); // PRG 303
});

test('Source code preserves scarcity model and contains zero forbidden signals', () => {
  const source = requiredAccessFiles.map((path) => read(path)).join('\n');
  const forbidden = [
    /production[_ -]?cap|edition[_ -]?total|max[_ -]?pieces|piece[_ -]?total|\b200\b/i,
    /restock(?:ing)?|replenish(?:ment)?/i,
    /temporary[_ -]?sold[_ -]?out/i,
  ];

  for (const signal of forbidden) {
    assert.doesNotMatch(source, signal);
  }
});
