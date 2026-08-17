import assert from 'node:assert/strict';
import { existsSync, readFileSync, readdirSync } from 'node:fs';
import { extname, relative, resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const themeRoot = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');
const pluginRoot = resolve(root, 'wp-content', 'plugins', 'statement-collector-core');

function readTheme(path) {
  return readFileSync(resolve(themeRoot, path), 'utf8');
}

function readPlugin(path) {
  return readFileSync(resolve(pluginRoot, path), 'utf8');
}

function walk(path, output = []) {
  if (!existsSync(path)) return output;
  for (const entry of readdirSync(path, { withFileTypes: true })) {
    const child = resolve(path, entry.name);
    if (entry.isDirectory()) walk(child, output);
    else output.push(child);
  }
  return output;
}

function balancedBraces(css) {
  let depth = 0;
  for (const character of css.replace(/\/\*[\s\S]*?\*\//g, '')) {
    if (character === '{') depth += 1;
    if (character === '}') depth -= 1;
    if (depth < 0) return false;
  }
  return depth === 0;
}

test('M8 focused runtime files exist', () => {
  const required = [
    resolve(pluginRoot, 'src', 'Cart', 'Integrity.php'),
    resolve(themeRoot, 'inc', 'cart.php'),
    resolve(themeRoot, 'assets', 'css', 'cart.css'),
    resolve(themeRoot, 'woocommerce', 'cart', 'cart.php'),
  ];

  for (const path of required) assert.equal(existsSync(path), true, `missing M8 file: ${relative(root, path)}`);
});

test('Core owns canonical LIVE-only cart lifecycle integrity', () => {
  const entrypoint = readPlugin('statement-collector-core.php');
  const plugin = readPlugin('src/Plugin.php');
  const integrity = readPlugin('src/Cart/Integrity.php');

  assert.match(entrypoint, /src[\\/]Cart[\\/]Integrity\.php/);
  assert.match(plugin, /Cart\\Integrity::boot\s*\(/);
  assert.match(integrity, /woocommerce_cart_item_is_purchasable/);
  assert.match(integrity, /woocommerce_check_cart_items/);
  assert.match(integrity, /Metadata::get_release_owner\s*\(/);
  assert.match(integrity, /Metadata::get_release_state\s*\(/);
  assert.match(integrity, /ReleaseState::LIVE/);
  assert.match(integrity, /remove_cart_item\s*\(/);
  assert.match(integrity, /A piece in your bag is no longer available\./);
  assert.doesNotMatch(integrity, /set_stock|stock_quantity|update_meta_data|delete_meta_data|->save\s*\(|wp_set_object_terms/i);
});

test('Bag count uses the native cart count and all header placements share one label helper', () => {
  const cart = readTheme('inc/cart.php');
  const navigation = readTheme('inc/navigation.php');
  const header = readTheme('template-parts/header/site-header.php');
  const mobile = readTheme('template-parts/header/mobile-navigation.php');

  assert.match(cart, /function\s+get_bag_count\s*\(/);
  assert.match(cart, /get_cart_contents_count\s*\(/);
  assert.match(cart, /max\s*\(\s*0\s*,/);
  assert.match(cart, /function\s+get_bag_label\s*\(/);
  assert.match(cart, /BAG \(%d\)/);
  assert.match(navigation, /wc_get_cart_url\s*\(/);
  assert.ok((header.match(/get_bag_label\s*\(/g) ?? []).length >= 2, 'desktop and mobile header must use the shared Bag label');
  assert.match(mobile, /get_bag_label\s*\(/);
  assert.doesNotMatch(`${cart}\n${navigation}\n${header}\n${mobile}`, /cart[_ -]?fragments?|mini[-_ ]?cart|wc_ajax|fetch\s*\(|XMLHttpRequest/i);
});

test('classic Cart override retains current native form, item, and security contracts', () => {
  const cart = readTheme('woocommerce/cart/cart.php');

  assert.match(cart, /@version\s+\d+\.\d+\.\d+/);
  assert.match(cart, /<form\b[^>]*woocommerce-cart-form/is);
  assert.match(cart, /wc_get_cart_url\s*\(/);
  assert.match(cart, /WC\(\)->cart->get_cart\s*\(/);
  assert.match(cart, /woocommerce_cart_item_thumbnail/);
  assert.match(cart, /woocommerce_cart_item_name/);
  assert.match(cart, /wc_get_formatted_cart_item_data\s*\(/);
  assert.match(cart, /woocommerce_quantity_input\s*\(/);
  assert.match(cart, /wc_get_cart_remove_url\s*\(/);
  assert.match(cart, /woocommerce-cart/);
  assert.match(cart, /woocommerce_cart_collaterals/);
});

test('Cart presentation keeps native totals and checkout while omitting coupons and cross-sells', () => {
  const cartModule = readTheme('inc/cart.php');
  const cartTemplate = readTheme('woocommerce/cart/cart.php');

  assert.match(cartModule, /remove_action\s*\(\s*['"]woocommerce_cart_collaterals['"]\s*,\s*['"]woocommerce_cross_sell_display['"]/);
  assert.match(cartTemplate, /do_action\(\s*['"]woocommerce_cart_collaterals['"]\s*\)/);
  assert.match(cartTemplate, /UPDATE BAG/);
  assert.doesNotMatch(cartTemplate, /coupon_code|apply_coupon|APPLY COUPON/i);
  assert.doesNotMatch(cartTemplate, /wc_get_checkout_url|\/checkout\/?['"]/i);
  assert.doesNotMatch(`${cartModule}\n${cartTemplate}`, /calculate_totals|set_total|set_subtotal/i);
});

test('empty Bag uses native Woo hooks and configured Shop routing', () => {
  const cart = readTheme('inc/cart.php');

  assert.match(cart, /woocommerce_cart_is_empty/);
  assert.match(cart, /woocommerce_return_to_shop_text/);
  assert.match(cart, /YOUR BAG IS EMPTY/);
  assert.match(cart, /CONTINUE SHOPPING/);
  assert.doesNotMatch(cart, /['"]\/shop\/?['"]/i);
  assert.equal(existsSync(resolve(themeRoot, 'woocommerce', 'cart', 'cart-empty.php')), false, 'native cart-empty.php should remain authoritative');
});

test('Cart stylesheet is conditional, token-driven, responsive, and sticky only on desktop', () => {
  const functions = readTheme('functions.php');
  const assets = readTheme('inc/assets.php');
  const css = readTheme('assets/css/cart.css');

  assert.match(functions, /inc[\\/'].*cart\.php/);
  assert.match(assets, /is_statement_cart\s*\(/);
  assert.match(assets, /assets\/css\/cart\.css/);
  assert.equal(balancedBraces(css), true, 'cart.css braces are unbalanced');
  assert.match(css, /var\(--wp--preset--/);
  assert.match(css, /grid-template-columns/);
  assert.match(css, /@media\s*\(min-width:/);
  assert.match(css, /position\s*:\s*sticky/);
  assert.doesNotMatch(css, /#[0-9a-f]{3,8}\b|@import|https?:\/\/|url\s*\(|gradient|box-shadow|border-radius\s*:\s*999/i);
});

test('M8 adds no JavaScript or extra Cart override surface', () => {
  const scripts = walk(themeRoot)
    .filter((path) => extname(path).toLowerCase() === '.js')
    .map((path) => relative(themeRoot, path).replaceAll('\\', '/'));
  const overrides = walk(resolve(themeRoot, 'woocommerce'))
    .filter((path) => extname(path).toLowerCase() === '.php')
    .map((path) => relative(resolve(themeRoot, 'woocommerce'), path).replaceAll('\\', '/'))
    .sort();

  assert.ok(scripts.includes('assets/js/navigation.js'));
  assert.deepEqual(
    overrides.filter((path) => !path.startsWith('checkout/')),
    ['cart/cart.php', 'content-product.php', 'content-single-product.php'],
  );
});

test('M8 runtime remains inside Cart and Bag scope', () => {
  const files = [
    readPlugin('src/Cart/Integrity.php'),
    readTheme('inc/cart.php'),
    readTheme('assets/css/cart.css'),
    readTheme('woocommerce/cart/cart.php'),
  ].join('\n');

  assert.doesNotMatch(files, /register_rest_route|wp_ajax_|wp_schedule|as_schedule_|magic[_ -]?link|access[_ -]?(?:token|session)|payment[_ -]?gateway|shipping[_ -]?(?:method|zone)|checkout[_ -]?(?:field|template)|order[_ -]?(?:meta|hook)|newsletter|custom[_ -]?table|production[_ -]?(?:cap|total)|restock/i);
});
