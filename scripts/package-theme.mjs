import { execSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const defaultVersion = '0.13.0-rc.19';

export const approvedThemeFiles = [
  'style.css',
  'functions.php',
  'index.php',
  'header.php',
  'footer.php',
  'page.php',
  'single.php',
  '404.php',
  'front-page.php',
  'template-canvas.php',
  'template-full-width.php',
  'page-drops.php',
  'page-archive.php',
  'page-about.php',
  'page-contact.php',
  'theme.json',
  'assets/css/base.css',
  'assets/css/layout.css',
  'assets/css/header.css',
  'assets/css/footer.css',
  'assets/css/home.css',
  'assets/css/product-card.css',
  'assets/css/catalog.css',
  'assets/css/product.css',
  'assets/css/cart.css',
  'assets/css/checkout.css',
  'assets/css/account.css',
  'assets/css/woo-blocks.css',
  'assets/js/navigation.js',
  'assets/js/hero-slider.js',
  'assets/js/product.js',
  'assets/images/statement-logo.png',
  'assets/images/statement-brand-insignia-vector.jpg',
  'assets/images/statement-brand-insignia-gold.jpg',
  'assets/images/statement-brand-wordmark.jpg',
  'assets/images/statement-brand-leather-badge.jpg',
  'assets/images/statement-brand-leather-patch.jpg',
  'assets/images/statement-crafted-not-mass-made-poster.jpg',
  'assets/images/statement-collector-dust-bag.jpg',
  'assets/images/statement-collector-patch-palm.jpg',
  'assets/images/statement-hero-slide-monogram-arch.jpg',
  'assets/images/statement-hero-slide-monogram-golden.jpg',
  'assets/images/statement-hero-slide-hood-arch.jpg',
  'assets/video/statement-hero-mobile-monogram.mp4',
  'assets/images/statement-monogram-jacket-model-front.webp',
  'assets/images/statement-monogram-jacket-product-front.webp',
  'assets/images/statement-monogram-jacket-model-side.webp',
  'assets/images/statement-monogram-jacket-model-back.webp',
  'assets/images/statement-monogram-jacket-product-front-02.webp',
  'assets/images/statement-panelled-hood-jacket-model-front.webp',
  'assets/images/statement-panelled-hood-jacket-product-front.webp',
  'assets/images/statement-panelled-hood-jacket-model-side.webp',
  'assets/images/statement-panelled-hood-jacket-product-front-02.webp',
  'assets/images/statement-panelled-hood-jacket-branding-detail.webp',
  'assets/images/statement-panelled-hood-jacket-product-front-04.webp',
  'assets/images/statement-monogram-jacket-front.jpg',
  'assets/images/statement-monogram-jacket-back.jpg',
  'assets/images/statement-monogram-jacket-side.jpg',
  'assets/images/statement-monogram-jacket-flatlay-concrete.jpg',
  'assets/images/statement-monogram-jacket-flatlay-slate.jpg',
  'assets/images/statement-monogram-jacket-collar-detail.jpg',
  'assets/images/statement-panelled-hood-jacket-front.jpg',
  'assets/images/statement-panelled-hood-jacket-side.jpg',
  'assets/images/statement-panelled-hood-jacket-back.jpg',
  'assets/images/statement-panelled-hood-jacket-cathedral-front.jpg',
  'assets/images/statement-panelled-hood-jacket-embroidery-detail.jpg',
  'assets/images/statement-panelled-hood-jacket-night-34.jpg',
  'assets/images/statement-hero-slide-hood-01.jpg',
  'assets/images/statement-hero-slide-hood-02.jpg',
  'assets/images/statement-hero-slide-monogram-01.jpg',
  'assets/images/statement-hero-slide-monogram-02.jpg',
  'inc/assets.php',
  'inc/navigation.php',
  'inc/home.php',
  'inc/catalog.php',
  'inc/product.php',
  'inc/cart.php',
  'inc/checkout.php',
  'inc/setup.php',
  'inc/design-tokens.php',
  'inc/hooks.php',
  'inc/page-meta.php',
  'inc/customizer.php',
  'inc/woocommerce.php',
  'inc/compatibility/woocommerce.php',
  'inc/compatibility/woo-blocks.php',
  'inc/compatibility/elementor.php',
  'inc/compatibility/gutenberg.php',
  'inc/compatibility/seo.php',
  'inc/compatibility/jetpack.php',
  'inc/compatibility/forms.php',
  'inc/compatibility/caching.php',
  'inc/admin/health.php',
  'inc/admin/options-export.php',
  'inc/admin/setup-screen.php',
  'template-parts/header/site-header.php',
  'template-parts/header/mobile-navigation.php',
  'template-parts/header/search-dialog.php',
  'template-parts/footer/site-footer.php',
  'template-parts/home/hero.php',
  'template-parts/home/active-drop.php',
  'template-parts/home/drops-list.php',
  'template-parts/home/editorial.php',
  'template-parts/home/lookbook.php',
  'template-parts/home/products.php',
  'template-parts/home/brand-object.php',
  'template-parts/home/principle.php',
  'template-parts/home/archive-link.php',
  'template-parts/home/email-capture.php',
  'template-parts/product/card.php',
  'template-parts/product/gallery.php',
  'template-parts/product/summary.php',
  'template-parts/product/details.php',
  'template-parts/product/size-guide.php',
  'taxonomy-statement_drop.php',
  'woocommerce/content-product.php',
  'woocommerce/content-single-product.php',
  'woocommerce/cart/cart.php',
  'woocommerce/checkout/form-checkout.php',
];

export function packageTheme(version = defaultVersion) {
  const sourceRoot = join(root, 'wp-content', 'themes', 'statement-collector-theme');
  const styleCssFile = join(sourceRoot, 'style.css');
  if (!existsSync(styleCssFile)) {
    throw new Error(`Missing theme style.css file: ${styleCssFile}`);
  }
  const styleContent = readFileSync(styleCssFile, 'utf8');
  const headerMatch = styleContent.match(/^[ \t\/*#]*Version:\s*(.+)$/m);
  if (!headerMatch || headerMatch[1].trim() !== version) {
    throw new Error(`Theme header Version mismatch in source style.css. Found "${headerMatch ? headerMatch[1].trim() : 'NONE'}", expected "${version}".`);
  }
  const functionsFile = join(sourceRoot, 'functions.php');
  if (existsSync(functionsFile)) {
    const fnContent = readFileSync(functionsFile, 'utf8');
    const constMatch = fnContent.match(/define\(\s*['"]STATEMENT_COLLECTOR_THEME_VERSION['"]\s*,\s*['"]([^'"]+)['"]\s*\);/);
    if (!constMatch || constMatch[1] !== version) {
      throw new Error(`STATEMENT_COLLECTOR_THEME_VERSION constant mismatch in source functions.php. Found "${constMatch ? constMatch[1] : 'NONE'}", expected "${version}".`);
    }
  }

  const distDir = join(root, 'dist');
  const uniqueId = `pkg-theme-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
  const stagingRoot = join(root, 'tmp', uniqueId);
  const stagingThemeDir = join(stagingRoot, 'statement-collector-theme');
  const zipFile = join(distDir, `statement-collector-theme-${version}.zip`);

  if (existsSync(stagingRoot)) rmSync(stagingRoot, { recursive: true, force: true, maxRetries: 3 });
  mkdirSync(stagingThemeDir, { recursive: true });
  mkdirSync(distDir, { recursive: true });

  let fileCount = 0;
  let phpCount = 0;

  for (const relPath of approvedThemeFiles) {
    const src = join(sourceRoot, relPath);
    if (!existsSync(src)) {
      continue;
    }
    const dest = join(stagingThemeDir, relPath);
    mkdirSync(dirname(dest), { recursive: true });
    writeFileSync(dest, readFileSync(src));
    fileCount += 1;
    if (relPath.endsWith('.php')) {
      phpCount += 1;
    }
  }

  if (existsSync(zipFile)) {
    rmSync(zipFile);
  }

  execSync(`tar -a -c -f "${zipFile}" statement-collector-theme`, {
    cwd: stagingRoot,
    stdio: 'pipe',
  });

  const zipBytes = readFileSync(zipFile);
  const sha256 = createHash('sha256').update(zipBytes).digest('hex');

  if (existsSync(stagingRoot)) rmSync(stagingRoot, { recursive: true, force: true, maxRetries: 3 });

  return {
    type: 'theme',
    name: `statement-collector-theme-${version}.zip`,
    path: zipFile,
    filename: `statement-collector-theme-${version}.zip`,
    root_folder: 'statement-collector-theme',
    rootFolder: 'statement-collector-theme',
    size_bytes: zipBytes.length,
    sizeBytes: zipBytes.length,
    sha256,
    file_count: fileCount,
    fileCount: fileCount,
    php_count: phpCount,
    phpCount: phpCount,
    version,
    header_version: version,
    runtime_version: version,
  };
}

if (process.argv[1] && process.argv[1].endsWith('package-theme.mjs')) {
  const result = packageTheme();
  console.log(JSON.stringify(result, null, 2));
}
