import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const themeDir = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');
const demoDir = resolve(root, 'tools', 'statement-client-demo');

test('Visual Sprint 07: Authentic Statement Brand Logo & Header Integration', () => {
  // 1. Logo asset existence
  assert.ok(existsSync(join(themeDir, 'assets/images/statement-logo.png')), 'Theme must contain statement-logo.png');
  assert.ok(existsSync(join(demoDir, 'assets/images/statement-logo.png')), 'Demo plugin must contain statement-logo.png');

  // 2. Navigation rendering logic
  const navPhp = readFileSync(join(themeDir, 'inc/navigation.php'), 'utf8');
  assert.match(navPhp, /statement-logo\.png/, 'render_site_brand() must reference statement-logo.png');
  assert.match(navPhp, /class="statement-brand-logo"/, 'render_site_brand() outputs statement-brand-logo class');
  assert.match(navPhp, /the_custom_logo\(\)/, 'render_site_brand() supports WordPress custom_logo');

  // 3. Navigation menus omit Journal
  assert.doesNotMatch(navPhp, /JOURNAL/, 'Primary and mobile navigation fallbacks must omit JOURNAL');
  assert.doesNotMatch(navPhp, /function get_journal_url/, 'navigation.php must not define get_journal_url');

  // 4. Header CSS
  const headerCss = readFileSync(join(themeDir, 'assets/css/header.css'), 'utf8');
  assert.match(headerCss, /\.statement-brand-logo/, 'header.css must define .statement-brand-logo styles');
  assert.match(headerCss, /max-block-size/, 'header.css defines max-block-size for logo');

  // 5. Footer omits Journal
  const footerPhp = readFileSync(join(themeDir, 'template-parts/footer/site-footer.php'), 'utf8');
  assert.doesNotMatch(footerPhp, /JOURNAL/, 'Footer fallback must omit JOURNAL link');
  assert.match(footerPhp, /CONTACT/, 'Footer fallback must include CONTACT link');
});

test('Visual Sprint 07: Hero Rebuild with 1920x1080 Campaign Imagery and Mobile Video', () => {
  // 1. Media asset files
  assert.ok(existsSync(join(themeDir, 'assets/images/statement-hero-slide-monogram-arch.jpg')), 'Theme must contain arch campaign image');
  assert.ok(existsSync(join(themeDir, 'assets/images/statement-hero-slide-monogram-golden.jpg')), 'Theme must contain golden hour campaign image');
  assert.ok(existsSync(join(themeDir, 'assets/images/statement-hero-slide-hood-arch.jpg')), 'Theme must contain hood arch campaign image');
  assert.ok(existsSync(join(themeDir, 'assets/video/statement-hero-mobile-monogram.mp4')), 'Theme must contain mobile MP4 video');

  // 2. Hero template logic
  const heroPhp = readFileSync(join(themeDir, 'template-parts/home/hero.php'), 'utf8');
  assert.match(heroPhp, /statement-hero-mobile-monogram\.mp4/, 'hero.php default slides include mobile MP4');
  assert.match(heroPhp, /(?:statement-hero-slide-monogram-arch\.jpg|statement-black-nwhite-hoodie-n-jacket-product-front\.webp)/, 'hero.php default slides include desktop image');
  assert.match(heroPhp, /statement-hero-slide__video/, 'hero.php renders HTML5 video element for mobile');
  assert.match(heroPhp, /autoplay\s+preload="metadata"/, 'hero video includes autoplay and metadata preload');
  assert.match(heroPhp, /statement-hero-slide__desktop-media/, 'hero.php tags desktop media container');
  assert.match(heroPhp, /statement-hero-slide__mobile-media/, 'hero.php tags mobile media container');

  // 3. Hero JS lifecycle
  const heroJs = readFileSync(join(themeDir, 'assets/js/hero-slider.js'), 'utf8');
  assert.match(heroJs, /syncVideos/, 'hero-slider.js defines syncVideos handler');
  assert.match(heroJs, /prefersReducedMotion/, 'hero-slider.js checks prefersReducedMotion');
  assert.match(heroJs, /visibilitychange/, 'hero-slider.js pauses video on visibility change');

  // 4. Hero CSS
  const homeCss = readFileSync(join(themeDir, 'assets/css/home.css'), 'utf8');
  assert.match(homeCss, /\.statement-hero-slide__video/, 'home.css styles video element');
  assert.match(homeCss, /min-block-size:\s*clamp\(34rem,\s*78svh,\s*54rem\)/, 'home.css sets 78svh hero height');
});

