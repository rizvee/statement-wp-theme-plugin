import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const themeDir = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');

test('Account UI: Dedicated Stylesheet & Conditional Asset Loading', () => {
  // 1. File existence
  assert.ok(existsSync(join(themeDir, 'assets/css/account.css')), 'Theme must contain assets/css/account.css');

  // 2. Asset enqueuing logic
  const assetsPhp = readFileSync(join(themeDir, 'inc/assets.php'), 'utf8');
  assert.match(assetsPhp, /\$is_account\s*=\s*function_exists\(\s*'is_account_page'\s*\)\s*&&\s*is_account_page\(\);/, 'inc/assets.php checks is_account_page()');
  assert.match(assetsPhp, /wp_enqueue_style\(\s*'statement-collector-account'/, 'inc/assets.php enqueues statement-collector-account');
});

test('Account UI: Guest Sign-In & Form Input Polish', () => {
  const accountCss = readFileSync(join(themeDir, 'assets/css/account.css'), 'utf8');

  // 1. Guest layout
  assert.match(accountCss, /\.woocommerce-account:not\(\.logged-in\)/, 'account.css formats guest authentication layout');
  assert.match(accountCss, /\.statement-auth-card/, 'account.css defines .statement-auth-card');
  assert.match(accountCss, /\.statement-auth-title/, 'account.css defines .statement-auth-title');

  // 2. Input controls
  assert.match(accountCss, /min-block-size:\s*3\.25rem/, 'account.css sets 3.25rem minimum height for form inputs');
  assert.match(accountCss, /border:\s*1px solid var\(--wp--preset--color--border-grey\)/, 'account.css sets subtle border on inputs');
  assert.match(accountCss, /border-color:\s*var\(--wp--preset--color--near-black\)/, 'account.css highlights input on focus');

  // 3. Submit CTA button
  assert.match(accountCss, /\.woocommerce-form-login__submit/, 'account.css styles login submit button');
  assert.match(accountCss, /min-block-size:\s*3\.5rem/, 'account.css sets 3.5rem minimum height for CTA button');
  assert.match(accountCss, /background:\s*var\(--wp--preset--color--near-black\)/, 'account.css sets near-black background on CTA');
  assert.match(accountCss, /background:\s*var\(--wp--preset--color--ink-navy\)/, 'account.css sets ink-navy on button hover');
});

test('Account UI: Lost Password Screen Polish', () => {
  const accountCss = readFileSync(join(themeDir, 'assets/css/account.css'), 'utf8');
  assert.match(accountCss, /\.lost_reset_password/, 'account.css styles .lost_reset_password');
  assert.match(accountCss, /\.woocommerce-ResetPassword/, 'account.css styles .woocommerce-ResetPassword');
  assert.match(accountCss, /\.woocommerce-LostPassword/, 'account.css styles lost password link');
});

test('Account UI: Logged-in Dashboard Navigation and Content Layout', () => {
  const accountCss = readFileSync(join(themeDir, 'assets/css/account.css'), 'utf8');

  // 1. Dashboard layout
  assert.match(accountCss, /\.woocommerce-account\.logged-in/, 'account.css defines logged-in account grid');
  assert.match(accountCss, /\.woocommerce-MyAccount-navigation/, 'account.css styles navigation tabs');
  assert.match(accountCss, /\.woocommerce-MyAccount-content/, 'account.css styles content container');

  // 2. Navigation items
  assert.match(accountCss, /\.woocommerce-MyAccount-navigation\s+\.is-active\s+a/, 'account.css styles active nav tab');
});

test('Account UI: Orders Table & Responsive Mobile Stack', () => {
  const accountCss = readFileSync(join(themeDir, 'assets/css/account.css'), 'utf8');

  // 1. Orders table
  assert.match(accountCss, /\.woocommerce-orders-table/, 'account.css styles .woocommerce-orders-table');
  assert.match(accountCss, /\.woocommerce-orders-table__cell-order-actions/, 'account.css styles order actions');

  // 2. Mobile stacked table
  assert.match(accountCss, /@media\s*\(max-width:\s*48rem\)/, 'account.css includes mobile media query');
  assert.match(accountCss, /content:\s*attr\(data-title\)/, 'account.css uses data-title attribute for mobile responsive headers');
});

test('Account UI: Monochrome Notices and Feedback States', () => {
  const accountCss = readFileSync(join(themeDir, 'assets/css/account.css'), 'utf8');
  assert.match(accountCss, /\.woocommerce-error/, 'account.css styles .woocommerce-error');
  assert.match(accountCss, /\.woocommerce-message/, 'account.css styles .woocommerce-message');
  assert.match(accountCss, /\.woocommerce-info/, 'account.css styles .woocommerce-info');
  assert.match(accountCss, /border-left:\s*4px solid var\(--wp--preset--color--near-black\)/, 'account.css uses near-black accent border for notices');
});
