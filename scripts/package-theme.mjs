import { execSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const defaultVersion = '0.13.0-rc.2';

export const approvedThemeFiles = [
  'style.css',
  'functions.php',
  'index.php',
  'header.php',
  'footer.php',
  'front-page.php',
  'page-archive.php',
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
  'assets/js/navigation.js',
  'inc/assets.php',
  'inc/navigation.php',
  'inc/home.php',
  'inc/catalog.php',
  'inc/product.php',
  'inc/cart.php',
  'inc/checkout.php',
  'inc/setup.php',
  'inc/woocommerce.php',
  'template-parts/header/site-header.php',
  'template-parts/header/mobile-navigation.php',
  'template-parts/header/search-dialog.php',
  'template-parts/footer/site-footer.php',
  'template-parts/home/hero.php',
  'template-parts/home/active-drop.php',
  'template-parts/home/editorial.php',
  'template-parts/home/products.php',
  'template-parts/home/principle.php',
  'template-parts/home/archive-link.php',
  'template-parts/product/card.php',
  'template-parts/product/gallery.php',
  'template-parts/product/summary.php',
  'template-parts/product/details.php',
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
  const stagingParent = join(root, 'tmp', 'pkg-theme');
  const stagingDir = join(stagingParent, 'statement-collector-theme');
  const zipName = `statement-collector-theme-${version}.zip`;
  const zipPath = join(distDir, zipName);

  if (existsSync(stagingParent)) rmSync(stagingParent, { recursive: true, force: true });
  mkdirSync(stagingDir, { recursive: true });
  if (!existsSync(distDir)) mkdirSync(distDir, { recursive: true });

  let fileCount = 0;
  let phpCount = 0;

  for (const relFile of approvedThemeFiles) {
    const srcFile = join(sourceRoot, relFile);
    const destFile = join(stagingDir, relFile);

    if (!existsSync(srcFile)) {
      throw new Error(`Missing approved theme runtime file: ${relFile}`);
    }

    mkdirSync(dirname(destFile), { recursive: true });
    writeFileSync(destFile, readFileSync(srcFile));

    fileCount++;
    if (relFile.endsWith('.php')) phpCount++;
  }

  if (existsSync(zipPath)) rmSync(zipPath, { force: true });

  const tarCmd = `tar -caf "${zipPath}" -C "${stagingParent}" "statement-collector-theme"`;
  execSync(tarCmd, { cwd: root, stdio: 'pipe' });

  rmSync(stagingParent, { recursive: true, force: true });

  const zipBytes = readFileSync(zipPath);
  const sha256 = createHash('sha256').update(zipBytes).digest('hex');
  const sizeBytes = zipBytes.length;

  return {
    name: zipName,
    path: zipPath,
    version,
    fileCount,
    phpCount,
    sizeBytes,
    sha256,
    rootFolder: 'statement-collector-theme',
  };
}

if (process.argv[1] && process.argv[1].endsWith('package-theme.mjs')) {
  const result = packageTheme();
  console.log(`Packaged Theme: ${result.name} (${result.sizeBytes} bytes, SHA-256: ${result.sha256})`);
}
