import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const themeDir = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');
const demoDir = resolve(root, 'tools', 'statement-client-demo');

test('Drop Page: Luxury Collection & Release Index Architecture', () => {
  // 1. Template file existence
  assert.ok(existsSync(join(themeDir, 'taxonomy-statement_drop.php')), 'Theme must contain taxonomy-statement_drop.php');

  // 2. Drop template structure
  const dropPhp = readFileSync(join(themeDir, 'taxonomy-statement_drop.php'), 'utf8');
  assert.match(dropPhp, /class="statement-drop-page statement-catalog statement-container--wide"/, 'Drop template uses wide luxury container');
  assert.match(dropPhp, /class="statement-drop-page__header"/, 'Drop template renders distinct header');
  assert.match(dropPhp, /statement-eyebrow/, 'Drop template renders eyebrow');
  assert.match(dropPhp, /CURRENT RELEASE/, 'Drop template renders CURRENT RELEASE');
  assert.match(dropPhp, /statement-drop-page__title/, 'Drop template renders structured title');
  assert.match(dropPhp, /statement-drop-page__number/, 'Drop template renders drop numeral');
  assert.match(dropPhp, /statement-drop-page__name/, 'Drop template renders drop name');
  assert.match(dropPhp, /woocommerce_product_loop/, 'Drop template renders products loop');

  // 3. Breadcrumb & duplicate title suppression
  assert.doesNotMatch(dropPhp, /woocommerce_breadcrumb/, 'Drop template must not render breadcrumbs');

  // 4. Catalog CSS styling
  const catalogCss = readFileSync(join(themeDir, 'assets/css/catalog.css'), 'utf8');
  assert.match(catalogCss, /\.statement-drop-page__header/, 'catalog.css styles .statement-drop-page__header');
  assert.match(catalogCss, /\.statement-drop-page__title/, 'catalog.css styles .statement-drop-page__title');
  assert.match(catalogCss, /\.statement-drop-page__number/, 'catalog.css styles .statement-drop-page__number');
  assert.match(catalogCss, /\.statement-drop-page__name/, 'catalog.css styles .statement-drop-page__name');
  assert.match(catalogCss, /\.woocommerce-breadcrumb\s*\{\s*display:\s*none;/, 'catalog.css suppresses breadcrumbs');
});

test('Drops Index (/drops/): Editorial Numerals and Text Directory', () => {
  // 1. Template existence
  assert.ok(existsSync(join(themeDir, 'page-drops.php')), 'Theme must contain page-drops.php');

  // 2. Drops page content structure
  const dropsPhp = readFileSync(join(themeDir, 'page-drops.php'), 'utf8');
  assert.match(dropsPhp, /DROPS/, 'page-drops.php renders DROPS title');
  assert.match(dropsPhp, /01/, 'page-drops.php renders editorial index 01');
  assert.match(dropsPhp, /02/, 'page-drops.php renders editorial index 02');
  assert.match(dropsPhp, /03/, 'page-drops.php renders editorial index 03');
  assert.match(dropsPhp, /VIEW RELEASE →/, 'page-drops.php renders VIEW RELEASE CTA');
  assert.match(dropsPhp, /UPCOMING/, 'page-drops.php renders UPCOMING badge');
  assert.match(dropsPhp, /PAST RELEASES/, 'page-drops.php renders PAST RELEASES group');

  // 3. No fake dates or fake production totals
  assert.doesNotMatch(dropsPhp, /1 of \d+/i, 'page-drops.php must not invent production totals');
  assert.doesNotMatch(dropsPhp, /only \d+ left/i, 'page-drops.php must not invent fake stock urgency');
});

test('Single Product Page (PDP): High-Fashion Layout Proportions and Hierarchy', () => {
  // 1. Gallery to Summary Proportion in CSS
  const productCss = readFileSync(join(themeDir, 'assets/css/product.css'), 'utf8');
  assert.match(productCss, /grid-template-columns:\s*minmax\(0,\s*1\.5fr\)\s+minmax\(0,\s*1fr\)/, 'product.css sets 60/40 gallery to summary ratio on desktop');
  assert.match(productCss, /position:\s*sticky/, 'product.css makes summary sticky on desktop');

  // 2. Summary template hierarchy
  const summaryPhp = readFileSync(join(themeDir, 'template-parts/product/summary.php'), 'utf8');
  assert.match(summaryPhp, /class="statement-product__summary"/, 'summary.php renders .statement-product__summary');
  assert.match(summaryPhp, /class="statement-product__title"/, 'summary.php renders .statement-product__title');
  assert.match(summaryPhp, /woocommerce_template_single_price/, 'summary.php renders price');
  assert.match(summaryPhp, /woocommerce_template_single_excerpt/, 'summary.php renders short description excerpt');
  assert.match(summaryPhp, /woocommerce_template_single_add_to_cart/, 'summary.php renders add to cart');
  assert.match(summaryPhp, /template-parts\/product\/size-guide/, 'summary.php includes size guide template part');
});

test('Panelled Hood XL Variation & Interactive Size Selector Buttons', () => {
  // 1. XL Variation SKU in Seeder
  const seederPhp = readFileSync(join(demoDir, 'src/DemoSeederService.php'), 'utf8');
  assert.match(seederPhp, /STMT-CD-D001-PHJ-XL/, 'DemoSeederService defines XL SKU for Panelled Hood Jacket');
  assert.match(seederPhp, /variable \(S, M, L, XL\)/, 'DemoSeederService plan includes S, M, L, XL for Product 2');

  // 2. Product JS interaction script
  assert.ok(existsSync(join(themeDir, 'assets/js/product.js')), 'Theme must contain assets/js/product.js');
  const productJs = readFileSync(join(themeDir, 'assets/js/product.js'), 'utf8');
  assert.match(productJs, /statement-size-button-group/, 'product.js creates size button group');
  assert.match(productJs, /statement-size-btn/, 'product.js creates size buttons');
  assert.match(productJs, /setAttribute\(\s*['"]role['"]\s*,\s*['"]radio['"]\s*\)/, 'product.js uses accessible radio role for buttons');
  assert.match(productJs, /select\.dispatchEvent\(changeEvent\)/, 'product.js dispatches native change event on select');

  // 3. Product CSS size button styles
  const productCss = readFileSync(join(themeDir, 'assets/css/product.css'), 'utf8');
  assert.match(productCss, /\.statement-size-btn/, 'product.css styles .statement-size-btn');
  assert.match(productCss, /\.statement-size-btn\.is-selected/, 'product.css styles selected size button');
  assert.match(productCss, /\.statement-native-select--hidden/, 'product.css visually hides native select');
});

test('Body Size Guide: Nomenclature, CM Table, and Fit Disclaimer', () => {
  const sizeGuidePhp = readFileSync(join(themeDir, 'template-parts/product/size-guide.php'), 'utf8');
  assert.match(sizeGuidePhp, /BODY SIZE GUIDE/, 'Size guide title is BODY SIZE GUIDE');
  assert.match(sizeGuidePhp, /Body measurements are provided as a general fit guide\. Garment measurements may vary by piece\./, 'Size guide includes verified disclaimer');
  assert.match(sizeGuidePhp, /CHEST \(CM\)/, 'Size guide table includes CHEST (CM)');
  assert.match(sizeGuidePhp, /WAIST \(CM\)/, 'Size guide table includes WAIST (CM)');
  assert.match(sizeGuidePhp, /HEIGHT \(CM\)/, 'Size guide table includes HEIGHT (CM)');
  assert.match(sizeGuidePhp, /<strong>XL<\/strong>/, 'Size guide table includes XL row');
  assert.match(sizeGuidePhp, /dialog id="statement-size-guide-dialog"/, 'Size guide uses accessible HTML5 dialog');
});

test('Mobile Add to Bag UX & Sticky Bar Integration', () => {
  const productJs = readFileSync(join(themeDir, 'assets/js/product.js'), 'utf8');
  assert.match(productJs, /statement-mobile-sticky-bar/, 'product.js creates .statement-mobile-sticky-bar');
  assert.match(productJs, /IntersectionObserver/, 'product.js observes primary button visibility');

  const productCss = readFileSync(join(themeDir, 'assets/css/product.css'), 'utf8');
  assert.match(productCss, /\.statement-mobile-sticky-bar/, 'product.css styles sticky bar');
  assert.match(productCss, /\.statement-mobile-sticky-bar\.is-visible/, 'product.css styles visible sticky bar');
  assert.match(productCss, /env\(safe-area-inset-bottom/, 'product.css respects safe-area-inset-bottom');
});
