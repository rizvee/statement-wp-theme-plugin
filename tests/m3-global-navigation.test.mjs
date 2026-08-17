import assert from 'node:assert/strict';
import { existsSync, readFileSync, readdirSync } from 'node:fs';
import { extname, relative, resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const themeRoot = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');
const requiredFiles = [
  'assets/css/header.css',
  'assets/css/footer.css',
  'assets/js/navigation.js',
  'inc/navigation.php',
  'template-parts/header/site-header.php',
  'template-parts/header/mobile-navigation.php',
  'template-parts/header/search-dialog.php',
  'template-parts/footer/site-footer.php',
];

function read(path) {
  return readFileSync(resolve(themeRoot, path), 'utf8');
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

test('M3 runtime files exist', () => {
  for (const path of requiredFiles) {
    assert.equal(existsSync(resolve(themeRoot, path)), true, `missing M3 file: ${path}`);
  }
});

test('theme setup registers only the approved global navigation capabilities', () => {
  const setup = read('inc/setup.php');

  assert.match(setup, /add_theme_support\(\s*'custom-logo'/);
  assert.match(setup, /'flex-height'\s*=>\s*true/);
  assert.match(setup, /'flex-width'\s*=>\s*true/);
  assert.match(setup, /register_nav_menus\s*\(/);
  assert.match(setup, /['"]primary['"]\s*=>/);
  assert.match(setup, /['"]footer['"]\s*=>/);
});

test('header delegates visible UI and preserves native site identity and menus', () => {
  const header = read('header.php');
  const siteHeader = read('template-parts/header/site-header.php');
  const navigation = read('inc/navigation.php');
  const combined = `${siteHeader}\n${navigation}`;

  assert.match(header, /get_template_part\(\s*'template-parts\/header\/site-header'/);
  assert.match(siteHeader, /<header\b/i);
  assert.match(siteHeader, /<nav\b/i);
  assert.match(siteHeader, /wp_nav_menu\s*\(/);
  assert.match(siteHeader, /['"]theme_location['"]\s*=>\s*['"]primary['"]/);
  assert.match(siteHeader, /['"]fallback_cb['"]\s*=>\s*false/);
  assert.match(combined, /has_custom_logo\s*\(/);
  assert.match(combined, /the_custom_logo\s*\(/);
  assert.match(combined, /get_bloginfo\(\s*'name'\s*\)/);
  assert.doesNotMatch(combined, /DROP\s*001/i);
});

test('mobile navigation reuses the primary menu in an accessible dialog', () => {
  const siteHeader = read('template-parts/header/site-header.php');
  const mobile = read('template-parts/header/mobile-navigation.php');

  assert.match(siteHeader, /<button\b[^>]*aria-controls=["']statement-mobile-navigation["'][^>]*aria-expanded=["']false["']/is);
  assert.match(mobile, /<dialog\b[^>]*id=["']statement-mobile-navigation["']/is);
  assert.match(mobile, /wp_nav_menu\s*\(/);
  assert.match(mobile, /['"]theme_location['"]\s*=>\s*['"]primary['"]/);
  assert.match(mobile, /['"]fallback_cb['"]\s*=>\s*false/);
  assert.match(mobile, /<button\b[^>]*data-dialog-close/is);
  assert.match(mobile, /<nav\b/i);
});

test('search uses a native, labelled WordPress search dialog without live requests', () => {
  const search = read('template-parts/header/search-dialog.php');

  assert.match(search, /<dialog\b[^>]*id=["']statement-search-dialog["']/is);
  assert.match(search, /<form\b[^>]*role=["']search["'][^>]*method=["']get["']/is);
  assert.match(search, /home_url\(\s*['"]\/["']\s*\)/);
  assert.match(search, /<label\b[^>]*for=["']statement-search-field["']/is);
  assert.match(search, /<input\b[^>]*type=["']search["'][^>]*name=["']s["']/is);
  assert.doesNotMatch(search, /wp_ajax|fetch\s*\(|XMLHttpRequest|autocomplete/i);
});

test('commerce utility helpers use WooCommerce APIs and degrade without WooCommerce', () => {
  const navigation = read('inc/navigation.php');
  const templates = [
    read('template-parts/header/site-header.php'),
    read('template-parts/header/mobile-navigation.php'),
  ].join('\n');

  assert.match(navigation, /function_exists\(\s*'wc_get_page_permalink'\s*\)/);
  assert.match(navigation, /wc_get_page_id\(\s*'myaccount'\s*\)/);
  assert.match(navigation, /wc_get_page_permalink\(\s*'myaccount'\s*\)/);
  assert.match(navigation, /function_exists\(\s*'wc_get_cart_url'\s*\)/);
  assert.match(navigation, /wc_get_page_id\(\s*'cart'\s*\)/);
  assert.match(navigation, /wc_get_cart_url\s*\(/);
  assert.doesNotMatch(`${navigation}\n${templates}`, /["']\/(?:cart|my-account)\/?["']/i);
  assert.doesNotMatch(`${navigation}\n${templates}`, /cart[_ -]?(?:count|fragment|contents)|mini[-_ ]?cart/i);
});

test('footer delegates a restrained brand and WordPress-driven menu', () => {
  const footer = read('footer.php');
  const siteFooter = read('template-parts/footer/site-footer.php');

  assert.match(footer, /get_template_part\(\s*'template-parts\/footer\/site-footer'/);
  assert.ok(footer.indexOf('get_template_part') < footer.indexOf('wp_footer'), 'footer component must load before wp_footer()');
  assert.match(siteFooter, /<footer\b/i);
  assert.match(siteFooter, /wp_nav_menu\s*\(/);
  assert.match(siteFooter, /['"]theme_location['"]\s*=>\s*['"]footer['"]/);
  assert.match(siteFooter, /['"]fallback_cb['"]\s*=>\s*false/);
  assert.match(siteFooter, /Crafted\. Not Mass Made\./i);
  assert.doesNotMatch(siteFooter, /mailto:|tel:|newsletter|street address|privacy policy|terms of service/i);
});

test('local assets are enqueued and dialog JavaScript remains interaction-only', () => {
  const assets = read('inc/assets.php');
  const script = read('assets/js/navigation.js');
  const headerCss = read('assets/css/header.css');
  const footerCss = read('assets/css/footer.css');
  const css = `${headerCss}\n${footerCss}`;

  assert.match(assets, /assets\/css\/header\.css/);
  assert.match(assets, /assets\/css\/footer\.css/);
  assert.match(assets, /wp_enqueue_script\s*\(/);
  assert.match(assets, /assets\/js\/navigation\.js/);
  assert.match(script, /showModal\s*\(/);
  assert.match(script, /\.close\s*\(/);
  assert.match(script, /aria-expanded/);
  assert.match(script, /\.focus\s*\(/);
  assert.doesNotMatch(`${assets}\n${script}`, /jquery|fetch\s*\(|XMLHttpRequest|localStorage|wp_ajax|cart[_ -]?(?:count|fragment|contents)/i);
  assert.equal(balancedBraces(headerCss), true, 'header.css braces are unbalanced');
  assert.equal(balancedBraces(footerCss), true, 'footer.css braces are unbalanced');
  assert.doesNotMatch(css, /#[0-9a-f]{3,8}\b|@import|(?:\.woocommerce|\.wc-block|woocommerce-)/i);
  assert.doesNotMatch(`${css}\n${script}`, /https?:\/\//i);
});

test('M3 runtime stays within global-shell scope', () => {
  const m3Files = [
    'header.php',
    'footer.php',
    'inc/setup.php',
    'inc/navigation.php',
    'assets/css/header.css',
    'assets/css/footer.css',
    'assets/js/navigation.js',
    'template-parts/header/mobile-navigation.php',
    'template-parts/header/search-dialog.php',
    'template-parts/header/site-header.php',
    'template-parts/footer/site-footer.php',
  ];
  const source = m3Files
    .map((path) => read(path))
    .join('\n');
  const forbidden = [
    /register_post_type\s*\(/i,
    /register_taxonomy\s*\(/i,
    /register_rest_route\s*\(/i,
    /wp_ajax_/i,
    /wp_schedule_/i,
    /add[_ -]?to[_ -]?cart/i,
    /private[_ -]?access/i,
    /reminder[_ -]?email/i,
    /checkout/i,
    /newsletter/i,
  ];

  for (const signal of forbidden) assert.doesNotMatch(source, signal);
  const unexpectedJavaScript = walk(themeRoot)
    .filter((path) => extname(path).toLowerCase() === '.js')
    .map((path) => relative(themeRoot, path).replaceAll('\\', '/'))
    .filter((path) => !['assets/js/navigation.js', 'assets/js/hero-slider.js'].includes(path));
  assert.deepEqual(unexpectedJavaScript, []);
});
