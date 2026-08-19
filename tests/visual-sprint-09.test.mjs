import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import { join, resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const themeRoot = join(root, 'wp-content', 'themes', 'statement-collector-theme');

test('Visual Sprint 09: Theme Candidate Version is bumped to 0.13.0-rc.19', () => {
  const styleCss = readFileSync(join(themeRoot, 'style.css'), 'utf8');
  assert.match(styleCss, /Version:\s*0\.13\.0-rc\.19/);

  const functionsPhp = readFileSync(join(themeRoot, 'functions.php'), 'utf8');
  assert.match(functionsPhp, /define\(\s*['"]STATEMENT_COLLECTOR_THEME_VERSION['"]\s*,\s*['"]0\.13\.0-rc\.19['"]\s*\);/);
});

test('Visual Sprint 09: About Page is 100% Pure Typographic Art Direction (Zero Images)', () => {
  const aboutPath = join(themeRoot, 'page-about.php');
  assert.ok(existsSync(aboutPath), 'page-about.php must exist');

  const content = readFileSync(aboutPath, 'utf8');

  // Must contain NO <img>, <picture>, <svg>
  assert.doesNotMatch(content, /<img/i, 'About page must contain zero <img> tags');
  assert.doesNotMatch(content, /<picture/i, 'About page must contain zero <picture> tags');
  assert.doesNotMatch(content, /<svg/i, 'About page must contain zero <svg> tags');

  // Must contain editorial opening quote
  assert.match(content, /Clothing should be more than something you wear/i);

  // Must contain 12-column narrative sections
  assert.match(content, /statement-narrative-rail/);
  assert.match(content, /PHILOSOPHY/);
  assert.match(content, /CRAFT/);
  assert.match(content, /EXCLUSIVITY/);
  assert.match(content, /RESPONSIBILITY/);

  // Must contain brand signature conclusion
  assert.match(content, /CRAFTED\.\s*LIMITED\.\s*NEVER\s*RESTOCKED\./i);
});

test('Visual Sprint 09: Contact Page is Luxury Concierge Interface (Zero Images)', () => {
  const contactPath = join(themeRoot, 'page-contact.php');
  assert.ok(existsSync(contactPath), 'page-contact.php must exist');

  const content = readFileSync(contactPath, 'utf8');

  // Must contain NO <img>, <picture>, <svg>
  assert.doesNotMatch(content, /<img/i, 'Contact page must contain zero <img> tags');
  assert.doesNotMatch(content, /<picture/i, 'Contact page must contain zero <picture> tags');
  assert.doesNotMatch(content, /<svg/i, 'Contact page must contain zero <svg> tags');

  // Must contain concierge email and channels
  assert.match(content, /info@mystatement\.store/);
  assert.match(content, /mailto:/);
  assert.match(content, /instagram\.com\/statement\.au/);
  assert.match(content, /PRIMARY CORRESPONDENCE/i);
});

test('Visual Sprint 09: Drop Page is Release Document & Collection Register', () => {
  const dropPath = join(themeRoot, 'taxonomy-statement_drop.php');
  assert.ok(existsSync(dropPath), 'taxonomy-statement_drop.php must exist');

  const content = readFileSync(dropPath, 'utf8');

  // Must contain release document architecture
  assert.match(content, /statement-drop-document/);
  assert.match(content, /statement-drop-document__meta-bar/);
  assert.match(content, /statement-drop-document__overview/);
  assert.match(content, /statement-drop-document__spec/);
  assert.match(content, /statement-drop-document__register/);
  assert.match(content, /COLLECTION REGISTER/i);
  assert.match(content, /statement-register-list/);
  assert.match(content, /VIEW PIECE →/);
});

test('Visual Sprint 09: Single Product Page (PDP) contains Disclosures and 65/35 Composition', () => {
  const singleProduct = readFileSync(join(themeRoot, 'woocommerce', 'content-single-product.php'), 'utf8');
  assert.match(singleProduct, /template-parts\/product\/gallery/);
  assert.match(singleProduct, /template-parts\/product\/summary/);

  const summary = readFileSync(join(themeRoot, 'template-parts', 'product', 'summary.php'), 'utf8');
  assert.match(summary, /template-parts\/product\/details/);
  assert.match(summary, /template-parts\/product\/size-guide/);

  const details = readFileSync(join(themeRoot, 'template-parts', 'product', 'details.php'), 'utf8');
  assert.match(details, /statement-disclosure/);
  assert.match(details, /PRODUCT DETAILS/i);
  assert.match(details, /SIZE & FIT/i);
  assert.match(details, /DISPATCH & CARE/i);
});