test('Visual Sprint 07: Strict 5-Part Homepage Hierarchy & Editorial Drops Directory', () => {
  const frontPage = readFileSync(join(themeDir, 'front-page.php'), 'utf8');
  assert.match(frontPage, /template-parts\/home\/hero/, 'front-page.php loads hero');
  assert.match(frontPage, /template-parts\/home\/active-drop/, 'front-page.php loads active-drop');
  assert.match(frontPage, /template-parts\/home\/drops-list/, 'front-page.php loads drops-list');
  assert.doesNotMatch(frontPage, /template-parts\/home\/products/, 'front-page.php does NOT separate products from active-drop');

  const activeDrop = readFileSync(join(themeDir, 'template-parts/home/active-drop.php'), 'utf8');
  assert.match(activeDrop, /statement-home-drop__products-grid/, 'active-drop renders side-by-side products grid');
  assert.match(activeDrop, /template-parts\/product\/card/, 'active-drop renders product cards');

  const dropsList = readFileSync(join(themeDir, 'template-parts/home/drops-list.php'), 'utf8');
  assert.match(dropsList, /CURRENT RELEASE/, 'drops-list includes CURRENT RELEASE group');
  assert.match(dropsList, /UPCOMING DROPS/, 'drops-list includes UPCOMING DROPS group');
  assert.match(dropsList, /Drop 002/, 'drops-list includes Drop 002');
  assert.match(dropsList, /Drop 003/, 'drops-list includes Drop 003');

  const pageDrops = readFileSync(join(themeDir, 'page-drops.php'), 'utf8');
  assert.match(pageDrops, /statement-drops-page/, 'page-drops.php exists with editorial layout');
  assert.match(pageDrops, /UPCOMING/, 'page-drops.php includes UPCOMING');
});

test('Visual Sprint 07: Text-Only About & Contact Pages', () => {
  const aboutPhp = readFileSync(join(themeDir, 'page-about.php'), 'utf8');
  assert.doesNotMatch(aboutPhp, /<img/i, 'page-about.php must NOT contain <img> tags');
  assert.doesNotMatch(aboutPhp, /—/u, 'page-about.php must NOT contain em-dashes');
  assert.match(aboutPhp, /statement-about-(?:prose|narrative|document)/, 'page-about.php uses editorial prose container');
  assert.match(aboutPhp, /Born in Australia/, 'page-about.php includes client narrative');

  const contactPhp = readFileSync(join(themeDir, 'page-contact.php'), 'utf8');
  assert.doesNotMatch(contactPhp, /<img/i, 'page-contact.php must NOT contain <img> tags');
  assert.match(contactPhp, /info@mystatement\.store/, 'page-contact.php renders verified email');
  assert.match(contactPhp, /@statement\.au/, 'page-contact.php renders verified Instagram handle');
  assert.match(contactPhp, /get_facebook_url\(\)/, 'page-contact.php checks get_facebook_url()');
  assert.doesNotMatch(contactPhp, /9:00 AM/i, 'page-contact.php must NOT contain fake operating hours');
});

test('Visual Sprint 07: Product Photography Suites, Metadata Defect Remediation & Size Guide Polish', () => {
  // 1. Normalized WebP files exist
  const jacketWebp = [
    'statement-monogram-jacket-model-front.webp',
    'statement-monogram-jacket-product-front.webp',
    'statement-monogram-jacket-model-side.webp',
    'statement-monogram-jacket-model-back.webp',
    'statement-monogram-jacket-product-front-02.webp',
  ];
  for (const f of jacketWebp) {
    assert.ok(existsSync(join(themeDir, 'assets/images', f)), `Theme must contain ${f}`);
    assert.ok(existsSync(join(demoDir, 'assets/images', f)), `Demo must contain ${f}`);
  }

  const hoodWebp = [
    'statement-panelled-hood-jacket-model-front.webp',
    'statement-panelled-hood-jacket-product-front.webp',
    'statement-panelled-hood-jacket-model-side.webp',
    'statement-panelled-hood-jacket-product-front-02.webp',
    'statement-panelled-hood-jacket-branding-detail.webp',
    'statement-panelled-hood-jacket-product-front-04.webp',
  ];
  for (const f of hoodWebp) {
    assert.ok(existsSync(join(themeDir, 'assets/images', f)), `Theme must contain ${f}`);
    assert.ok(existsSync(join(demoDir, 'assets/images', f)), `Demo must contain ${f}`);
  }

  // 2. Seeder Hoodie remediation
  const seederPhp = readFileSync(join(demoDir, 'src/DemoSeederService.php'), 'utf8');
  assert.match(seederPhp, /SKU_P2_XL\s*=\s*'STMT-CD-D001-PHJ-XL'/, 'Seeder defines SKU_P2_XL');
  assert.match(seederPhp, /array\(\s*'size'\s*=>\s*'XL'/m, 'Seeder includes XL variation for hoodie');
  assert.match(seederPhp, /hood_highres/, 'Seeder Product 02 includes highres photography');

  // 3. Size Guide Component with XL
  const sizeGuidePhp = readFileSync(join(themeDir, 'template-parts/product/size-guide.php'), 'utf8');
  assert.match(sizeGuidePhp, /<strong>XL<\/strong>/, 'Size guide table must include XL row');
  assert.match(sizeGuidePhp, /statement-size-guide-trigger/, 'Size guide renders prominent trigger button');

  // 4. Product CSS
  const productCss = readFileSync(join(themeDir, 'assets/css/product.css'), 'utf8');
  assert.match(productCss, /\.statement-size-guide-trigger/, 'product.css styles size guide trigger');
});
