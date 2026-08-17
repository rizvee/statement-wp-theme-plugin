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

test('M6 focused catalog files exist', () => {
  const required = [
    resolve(pluginRoot, 'src', 'Catalog', 'Visibility.php'),
    resolve(themeRoot, 'inc', 'catalog.php'),
    resolve(themeRoot, 'taxonomy-statement_drop.php'),
    resolve(themeRoot, 'template-parts', 'product', 'card.php'),
    resolve(themeRoot, 'woocommerce', 'content-product.php'),
    resolve(themeRoot, 'assets', 'css', 'product-card.css'),
    resolve(themeRoot, 'assets', 'css', 'catalog.css'),
  ];

  for (const path of required) assert.equal(existsSync(path), true, `missing M6 file: ${relative(root, path)}`);
});

test('Core applies canonical LIVE visibility at the WooCommerce main-query boundary', () => {
  const entrypoint = readPlugin('statement-collector-core.php');
  const plugin = readPlugin('src/Plugin.php');
  const visibility = readPlugin('src/Catalog/Visibility.php');

  assert.match(entrypoint, /src[\\/]Catalog[\\/]Visibility\.php/);
  assert.match(plugin, /Catalog\\Visibility::boot\s*\(/);
  assert.match(visibility, /woocommerce_product_query/);
  assert.match(visibility, /Metadata::RELEASE_STATE_KEY/);
  assert.match(visibility, /ReleaseState::LIVE/);
  assert.match(visibility, /is_main_query\s*\(/);
  assert.match(visibility, /is_post_type_archive\s*\(\s*['"]product['"]/);
  assert.match(visibility, /is_tax\s*\(\s*Taxonomy::KEY/);
  assert.match(visibility, /get\s*\(\s*['"]meta_query['"]\s*\)/);
  assert.match(visibility, /set\s*\(\s*['"]meta_query['"]/);
  assert.doesNotMatch(visibility, /the_posts|get_posts\s*\(|wc_get_products\s*\(|set_stock|update_meta|wp_set_object_terms/i);
  assert.match(visibility, /rest_pre_echo_response/, 'Store API serialization must retain a fail-closed privacy boundary.');
});

test('Shop remains on the native WooCommerce archive with restrained loop UI', () => {
  const catalog = readTheme('inc/catalog.php');

  assert.equal(existsSync(resolve(themeRoot, 'woocommerce', 'archive-product.php')), false, 'native archive-product.php should remain authoritative');
  assert.match(catalog, /woocommerce_before_shop_loop/);
  assert.match(catalog, /woocommerce_result_count/);
  assert.match(catalog, /woocommerce_catalog_ordering/);
  assert.match(catalog, /woocommerce_sidebar/);
  assert.match(catalog, /woocommerce_get_sidebar/);
  assert.match(catalog, /woocommerce_no_products_found/);
  assert.match(catalog, /NO CURRENT RELEASE/);
  assert.doesNotMatch(catalog, /WP_Query|wc_get_products|posts_per_page|paged\s*=>/);
});

test('Drop archive uses native taxonomy data and WooCommerce loop pagination without a parallel query', () => {
  const drop = readTheme('taxonomy-statement_drop.php');

  assert.match(drop, /get_queried_object\s*\(/);
  assert.match(drop, /woocommerce_product_loop\s*\(/);
  assert.match(drop, /woocommerce_product_loop_start\s*\(/);
  assert.match(drop, /wc_get_template_part\s*\(\s*['"]content['"]\s*,\s*['"]product['"]\s*\)/);
  assert.match(drop, /woocommerce_after_shop_loop/);
  assert.match(drop, /woocommerce_no_products_found/);
  assert.doesNotMatch(drop, /WP_Query|wc_get_products|get_posts|query_posts|posts_per_page|Drop\s*00\d/i);
});

test('Home, Shop, and Drop share one restrained product card', () => {
  const homeProducts = readTheme('template-parts/home/products.php');
  const loopOverride = readTheme('woocommerce/content-product.php');
  const card = readTheme('template-parts/product/card.php');

  assert.match(homeProducts, /get_template_part\(\s*['"]template-parts\/product\/card['"]/);
  assert.match(loopOverride, /@version\s+9\.4\.0/);
  assert.match(loopOverride, /wc_product_class\s*\(/);
  assert.match(loopOverride, /get_template_part\(\s*['"]template-parts\/product\/card['"]/);
  for (const method of ['get_image_id', 'get_image', 'get_name', 'get_permalink', 'get_price_html']) {
    assert.match(card, new RegExp(`${method}\\s*\\(`));
  }
  assert.match(card, /Statement\\Collector\\Core\\PublicApi/);
  assert.doesNotMatch(`${homeProducts}\n${loopOverride}\n${card}`, /add[_ -]?to[_ -]?cart|rating|review|sale[_ -]?badge|quick[_ -]?(?:add|view)|wishlist|compare|swatch|stock[_ -]?(?:count|quantity)|production[_ -]?(?:total|quantity)/i);
});

test('Catalog assets are conditional, token-driven, responsive, and add no JavaScript', () => {
  const assets = readTheme('inc/assets.php');
  const cardCss = readTheme('assets/css/product-card.css');
  const catalogCss = readTheme('assets/css/catalog.css');
  const css = `${cardCss}\n${catalogCss}`;
  const scripts = walk(themeRoot)
    .filter((path) => extname(path).toLowerCase() === '.js')
    .map((path) => relative(themeRoot, path).replaceAll('\\', '/'));

  assert.match(assets, /assets\/css\/product-card\.css/);
  assert.match(assets, /assets\/css\/catalog\.css/);
  assert.match(assets, /is_statement_catalog\s*\(/);
  assert.equal(balancedBraces(cardCss), true, 'product-card.css braces are unbalanced');
  assert.equal(balancedBraces(catalogCss), true, 'catalog.css braces are unbalanced');
  assert.match(css, /var\(--wp--preset--/);
  assert.match(css, /aspect-ratio/);
  assert.match(catalogCss, /grid-template-columns\s*:\s*1fr/);
  assert.match(catalogCss, /repeat\(2\s*,\s*minmax\(0\s*,\s*1fr\)\)/);
  assert.match(catalogCss, /repeat\(3\s*,\s*minmax\(0\s*,\s*1fr\)\)/);
  assert.ok(scripts.includes('assets/js/navigation.js'));
});

test('Theme does not interpret release metadata or hide catalog results after the query', () => {
  const source = walk(themeRoot)
    .filter((path) => ['.php', '.css', '.js'].includes(extname(path).toLowerCase()))
    .map((path) => readFileSync(path, 'utf8'))
    .join('\n');

  assert.doesNotMatch(source, /PRIVATE_ACCESS|_statement_release_state|_statement_edition_label|ReleaseState::/);
  assert.doesNotMatch(source, /the_posts|array_filter\s*\(|display\s*:\s*none[^}]*product|data-product-id|data-release-state|application\/ld\+json/i);
});

test('M6 runtime remains server-rendered and inside catalog scope', () => {
  const m6Files = [
    'inc/catalog.php',
    'taxonomy-statement_drop.php',
    'template-parts/product/card.php',
    'woocommerce/content-product.php',
    'assets/css/product-card.css',
    'assets/css/catalog.css',
  ];
  const themeSource = m6Files
    .map((path) => readTheme(path))
    .join('\n');
  const visibility = readPlugin('src/Catalog/Visibility.php');
  const source = `${themeSource}\n${visibility}`;

  assert.doesNotMatch(source, /single-product|variation[_ -]?(?:selector|purchase)|mini[-_ ]?cart|cart[_ -]?count|checkout|magic[_ -]?link|access[_ -]?session|register_rest_route|wp_ajax_|wp_schedule|as_schedule_|wishlist|quick[_ -]?view|facet|campaign[_ -]?metadata|elementor|\bACF\b/i);
  assert.doesNotMatch(source, /fetch\s*\(|XMLHttpRequest|catalog\.js|slider|carousel/i);
});
