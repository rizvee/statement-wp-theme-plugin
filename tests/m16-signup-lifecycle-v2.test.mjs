import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const pluginDir = resolve(root, 'wp-content', 'plugins', 'statement-collector-core');
const themeDir = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');

test('Core Marketing SignupService implements full Mode A/B/C capture with security invariants', () => {
  const signupPhp = readFileSync(resolve(pluginDir, 'src', 'Marketing', 'SignupService.php'), 'utf8');

  assert.match(signupPhp, /namespace\s+Statement\\Collector\\Core\\Marketing;/);
  assert.match(signupPhp, /final\s+class\s+SignupService/);
  assert.match(signupPhp, /const\s+NONCE_ACTION\s*=\s*['"]statement_signup_action['"];/);
  assert.match(signupPhp, /const\s+ACTION_SUBMIT\s*=\s*['"]statement_signup_submit['"];/);
  assert.match(signupPhp, /wp_verify_nonce/);
  assert.match(signupPhp, /is_email/);
  assert.match(signupPhp, /RateLimiter::is_rate_limited/);
  assert.match(signupPhp, /hash_hmac/);
  assert.match(signupPhp, /GrantService::issue_grant/);
  assert.match(signupPhp, /wp_safe_redirect/);
  assert.match(signupPhp, /303/); // PRG pattern
});

test('Core Admin LifecycleV2Admin implements admin manual overrides and audit log', () => {
  const lifecyclePhp = readFileSync(resolve(pluginDir, 'src', 'Admin', 'LifecycleV2Admin.php'), 'utf8');

  assert.match(lifecyclePhp, /namespace\s+Statement\\Collector\\Core\\Admin;/);
  assert.match(lifecyclePhp, /final\s+class\s+LifecycleV2Admin/);
  assert.match(lifecyclePhp, /current_user_can\(\s*['"]manage_woocommerce['"]\s*\)/);
  assert.match(lifecyclePhp, /wp_verify_nonce/);
  assert.match(lifecyclePhp, /OPTION_AUDIT_LOG\s*=\s*['"]statement_lifecycle_audit_log['"];/);
  assert.match(lifecyclePhp, /handle_lifecycle_override/);
  assert.match(lifecyclePhp, /record_audit_event/);
  assert.match(lifecyclePhp, /Metadata::set_release_state/);
  assert.match(lifecyclePhp, /ReleaseState::LIVE/);
  assert.match(lifecyclePhp, /ReleaseState::PRIVATE_ACCESS/);
});

test('Catalog Visibility isolates QA fixtures from public queries without breaking direct lookups', () => {
  const visibilityPhp = readFileSync(resolve(pluginDir, 'src', 'Catalog', 'Visibility.php'), 'utf8');

  assert.match(visibilityPhp, /posts_clauses/);
  assert.match(visibilityPhp, /filter_public_catalog_posts_clauses/);
  assert.match(visibilityPhp, /TEST —/);
  assert.match(visibilityPhp, /test-/);
});

test('PublicApi provides clean drop resolution helpers', () => {
  const publicApi = readFileSync(resolve(pluginDir, 'src', 'PublicApi.php'), 'utf8');

  assert.match(publicApi, /function get_current_drop\s*\(/);
  assert.match(publicApi, /function get_drop_state\s*\(/);
  assert.match(publicApi, /function get_live_products_for_drop\s*\(/);
});

test('Theme homepage email capture template integrates seamlessly with SignupService', () => {
  const emailCapturePhp = readFileSync(resolve(themeDir, 'template-parts', 'home', 'email-capture.php'), 'utf8');

  assert.match(emailCapturePhp, /SignupService::NONCE_ACTION/);
  assert.match(emailCapturePhp, /name=["']statement_signup_submit["']/);
  assert.match(emailCapturePhp, /name=["']statement_email["']/);
  assert.match(emailCapturePhp, /PRIVATE ACCESS/);
  assert.match(emailCapturePhp, /role=["']status["']/);
});
