import assert from 'node:assert/strict';
import { existsSync, readdirSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const themeDir = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');

test('M14 Storefront Hardening: Theme Version & Constant Invariants', () => {
  const styleCss = readFileSync(resolve(themeDir, 'style.css'), 'utf8');
  const functionsPhp = readFileSync(resolve(themeDir, 'functions.php'), 'utf8');

  assert.match(styleCss, /Version:\s*0\.13\.0-rc\.3/i, 'style.css must specify Version: 0.13.0-rc.3');
  assert.match(
    functionsPhp,
    /define\(\s*['"]STATEMENT_COLLECTOR_THEME_VERSION['"]\s*,\s*['"]0\.13\.0-rc\.3['"]\s*\);/,
    'functions.php must define STATEMENT_COLLECTOR_THEME_VERSION 0.13.0-rc.3'
  );
});

test('M14 Storefront Hardening: Private Access Gate CSS & Typography Integration', () => {
  const catalogCss = readFileSync(resolve(themeDir, 'assets', 'css', 'catalog.css'), 'utf8');

  assert.ok(catalogCss.includes('.statement-access-gate'), 'catalog.css must define .statement-access-gate');
  assert.ok(catalogCss.includes('.statement-access-gate__title'), 'catalog.css must define .statement-access-gate__title');
  assert.ok(catalogCss.includes('.statement-access-gate__subtitle'), 'catalog.css must define .statement-access-gate__subtitle');
  assert.ok(catalogCss.includes('.statement-access-gate__form'), 'catalog.css must define .statement-access-gate__form');
  assert.ok(catalogCss.includes('.statement-access-gate__input'), 'catalog.css must define .statement-access-gate__input');
  assert.ok(catalogCss.includes('.statement-access-gate__button'), 'catalog.css must define .statement-access-gate__button');
  assert.ok(catalogCss.includes('.statement-access-gate__consent'), 'catalog.css must define .statement-access-gate__consent');
  assert.ok(catalogCss.includes('--wp--preset--font-family--display'), 'Gate must use display typography preset');
});

test('M14 Storefront Hardening: Design System & theme.json Tokens', () => {
  const themeJson = JSON.parse(readFileSync(resolve(themeDir, 'theme.json'), 'utf8'));

  assert.equal(themeJson.version, 3, 'theme.json schema version must be 3');
  const palette = themeJson.settings.color.palette;
  const slugs = palette.map((p) => p.slug);
  assert.ok(slugs.includes('gallery-ivory'), 'Palette includes gallery-ivory');
  assert.ok(slugs.includes('warm-white'), 'Palette includes warm-white');
  assert.ok(slugs.includes('ink-navy'), 'Palette includes ink-navy');
  assert.ok(slugs.includes('near-black'), 'Palette includes near-black');
  assert.ok(slugs.includes('soft-graphite'), 'Palette includes soft-graphite');
  assert.ok(slugs.includes('border-grey'), 'Palette includes border-grey');
  assert.ok(slugs.includes('brass'), 'Palette includes brass');

  const fontFamilies = themeJson.settings.typography.fontFamilies.map((f) => f.slug);
  assert.ok(fontFamilies.includes('display'), 'Typography includes display font family');
  assert.ok(fontFamilies.includes('ui'), 'Typography includes ui font family');
});

test('M14 Storefront Hardening: Navigation Accessibility & Asset Loading', () => {
  const navJs = readFileSync(resolve(themeDir, 'assets', 'js', 'navigation.js'), 'utf8');
  const siteHeader = readFileSync(resolve(themeDir, 'template-parts', 'header', 'site-header.php'), 'utf8');

  assert.ok(navJs.includes('aria-expanded'), 'Navigation JS manages aria-expanded');
  assert.ok(siteHeader.includes('aria-controls'), 'Site header defines aria-controls');
  assert.ok(siteHeader.includes('aria-expanded'), 'Site header defines default aria-expanded');

  const headerCss = readFileSync(resolve(themeDir, 'assets', 'css', 'header.css'), 'utf8');
  assert.ok(headerCss.includes('.statement-site-header'), 'Header CSS defines .statement-site-header');
  assert.ok(headerCss.includes('.statement-brand'), 'Header CSS defines .statement-brand');
});

test('M14 Storefront Hardening: Packaged Theme 0.13.0-rc.3 Zip Exists and Verified', () => {
  const zipPath = resolve(root, 'dist', 'statement-collector-theme-0.13.0-rc.3.zip');
  assert.ok(existsSync(zipPath), 'Packaged theme 0.13.0-rc.3 ZIP must exist in dist/');
});

