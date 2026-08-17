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

test('M7 focused Core and product-detail files exist', () => {
  const required = [
    resolve(pluginRoot, 'src', 'Product', 'Access.php'),
    resolve(themeRoot, 'inc', 'product.php'),
    resolve(themeRoot, 'assets', 'css', 'product.css'),
    resolve(themeRoot, 'woocommerce', 'content-single-product.php'),
    resolve(themeRoot, 'template-parts', 'product', 'gallery.php'),
    resolve(themeRoot, 'template-parts', 'product', 'summary.php'),
    resolve(themeRoot, 'template-parts', 'product', 'details.php'),
  ];

  for (const path of required) assert.equal(existsSync(path), true, `missing M7 file: ${relative(root, path)}`);
});

test('Core owns direct product access and crafted Add-to-Cart protection', () => {
  const entrypoint = readPlugin('statement-collector-core.php');
  const plugin = readPlugin('src/Plugin.php');
  const access = readPlugin('src/Product/Access.php');

  assert.match(entrypoint, /src[\\/]Product[\\/]Access\.php/);
  assert.match(plugin, /Product\\Access::boot\s*\(/);
  assert.match(access, /template_redirect/);
  assert.match(access, /is_singular\s*\(\s*['"]product['"]/);
  assert.match(access, /set_404\s*\(/);
  assert.match(access, /status_header\s*\(\s*404\s*\)/);
  assert.match(access, /current_user_can\s*\(\s*['"]edit_post['"]/);
  assert.match(access, /is_preview\s*\(/);
  assert.match(access, /woocommerce_add_to_cart_validation/);
  assert.match(access, /Metadata::get_release_state\s*\(/);
  assert.match(access, /Metadata::get_release_owner\s*\(/);
  assert.match(access, /ReleaseState::LIVE/);
  assert.match(access, /wc_get_product\s*\(/);
  assert.match(access, /This piece is not currently available for purchase\./);
  assert.doesNotMatch(access, /wp_die|wp_safe_redirect|wp_redirect|set_stock|update_meta|PRIVATE_ACCESS.*notice|SOLD_OUT.*notice/i);
});

test('single-product override is minimal, versioned, and delegates focused components', () => {
  const template = readTheme('woocommerce/content-single-product.php');

  assert.equal(existsSync(resolve(themeRoot, 'woocommerce', 'single-product.php')), false, 'native single-product.php should remain authoritative');
  assert.match(template, /@version\s+3\.6\.0/);
  assert.match(template, /woocommerce_before_single_product/);
  assert.match(template, /woocommerce_after_single_product/);
  assert.match(template, /post_password_required\s*\(/);
  assert.match(template, /wc_product_class\s*\(/);
  for (const part of ['gallery', 'summary', 'details']) {
    assert.match(template, new RegExp(`get_template_part\\(\\s*['"]template-parts/product/${part}['"]`));
  }
  assert.doesNotMatch(template, /woocommerce_(?:single_product_summary|after_single_product_summary|before_single_product_summary)/);
});

test('product components use native Woo mechanics and legitimate product data', () => {
  const gallery = readTheme('template-parts/product/gallery.php');
  const summary = readTheme('template-parts/product/summary.php');
  const details = readTheme('template-parts/product/details.php');
  const source = `${gallery}\n${summary}\n${details}`;

  assert.match(gallery, /woocommerce_show_product_images\s*\(/);
  assert.match(summary, /PublicApi::get_drop\s*\(/);
  assert.match(summary, /PublicApi::get_edition_label\s*\(/);
  assert.match(summary, /get_name\s*\(/);
  assert.match(summary, /woocommerce_template_single_price\s*\(/);
  assert.match(summary, /woocommerce_template_single_excerpt\s*\(/);
  assert.match(summary, /woocommerce_template_single_add_to_cart\s*\(/);
  assert.match(details, /get_description\s*\(/);
  assert.match(details, /PRODUCT DETAILS/);
  assert.doesNotMatch(source, /get_gallery_image_ids|get_attachment|variation_id.*input|attributes.*json|production[_ -]?(?:total|quantity)|\b200\b/i);
});

test('single-product presentation uses conditional token-driven responsive CSS', () => {
  const functions = readTheme('functions.php');
  const assets = readTheme('inc/assets.php');
  const product = readTheme('inc/product.php');
  const css = readTheme('assets/css/product.css');

  assert.match(functions, /inc[\\/'].*product\.php/);
  assert.match(assets, /is_statement_product\s*\(/);
  assert.match(assets, /assets\/css\/product\.css/);
  assert.match(product, /woocommerce_product_single_add_to_cart_text/);
  assert.match(product, /ADD TO BAG/);
  assert.match(product, /remove_action\s*\(\s*['"]woocommerce_before_main_content['"]\s*,\s*['"]woocommerce_breadcrumb['"]/);
  assert.match(product, /remove_action\s*\(\s*['"]woocommerce_sidebar['"]\s*,\s*['"]woocommerce_get_sidebar['"]/);
  assert.equal(balancedBraces(css), true, 'product.css braces are unbalanced');
  assert.match(css, /var\(--wp--preset--/);
  assert.match(css, /scroll-snap-type/);
  assert.match(css, /position\s*:\s*sticky/);
  assert.match(css, /grid-template-columns/);
  assert.match(css, /@media\s*\(min-width:/);
  assert.doesNotMatch(css, /#[0-9a-f]{3,8}\b|@import|https?:\/\/|url\s*\(|gradient|border-radius\s*:\s*999/i);
});

test('Public API exposes canonical variation-safe edition labels read-only', () => {
  const publicApi = readPlugin('src/PublicApi.php');

  assert.match(publicApi, /function\s+get_edition_label\s*\(/);
  assert.match(publicApi, /Metadata::get_release_owner\s*\(/);
  assert.match(publicApi, /Metadata::get_edition_label\s*\(/);
  assert.doesNotMatch(publicApi, /update_meta_data|delete_meta_data|->save\s*\(|wp_set_object_terms/i);
});

test('M7 omits generic Woo single-product discovery and metadata UI', () => {
  const templates = [
    readTheme('woocommerce/content-single-product.php'),
    readTheme('template-parts/product/gallery.php'),
    readTheme('template-parts/product/summary.php'),
    readTheme('template-parts/product/details.php'),
  ].join('\n');

  assert.doesNotMatch(templates, /single_rating|reviews?|related[_ -]?products|upsell|single_meta|sku|categories|tags|sharing|product_data_tabs|sale_flash|wishlist|recommendations?/i);
});

test('theme remains release-blind and adds no first-party product JavaScript', () => {
  const source = walk(themeRoot)
    .filter((path) => ['.php', '.css', '.js'].includes(extname(path).toLowerCase()))
    .map((path) => readFileSync(path, 'utf8'))
    .join('\n');
  const scripts = walk(themeRoot)
    .filter((path) => extname(path).toLowerCase() === '.js')
    .map((path) => relative(themeRoot, path).replaceAll('\\', '/'));

  assert.doesNotMatch(source, /PRIVATE_ACCESS|_statement_release_state|_statement_edition_label|ReleaseState::/);
  assert.ok(scripts.includes('assets/js/navigation.js'));
  assert.doesNotMatch(source, /swiper|slick|custom[_ -]?variation|size[_ -]?(?:pill|button)|floating[_ -]?(?:bag|cart)/i);
});

test('M7 runtime remains inside product-detail scope', () => {
  const access = readPlugin('src/Product/Access.php');
  const productThemeFiles = [
    'inc/product.php',
    'assets/css/product.css',
    'woocommerce/content-single-product.php',
    'template-parts/product/gallery.php',
    'template-parts/product/summary.php',
    'template-parts/product/details.php',
  ];
  const source = `${access}\n${productThemeFiles.map((path) => readTheme(path)).join('\n')}`;

  assert.doesNotMatch(source, /mini[-_ ]?cart|cart[_ -]?drawer|checkout|magic[_ -]?link|access[_ -]?session|register_rest_route|wp_ajax_|wp_schedule|as_schedule_|custom[_ -]?table|archive[_ -]?(?:page|template)|elementor|\bACF\b/i);
});
