import assert from 'node:assert/strict';
import { existsSync, readdirSync, readFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const themeDir = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');

test('M14 Storefront Hardening: Theme Version & Constant Invariants (0.13.0-rc.21)', () => {
  const styleCss = readFileSync(resolve(themeDir, 'style.css'), 'utf8');
  const functionsPhp = readFileSync(resolve(themeDir, 'functions.php'), 'utf8');

  assert.match(styleCss, /Version:\s*0\.13\.0-rc\.21/i, 'style.css must specify Version: 0.13.0-rc.21');
  assert.match(
    functionsPhp,
    /define\(\s*['"]STATEMENT_COLLECTOR_THEME_VERSION['"]\s*,\s*['"]0\.13\.0-rc\.21['"]\s*\);/,
    'functions.php must define STATEMENT_COLLECTOR_THEME_VERSION 0.13.0-rc.21'
  );
});

test('M14 Storefront Hardening: Design System & theme.json Tokens', () => {
  const themeJson = JSON.parse(readFileSync(resolve(themeDir, 'theme.json'), 'utf8'));

  assert.equal(themeJson.version, 3, 'theme.json schema version must be 3');
  const palette = themeJson.settings.color.palette;
  const slugs = palette.map((p) => p.slug);
  const expectedPalette = [
    'gallery-ivory',
    'warm-white',
    'ink-navy',
    'near-black',
    'soft-graphite',
    'border-grey',
    'brass',
  ];
  for (const slug of expectedPalette) {
    assert.ok(slugs.includes(slug), `Palette must include token "${slug}"`);
  }

  const fontFamilies = themeJson.settings.typography.fontFamilies.map((f) => f.slug);
  assert.ok(fontFamilies.includes('display'), 'Typography includes display font family');
  assert.ok(fontFamilies.includes('ui'), 'Typography includes ui font family');

  const displayFont = themeJson.settings.typography.fontFamilies.find((f) => f.slug === 'display');
  assert.match(displayFont.fontFamily, /"Instrument Serif"/i, 'Display font must reference Instrument Serif');
  const uiFont = themeJson.settings.typography.fontFamilies.find((f) => f.slug === 'ui');
  assert.match(uiFont.fontFamily, /Inter/i, 'UI font must reference Inter');
});

test('M14 Storefront Hardening: Editorial Fallback Navigation', () => {
  const navPhp = readFileSync(resolve(themeDir, 'inc', 'navigation.php'), 'utf8');
  const siteHeader = readFileSync(resolve(themeDir, 'template-parts', 'header', 'site-header.php'), 'utf8');
  const mobileNav = readFileSync(resolve(themeDir, 'template-parts', 'header', 'mobile-navigation.php'), 'utf8');

  // Verify navigation functions exist
  assert.ok(navPhp.includes('function get_shop_url()'), 'navigation.php must define get_shop_url()');
  assert.ok(navPhp.includes('function get_archive_url()'), 'navigation.php must define get_archive_url()');
  assert.ok(navPhp.includes('function render_primary_navigation()'), 'navigation.php must define render_primary_navigation()');
  assert.ok(navPhp.includes('function render_mobile_primary_navigation()'), 'navigation.php must define render_mobile_primary_navigation()');

  // Verify fallback links to Shop and Archive
  assert.match(navPhp, /SHOP/i, 'Fallback navigation must render SHOP link');
  assert.match(navPhp, /ARCHIVE/i, 'Fallback navigation must render ARCHIVE link');

  // Verify site header and mobile navigation use the rendering functions
  assert.ok(siteHeader.includes('render_primary_navigation();'), 'site-header.php must call render_primary_navigation()');
  assert.ok(mobileNav.includes('render_mobile_primary_navigation();'), 'mobile-navigation.php must call render_mobile_primary_navigation()');

  // Verify About page exists
  assert.ok(existsSync(resolve(themeDir, 'page-about.php')), 'Theme must define page-about.php');
  assert.ok(existsSync(resolve(themeDir, 'page-contact.php')), 'Theme must define page-contact.php');
});

test('M14 Storefront Hardening: Navigation Accessibility & Dialog Management JS', () => {
  const navJs = readFileSync(resolve(themeDir, 'assets', 'js', 'navigation.js'), 'utf8');
  const siteHeader = readFileSync(resolve(themeDir, 'template-parts', 'header', 'site-header.php'), 'utf8');

  assert.ok(navJs.includes('aria-expanded'), 'Navigation JS manages aria-expanded');
  assert.ok(navJs.includes('statement-dialog-open'), 'Navigation JS toggles statement-dialog-open body class');
  assert.ok(navJs.includes('overflow'), 'Navigation JS locks body scroll');
  assert.ok(navJs.includes('addEventListener(\'resize\''), 'Navigation JS listens to resize events');
  assert.ok(navJs.includes('event.target === dialog'), 'Navigation JS supports backdrop click close');

  assert.ok(siteHeader.includes('aria-controls="statement-mobile-navigation"'), 'Site header defines aria-controls');
  assert.ok(siteHeader.includes('aria-expanded="false"'), 'Site header defines default aria-expanded');
});

test('M14 Storefront Hardening: Search Dialog Accessibility & Boundaries', () => {
  const searchDialog = readFileSync(resolve(themeDir, 'template-parts', 'header', 'search-dialog.php'), 'utf8');

  assert.ok(searchDialog.includes('role="search"'), 'Search form defines role="search"');
  assert.ok(searchDialog.includes('data-dialog-focus'), 'Search input has data-dialog-focus');
  assert.ok(searchDialog.includes('data-dialog-close'), 'Search dialog has close button');
  assert.ok(searchDialog.includes('aria-labelledby="statement-search-title"'), 'Search dialog is labelled by heading');
});

test('M14 Storefront Hardening: Homepage LIVE-only Release Contract', () => {
  const frontPage = readFileSync(resolve(themeDir, 'front-page.php'), 'utf8');
  const homePhp = readFileSync(resolve(themeDir, 'inc', 'home.php'), 'utf8');
  const homeCss = readFileSync(resolve(themeDir, 'assets', 'css', 'home.css'), 'utf8');

  // Verify bounded selection
  assert.match(homePhp, /HOME_PRODUCT_LIMIT\s*=\s*4;/, 'Home product selection must be capped at 4');
  assert.ok(homePhp.includes('PublicApi::is_publicly_live'), 'Home release data must check PublicApi::is_publicly_live');

  // Verify template structure
  assert.ok(frontPage.includes('template-parts/home/hero'), 'front-page.php includes hero template');
  assert.ok(frontPage.includes('template-parts/home/active-drop'), 'front-page.php includes active-drop template');
  assert.ok(frontPage.includes('template-parts/home/drops-list'), 'front-page.php includes drops-list template');
  assert.ok(frontPage.includes('template-parts/home/email-capture'), 'front-page.php includes email-capture template');

  // Verify home CSS defines editorial hero and grid
  assert.ok(homeCss.includes('.statement-home-hero'), 'home.css defines .statement-home-hero');
  assert.ok(homeCss.includes('.statement-home-products__grid'), 'home.css defines .statement-home-products__grid');
  assert.ok(homeCss.includes('.statement-home-signup'), 'home.css defines .statement-home-signup');
});

test('M14 Storefront Hardening: Catalog & Shop Lifecycle Ordering & Card Proportions', () => {
  const catalogCss = readFileSync(resolve(themeDir, 'assets', 'css', 'catalog.css'), 'utf8');
  const cardPhp = readFileSync(resolve(themeDir, 'template-parts', 'product', 'card.php'), 'utf8');
  const cardCss = readFileSync(resolve(themeDir, 'assets', 'css', 'product-card.css'), 'utf8');

  assert.ok(cardCss.includes('aspect-ratio: 4 / 5;'), 'product-card.css enforces 4/5 portrait ratio');
  assert.ok(cardCss.includes('object-fit: cover;'), 'product-card.css enforces object-fit cover');
  assert.ok(cardPhp.includes('statement-piece__empty'), 'card.php provides placeholder for missing image');

  // Lifecycle badges
  assert.ok(cardPhp.includes('statement-badge--'), 'card.php renders lifecycle badge for terminal products');
  assert.ok(catalogCss.includes('.statement-catalog'), 'catalog.css defines .statement-catalog');
  assert.ok(catalogCss.includes('.statement-catalog-empty'), 'catalog.css defines .statement-catalog-empty');
  assert.ok(catalogCss.includes('.statement-access-gate'), 'catalog.css defines .statement-access-gate');
});

test('M14 Storefront Hardening: Dedicated Archive Page Permanent Scarcity Model', () => {
  const archivePhp = readFileSync(resolve(themeDir, 'page-archive.php'), 'utf8');

  assert.ok(archivePhp.includes('PublicApi::get_archive_products'), 'page-archive.php calls PublicApi::get_archive_products');
  assert.ok(archivePhp.includes('PublicApi::get_past_drops'), 'page-archive.php calls PublicApi::get_past_drops');
  assert.ok(archivePhp.includes('statement-archive__grid'), 'page-archive.php defines statement-archive__grid');
  assert.ok(archivePhp.includes('Past Drops'), 'page-archive.php displays Past Drops section');

  // Scarcity protection: no waitlist or restock wording
  assert.ok(!archivePhp.includes('waitlist'), 'Archive page must not mention waitlist');
  assert.ok(!archivePhp.includes('restock'), 'Archive page must not mention restock');
  assert.ok(!archivePhp.includes('notify me'), 'Archive page must not mention notify me');
});

test('M14 Storefront Hardening: Product Detail Page Lifecycle UI & Variable Support', () => {
  const summaryPhp = readFileSync(resolve(themeDir, 'template-parts', 'product', 'summary.php'), 'utf8');
  const productCss = readFileSync(resolve(themeDir, 'assets', 'css', 'product.css'), 'utf8');

  // Terminal check
  assert.ok(summaryPhp.includes('$is_terminal'), 'summary.php evaluates terminal state');
  assert.ok(summaryPhp.includes('woocommerce_template_single_add_to_cart()'), 'summary.php calls single add to cart for LIVE items');
  assert.ok(summaryPhp.includes('statement-status-badge--terminal'), 'summary.php renders terminal badge when not purchasable');

  // Variable product CSS support
  assert.ok(productCss.includes('table.variations'), 'product.css supports native variations table');
  assert.ok(productCss.includes('.single_add_to_cart_button'), 'product.css styles add to cart button');
  assert.ok(productCss.includes('.statement-product__gallery'), 'product.css styles responsive gallery');
});

test('M14 Storefront Hardening: Cart & Bag Responsive Protection (320px Overflow Invariant)', () => {
  const cartCss = readFileSync(resolve(themeDir, 'assets', 'css', 'cart.css'), 'utf8');
  const cartPhp = readFileSync(resolve(themeDir, 'woocommerce', 'cart', 'cart.php'), 'utf8');

  assert.ok(cartCss.includes('.statement-cart'), 'cart.css defines .statement-cart');
  assert.ok(cartCss.includes('@media (max-width: 24rem)'), 'cart.css includes 320px mobile protection media query');
  assert.ok(cartCss.includes('overflow-wrap: anywhere'), 'cart.css prevents text overflow on narrow viewports');
  assert.ok(cartPhp.includes('YOUR BAG'), 'cart.php renders YOUR BAG heading');
  assert.ok(cartPhp.includes('UPDATE BAG'), 'cart.php renders UPDATE BAG button');
  assert.ok(cartPhp.includes('woocommerce_cart_collaterals'), 'cart.php invokes woocommerce_cart_collaterals hook');
});

test('M14 Storefront Hardening: Checkout Structure & Statement QA Gateway Presentation', () => {
  const checkoutPhp = readFileSync(resolve(themeDir, 'woocommerce', 'checkout', 'form-checkout.php'), 'utf8');
  const checkoutCss = readFileSync(resolve(themeDir, 'assets', 'css', 'checkout.css'), 'utf8');

  assert.ok(checkoutPhp.includes('woocommerce_checkout_billing'), 'form-checkout.php preserves billing hook');
  assert.ok(checkoutPhp.includes('woocommerce_checkout_shipping'), 'form-checkout.php preserves shipping hook');
  assert.ok(checkoutPhp.includes('woocommerce_checkout_order_review'), 'form-checkout.php preserves order review hook');
  assert.ok(checkoutCss.includes('.statement-checkout'), 'checkout.css defines .statement-checkout');
  assert.ok(checkoutCss.includes('#place_order'), 'checkout.css styles #place_order CTA');
  assert.ok(checkoutCss.includes('.woocommerce-order-received'), 'checkout.css styles Order Received confirmation');
  assert.ok(checkoutCss.includes('.woocommerce-MyAccount-navigation'), 'checkout.css styles My Account navigation');
});

test('M14 Storefront Hardening: Scarcity Invariant Across All Theme Templates', () => {
  const templateFiles = [
    resolve(themeDir, 'front-page.php'),
    resolve(themeDir, 'page-archive.php'),
    resolve(themeDir, 'taxonomy-statement_drop.php'),
    resolve(themeDir, 'template-parts', 'product', 'card.php'),
    resolve(themeDir, 'template-parts', 'product', 'summary.php'),
    resolve(themeDir, 'template-parts', 'home', 'principle.php'),
    resolve(themeDir, 'template-parts', 'footer', 'site-footer.php'),
  ];

  for (const file of templateFiles) {
    if (existsSync(file)) {
      const content = readFileSync(file, 'utf8').toLowerCase();
      assert.ok(!content.includes('back in stock'), `${file} must not contain "back in stock"`);
      assert.ok(!content.includes('join waitlist'), `${file} must not contain "join waitlist"`);
      assert.ok(!content.includes('hurry, only'), `${file} must not contain fake scarcity urgency`);
      assert.ok(!content.includes('never restocked'), `${file} must not contain "never restocked"`);
    }
  }

  const principlePhp = readFileSync(resolve(themeDir, 'template-parts', 'home', 'principle.php'), 'utf8');
  assert.match(principlePhp, /CRAFTED\./i, 'Principle must state "CRAFTED."');
  assert.match(principlePhp, /NOT MASS MADE\./i, 'Principle must state "NOT MASS MADE."');
  assert.match(principlePhp, /Produced with intention, not volume\./i, 'Principle must state supporting line');
});

test('M14 Storefront Hardening: Exact Four WooCommerce Template Overrides Boundary', () => {
  const wooDir = resolve(themeDir, 'woocommerce');
  assert.ok(existsSync(wooDir), 'woocommerce directory must exist');

  function getFiles(dir, base = '') {
    const entries = readdirSync(dir, { withFileTypes: true });
    const files = [];
    for (const entry of entries) {
      const rel = base ? `${base}/${entry.name}` : entry.name;
      if (entry.isDirectory()) {
        files.push(...getFiles(join(dir, entry.name), rel));
      } else if (entry.name.endsWith('.php')) {
        files.push(rel.replace(/\\/g, '/'));
      }
    }
    return files;
  }

  const overrides = getFiles(wooDir).sort();
  const expectedOverrides = [
    'cart/cart.php',
    'checkout/form-checkout.php',
    'content-product.php',
    'content-single-product.php',
  ].sort();

  assert.deepEqual(overrides, expectedOverrides, 'Theme must contain exactly four WooCommerce overrides');
});
