import assert from 'node:assert/strict';
import { existsSync, readFileSync, readdirSync } from 'node:fs';
import test from 'node:test';
import { extname, resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const themeRoot = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');
const requiredPalette = new Map([
  ['gallery-ivory', '#F3F0EA'],
  ['warm-white', '#FAF9F6'],
  ['ink-navy', '#172131'],
  ['near-black', '#090B0F'],
  ['soft-graphite', '#292B2F'],
  ['border-grey', '#D8D4CC'],
  ['brass', '#9A7A45'],
]);

function read(relativePath) {
  return readFileSync(resolve(themeRoot, relativePath), 'utf8');
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

test('theme.json v3 defines the restrained Statement token contract', () => {
  const path = resolve(themeRoot, 'theme.json');
  assert.equal(existsSync(path), true, 'missing theme.json');
  const config = JSON.parse(readFileSync(path, 'utf8'));

  assert.equal(config.version, 3);
  const palette = new Map(config.settings?.color?.palette?.map(({ slug, color }) => [slug, color.toUpperCase()]));
  assert.equal(palette.size, 7, 'palette should contain exactly seven approved colors');
  for (const [slug, color] of requiredPalette) assert.equal(palette.get(slug), color);
  assert.equal(config.settings?.color?.custom, false);
  assert.equal(config.settings?.color?.defaultPalette, false);

  const fonts = config.settings?.typography?.fontFamilies ?? [];
  assert.ok(fonts.some(({ fontFamily }) => fontFamily.includes('Instrument Serif') && fontFamily.includes('Iowan Old Style')));
  assert.ok(fonts.some(({ fontFamily }) => fontFamily.includes('Inter') && fontFamily.includes('Helvetica Neue')));
  assert.ok((config.settings?.typography?.fontSizes ?? []).length >= 5 && config.settings.typography.fontSizes.length <= 6, 'typography scale should stay restrained');
  assert.ok((config.settings?.spacing?.spacingSizes ?? []).length >= 6 && config.settings.spacing.spacingSizes.length <= 8, 'spacing scale should stay restrained');
  assert.equal(config.settings?.layout?.contentSize, '760px');
  assert.equal(config.settings?.layout?.wideSize, '1440px');
});

test('base and layout CSS provide accessible global primitives only', () => {
  const base = read('assets/css/base.css');
  const layout = read('assets/css/layout.css');
  const css = `${base}\n${layout}`;

  assert.equal(balancedBraces(base), true, 'base.css braces are unbalanced');
  assert.equal(balancedBraces(layout), true, 'layout.css braces are unbalanced');
  assert.doesNotMatch(css, /@import/i);
  assert.doesNotMatch(css, /https?:\/\//i);
  assert.doesNotMatch(css, /#[0-9a-f]{3,8}\b/i, 'theme.json should remain the palette source');
  assert.doesNotMatch(css, /(?:\.woocommerce|\.wc-block|woocommerce-)/i);
  assert.match(base, /:focus-visible/);
  assert.match(base, /prefers-reduced-motion\s*:\s*reduce/);
  assert.match(base, /\.screen-reader-text/);
  assert.match(layout, /\.statement-container\b/);
  assert.match(layout, /\.statement-container--wide\b/);
  assert.match(layout, /\.statement-reading-width\b/);
  assert.match(layout, /\.statement-page\b/);
  assert.match(layout, /\.statement-stack\b/);
});

test('asset module enqueues only the two local stylesheets', () => {
  const path = resolve(themeRoot, 'inc', 'assets.php');
  assert.equal(existsSync(path), true, 'missing inc/assets.php');
  const assets = read('inc/assets.php');
  const functions = read('functions.php');

  assert.match(functions, /inc[\\/'].*assets\.php/);
  assert.match(assets, /wp_enqueue_scripts/);
  assert.match(assets, /wp_enqueue_style\s*\(/);
  assert.match(assets, /assets\/css\/base\.css/);
  assert.match(assets, /assets\/css\/layout\.css/);
  assert.doesNotMatch(assets, /wp_enqueue_script\s*\(/);
});

test('document shell exposes WordPress lifecycle hooks and aligned skip target', () => {
  const header = read('header.php');
  const footer = read('footer.php');
  const index = read('index.php');

  for (const signal of ['<!doctype html>', 'language_attributes', "bloginfo( 'charset' )", 'name="viewport"', 'wp_head', 'body_class', 'wp_body_open']) {
    assert.ok(header.toLowerCase().includes(signal.toLowerCase()), `header missing ${signal}`);
  }
  assert.match(header, /href="#primary"/);
  assert.match(footer, /wp_footer\s*\(/);
  assert.match(index, /get_header\s*\(/);
  assert.match(index, /<main\s+id="primary"/);
  assert.match(index, /get_footer\s*\(/);
});

test('M2 theme contains no M3+ scope or external assets', () => {
  const files = walk(themeRoot);
  const sourceFiles = files.filter((path) => ['.php', '.css'].includes(extname(path)));
  const source = sourceFiles.map((path) => readFileSync(path, 'utf8')).join('\n');

  assert.equal(files.some((path) => extname(path).toLowerCase() === '.js'), false, 'JavaScript is out of scope');
  assert.doesNotMatch(source, /@font-face|fonts\.googleapis|use\.typekit|https?:\/\//i);
  assert.doesNotMatch(source, /wp_nav_menu|register_nav_menu|mobile[-_ ]?(?:menu|drawer)|mini[-_ ]?cart|product[-_ ]?card/i);
  assert.doesNotMatch(source, /account[-_ ]?menu|class=["'][^"']*search[-_ ]?form|announcement[-_ ]?bar/i);
});
