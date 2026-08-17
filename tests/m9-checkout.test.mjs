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

test('M9 adds only the focused Checkout presentation files', () => {
  for (const path of ['assets/css/checkout.css', 'inc/checkout.php', 'woocommerce/checkout/form-checkout.php']) {
    assert.equal(existsSync(resolve(themeRoot, path)), true, `missing M9 theme file: ${path}`);
  }

  const overrides = walk(resolve(themeRoot, 'woocommerce'))
    .filter((path) => extname(path).toLowerCase() === '.php')
    .map((path) => relative(resolve(themeRoot, 'woocommerce'), path).replaceAll('\\', '/'))
    .sort();

  assert.deepEqual(overrides, [
    'cart/cart.php',
    'checkout/form-checkout.php',
    'content-product.php',
    'content-single-product.php',
  ]);
});

test('existing Cart Integrity remains the single final checkout lifecycle gate', () => {
  const integrity = readPlugin('src/Cart/Integrity.php');
  const pluginFiles = walk(pluginRoot).map((path) => relative(pluginRoot, path).replaceAll('\\', '/'));

  assert.match(integrity, /woocommerce_check_cart_items/);
  assert.match(integrity, /Metadata::get_release_owner\s*\(/);
  assert.match(integrity, /ReleaseState::LIVE/);
  assert.match(integrity, /WOOCOMMERCE_CHECKOUT/);
  assert.match(integrity, /wc_add_notice\s*\(\s*self::REMOVAL_NOTICE\s*,\s*\$notice_type/);
  assert.equal(pluginFiles.some((path) => /src\/Checkout\/Validation\.php$/i.test(path)), false);
  assert.doesNotMatch(integrity, /set_stock_quantity|set_stock_status|update_meta_data|wc_create_order|new\s+WC_Order|process_payment|shipping_rate/i);
});

test('Checkout integration is scoped to the normal cart checkout and removes only coupon presentation', () => {
  const functions = readTheme('functions.php');
  const checkout = readTheme('inc/checkout.php');
  const assets = readTheme('inc/assets.php');

  assert.match(functions, /inc[\\/'].*checkout\.php/);
  assert.match(checkout, /function\s+is_statement_checkout\s*\(/);
  assert.match(checkout, /is_checkout\s*\(/);
  assert.match(checkout, /is_wc_endpoint_url\(\s*'order-pay'\s*\)/);
  assert.match(checkout, /is_wc_endpoint_url\(\s*'order-received'\s*\)/);
  assert.match(checkout, /remove_action\(\s*'woocommerce_before_checkout_form'\s*,\s*'woocommerce_checkout_coupon_form'/);
  assert.match(checkout, /woocommerce_order_button_text/);
  assert.doesNotMatch(checkout, /woocommerce_coupons_enabled|remove_coupon|calculate_totals|Statement\\Collector\\Core|_statement_(?:release_state|edition_label)/i);
  assert.match(assets, /is_statement_checkout\s*\(/);
  assert.match(assets, /assets\/css\/checkout\.css/);
});

test('Checkout override preserves the current native WooCommerce lifecycle', () => {
  const template = readTheme('woocommerce/checkout/form-checkout.php');

  assert.match(template, /@version\s+9\.4\.0/);
  assert.match(template, /woocommerce_before_checkout_form/);
  assert.match(template, /is_registration_required\s*\(/);
  assert.match(template, /is_registration_enabled\s*\(/);
  assert.match(template, /<form\b[^>]*method="post"[^>]*action="<\?php echo esc_url\( wc_get_checkout_url\(\) \); \?>"[^>]*enctype="multipart\/form-data"/is);
  for (const hook of [
    'woocommerce_checkout_before_customer_details',
    'woocommerce_checkout_billing',
    'woocommerce_checkout_shipping',
    'woocommerce_checkout_after_customer_details',
    'woocommerce_checkout_before_order_review_heading',
    'woocommerce_checkout_before_order_review',
    'woocommerce_checkout_order_review',
    'woocommerce_checkout_after_order_review',
    'woocommerce_after_checkout_form',
  ]) {
    assert.ok(template.includes(hook), `missing native checkout hook: ${hook}`);
  }
  assert.match(template, /esc_html_e\(\s*'CHECKOUT'/);
  assert.match(template, /esc_html_e\(\s*'ORDER SUMMARY'/);
  assert.doesNotMatch(template, /woocommerce_order_review\s*\(|woocommerce_checkout_payment\s*\(|wc_create_order|new\s+WC_Order/i);
});

test('Checkout CSS is token-driven, responsive, and contains no brittle commerce fabrication', () => {
  const css = readTheme('assets/css/checkout.css');

  assert.equal(balancedBraces(css), true, 'checkout.css braces are unbalanced');
  assert.match(css, /var\(--wp--preset--/);
  assert.match(css, /grid-template-columns/);
  assert.match(css, /@media\s*\(min-width:/);
  assert.match(css, /#place_order/);
  assert.match(css, /\.woocommerce-invalid/);
  assert.doesNotMatch(css, /#[0-9a-f]{3,8}\b|@import|https?:\/\/|url\s*\(|gradient|box-shadow|border-radius\s*:\s*999/i);
});

test('M9 introduces no first-party Checkout JavaScript', () => {
  const scripts = walk(themeRoot)
    .filter((path) => extname(path).toLowerCase() === '.js')
    .map((path) => relative(themeRoot, path).replaceAll('\\', '/'));

  assert.ok(scripts.includes('assets/js/navigation.js'));
  assert.equal(existsSync(resolve(themeRoot, 'assets/js/checkout.js')), false);
  assert.equal(existsSync(resolve(themeRoot, 'assets/js/payment.js')), false);
  assert.equal(existsSync(resolve(themeRoot, 'assets/js/address.js')), false);
});

test('M9 theme stays presentation-only and keeps private lifecycle data out of output', () => {
  const source = [
    readTheme('inc/checkout.php'),
    readTheme('assets/css/checkout.css'),
    readTheme('woocommerce/checkout/form-checkout.php'),
  ].join('\n');

  assert.doesNotMatch(source, /PRIVATE_ACCESS|_statement_release_state|_statement_edition_label|ReleaseState::/);
  assert.doesNotMatch(source, /register_rest_route|wp_ajax_|fetch\s*\(|XMLHttpRequest|localStorage|StoreApi|new\s+WC_Order|wc_create_order|process_payment|payment_complete|shipping[_ -]?rate|MyPost|ReachShip|Stripe|WooPayments|collector[_ -]?number|certificate|archive/i);
  assert.doesNotMatch(source, /FREE SHIPPING|Arrives in|Express available|production[_ -]?(?:cap|total)|200 pieces|restock/i);
});
