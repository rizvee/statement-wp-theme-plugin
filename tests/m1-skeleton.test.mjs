import assert from 'node:assert/strict';
import { spawnSync } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
import test from 'node:test';
import { resolve } from 'node:path';
import { resolvePhp } from '../scripts/lib/resolve-php.mjs';

const root = resolve(import.meta.dirname, '..');
const themeRoot = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');
const pluginRoot = resolve(root, 'wp-content', 'plugins', 'statement-collector-core');

const themeFiles = [
  'style.css',
  'functions.php',
  'index.php',
  'inc/setup.php',
  'inc/woocommerce.php',
];
const pluginFiles = ['statement-collector-core.php', 'src/Plugin.php'];
const forbiddenRuntimeSignals = [
  /register_post_type\s*\(/i,
  /register_taxonomy\s*\(/i,
  /register_post_meta\s*\(/i,
  /register_rest_route\s*\(/i,
  /wp_ajax_/i,
  /wp_schedule_(?:event|single_event)\s*\(/i,
  /as_schedule_(?:single|recurring)_action\s*\(/i,
  /woocommerce_(?:product_set_stock|reduce_order_stock|restore_order_stock)/i,
  /private[_ -]?access/i,
  /magic[_ -]?link/i,
  /email[_ -]?reminder/i,
];

function read(path) {
  return readFileSync(path, 'utf8');
}

function runtimeSource() {
  return [...themeFiles.map((path) => read(resolve(themeRoot, path))), ...pluginFiles.map((path) => read(resolve(pluginRoot, path)))].join('\n');
}

test('theme skeleton exposes the required standalone metadata and files', () => {
  for (const path of themeFiles) {
    assert.equal(existsSync(resolve(themeRoot, path)), true, `missing theme file: ${path}`);
  }

  const style = read(resolve(themeRoot, 'style.css'));
  assert.match(style, /^Theme Name:\s*Statement Collector(?:'s)? Piece$/m);
  assert.match(style, /^Description:\s*\S.+$/m);
  assert.match(style, /^Version:\s*0\.1\.0$/m);
  assert.match(style, /^Text Domain:\s*statement-collector-theme$/m);
  assert.doesNotMatch(style, /^Template\s*:/mi);
});

test('theme bootstrap stays focused and delegates setup', () => {
  const bootstrap = read(resolve(themeRoot, 'functions.php'));
  assert.match(bootstrap, /defined\(\s*'ABSPATH'\s*\)/);
  assert.match(bootstrap, /inc[\\/'].*setup\.php/);
  assert.match(bootstrap, /inc[\\/'].*woocommerce\.php/);
  assert.doesNotMatch(bootstrap, /add_action\s*\(/i);
  assert.ok(bootstrap.split(/\r?\n/).length <= 25, 'functions.php should remain a small bootstrap');
});

test('plugin skeleton exposes metadata and a namespaced one-time bootstrap', () => {
  for (const path of pluginFiles) {
    assert.equal(existsSync(resolve(pluginRoot, path)), true, `missing plugin file: ${path}`);
  }

  const entrypoint = read(resolve(pluginRoot, 'statement-collector-core.php'));
  const pluginClass = read(resolve(pluginRoot, 'src', 'Plugin.php'));
  assert.match(entrypoint, /^Plugin Name:\s*Statement Collector Core$/m);
  assert.match(entrypoint, /^Version:\s*0\.1\.0$/m);
  assert.match(entrypoint, /^Text Domain:\s*statement-collector-core$/m);
  assert.match(entrypoint, /defined\(\s*'ABSPATH'\s*\)/);
  assert.match(pluginClass, /namespace\s+Statement\\Collector\\Core\s*;/);
  assert.match(pluginClass, /final\s+class\s+Plugin/);
  assert.match(pluginClass, /static\s+function\s+boot\s*\(/);
});

test('base bootstraps contain no forbidden later-milestone registration', () => {
  const source = runtimeSource();
  for (const signal of forbiddenRuntimeSignals) {
    assert.doesNotMatch(source, signal);
  }
});

test('theme and plugin boot safely without WooCommerce', () => {
  const php = resolvePhp();
  assert.ok(php, 'PHP must resolve for the M1 bootstrap smoke test');

  const smokeTest = resolve(root, 'tests', 'php', 'm1-bootstrap-smoke.php');
  assert.equal(existsSync(smokeTest), true, 'missing PHP bootstrap smoke test');
  const result = spawnSync(php.executable, [smokeTest], {
    encoding: 'utf8',
    shell: false,
    windowsHide: true,
  });

  assert.equal(result.status, 0, `${result.stdout}${result.stderr}`);
  assert.match(result.stdout, /PASS: M1 bootstrap smoke passed\./);
});
