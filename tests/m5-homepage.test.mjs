import assert from 'node:assert/strict';
import { existsSync, readFileSync, readdirSync } from 'node:fs';
import { extname, relative, resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const themeRoot = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');
const pluginRoot = resolve(root, 'wp-content', 'plugins', 'statement-collector-core');
const requiredThemeFiles = [
  'front-page.php',
  'assets/css/home.css',
  'inc/home.php',
  'template-parts/home/hero.php',
  'template-parts/home/active-drop.php',
  'template-parts/home/editorial.php',
  'template-parts/home/products.php',
  'template-parts/home/principle.php',
  'template-parts/home/archive-link.php',
];

function readTheme(path) {
  return readFileSync(resolve(themeRoot, path), 'utf8');
}

function readPlugin(path) {
  return readFileSync(resolve(pluginRoot, path), 'utf8');
}

function walk(path, output = []) {
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

test('M5 focused homepage and Public API files exist', () => {
  for (const path of requiredThemeFiles) {
    assert.equal(existsSync(resolve(themeRoot, path)), true, `missing M5 theme file: ${path}`);
  }
  assert.equal(existsSync(resolve(pluginRoot, 'src', 'PublicApi.php')), true, 'missing core PublicApi.php');
});

test('front-page composition delegates each editorial section to focused template parts', () => {
  const frontPage = readTheme('front-page.php');

  assert.match(frontPage, /get_header\s*\(/);
  assert.match(frontPage, /<main\s+id=["']primary["']/i);
  assert.match(frontPage, /get_footer\s*\(/);
  for (const part of ['hero', 'active-drop', 'editorial', 'products', 'principle', 'archive-link']) {
    assert.match(frontPage, new RegExp(`get_template_part\\(\\s*['"]template-parts/home/${part}['"]`));
  }
  assert.doesNotMatch(frontPage, /<section\b/i, 'section markup belongs in template parts');
});

test('core Public API owns read-only public eligibility and canonical Drop resolution', () => {
  const entrypoint = readPlugin('statement-collector-core.php');
  const publicApi = readPlugin('src/PublicApi.php');
  const themeSource = walk(themeRoot)
    .filter((path) => extname(path).toLowerCase() === '.php')
    .map((path) => readFileSync(path, 'utf8'))
    .join('\n');

  assert.match(entrypoint, /src[\\/]PublicApi\.php/);
  assert.match(publicApi, /namespace\s+Statement\\Collector\\Core\s*;/);
  assert.match(publicApi, /final\s+class\s+PublicApi/);
  assert.match(publicApi, /Metadata::get_release_state\s*\(/);
  assert.match(publicApi, /Metadata::get_release_owner\s*\(/);
  assert.match(publicApi, /ReleaseState::LIVE/);
  assert.match(publicApi, /Taxonomy::KEY/);
  assert.match(publicApi, /get_the_terms\s*\(/);
  assert.doesNotMatch(publicApi, /update_meta_data|delete_meta_data|->save\s*\(|wp_set_object_terms|wp_delete_object_term/i);
  assert.match(themeSource, /Statement\\Collector\\Core\\PublicApi/);
  assert.doesNotMatch(themeSource, /_statement_release_state|_statement_edition_label|ReleaseState::|statement_drop/);
});

test('hero uses the native featured image and only links to a valid LIVE destination', () => {
  const hero = readTheme('template-parts/home/hero.php');

  assert.match(hero, /has_post_thumbnail\s*\(/);
  assert.match(hero, /get_the_post_thumbnail\s*\(/);
  assert.match(hero, /['"]loading['"]\s*=>\s*['"]eager['"]/);
  assert.match(hero, /['"]fetchpriority['"]\s*=>\s*['"]high['"]/);
  assert.match(hero, /ENTER DROP/);
  assert.match(hero, /if\s*\(\s*[^)]*drop_url/s);
  assert.doesNotMatch(hero, /https?:\/\/|placeholder|carousel|slider|DROP\s*001/i);
});

test('homepage selection uses bounded WooCommerce objects and renders restrained product data', () => {
  const home = readTheme('inc/home.php');
  const products = readTheme('template-parts/home/products.php');
  const combined = `${home}\n${products}`;

  assert.match(home, /wc_get_products\s*\(/);
  assert.match(home, /['"]status['"]\s*=>\s*['"]publish['"]/);
  assert.match(home, /['"]visibility['"]\s*=>\s*['"]visible['"]/);
  assert.match(home, /HOME_CANDIDATE_LIMIT\s*=\s*24/);
  assert.match(home, /['"]limit['"]\s*=>\s*HOME_CANDIDATE_LIMIT/);
  assert.match(home, /HOME_PRODUCT_LIMIT\s*=\s*4/);
  assert.match(home, /PublicApi::is_publicly_live\s*\(/);
  assert.match(home, /PublicApi::get_drop\s*\(/);
  for (const method of ['get_image_id', 'get_image', 'get_name', 'get_permalink', 'get_price_html']) {
    assert.match(products, new RegExp(`${method}\\s*\\(`));
  }
  assert.doesNotMatch(combined, /\$wpdb|WP_Query|SELECT\s+.+FROM|add[_ -]?to[_ -]?cart|rating|review|quick[_ -]?(?:add|view)|swatch|stock[_ -]?count/i);
});

test('editorial, principle, and archive sections use only approved native content', () => {
  const editorial = readTheme('template-parts/home/editorial.php');
  const principle = readTheme('template-parts/home/principle.php');
  const archive = readTheme('template-parts/home/archive-link.php');
  const home = readTheme('inc/home.php');

  assert.match(editorial, /the_content\s*\(/);
  assert.match(principle, /Crafted\. Limited\. Never Restocked\./);
  assert.match(home, /get_page_by_path\(\s*'archive'\s*,\s*OBJECT\s*,\s*array\(\s*'page'\s*\)\s*\)/s);
  assert.match(home, /get_post_status\s*\(/);
  assert.match(archive, /PAST RELEASES/);
  assert.match(archive, /ARCHIVE/);
  assert.doesNotMatch(archive, /ARCHIVED|SOLD_OUT/i);
  assert.doesNotMatch(home.slice(home.indexOf('function get_home_archive_url')), /wc_get_products/i);
});

test('home stylesheet is conditional, token-driven, responsive, and adds no JavaScript', () => {
  const assets = readTheme('inc/assets.php');
  const css = readTheme('assets/css/home.css');
  const scripts = walk(themeRoot)
    .filter((path) => extname(path).toLowerCase() === '.js')
    .map((path) => relative(themeRoot, path).replaceAll('\\', '/'));

  assert.match(assets, /is_front_page\s*\(/);
  assert.match(assets, /assets\/css\/home\.css/);
  assert.equal(balancedBraces(css), true, 'home.css braces are unbalanced');
  assert.match(css, /var\(--wp--preset--/);
  assert.match(css, /svh/);
  assert.match(css, /grid-template-columns\s*:\s*1fr/);
  assert.match(css, /@media\s*\(min-width:/);
  assert.doesNotMatch(css, /#[0-9a-f]{3,8}\b|@import|https?:\/\/|url\s*\(|(?:\.woocommerce|\.wc-block|woocommerce-)/i);
  assert.deepEqual(scripts, ['assets/js/navigation.js']);
});

test('M5 runtime remains absence-safe, privacy-safe, and inside homepage scope', () => {
  const homePhp = readTheme('inc/home.php');
  const themeSource = walk(themeRoot)
    .filter((path) => ['.php', '.css', '.js'].includes(extname(path).toLowerCase()))
    .map((path) => readFileSync(path, 'utf8'))
    .join('\n');

  assert.match(homePhp, /class_exists\(\s*['"]Statement\\Collector\\Core\\PublicApi['"]\s*\)/);
  assert.match(homePhp, /function_exists\(\s*['"]wc_get_products['"]\s*\)/);
  assert.doesNotMatch(themeSource, /PRIVATE_ACCESS|_statement_release_state|register_rest_route|wp_ajax_|Action Scheduler|magic[_ -]?link|access[_ -]?session/i);
  assert.doesNotMatch(themeSource, /elementor|\bACF\b|carousel|slider|animation[_ -]?library|homepage[_ -]?(?:option|setting)|mini[-_ ]?cart|cart[_ -]?count|checkout/i);
  assert.doesNotMatch(themeSource, /template-(?:product|shop)|single-product|archive-product/i);
});
