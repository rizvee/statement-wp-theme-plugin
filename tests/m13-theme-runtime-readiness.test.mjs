import assert from 'node:assert/strict';
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { resolve } from 'node:path';
import test from 'node:test';

const root = resolve(import.meta.dirname, '..');
const themeDir = resolve(root, 'wp-content', 'themes', 'statement-collector-theme');
const coreDir = resolve(root, 'wp-content', 'plugins', 'statement-collector-core');

function collectPhpFiles(dir, files = []) {
  if (!existsSync(dir)) return files;
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    const fullPath = resolve(dir, entry.name);
    if (entry.isDirectory()) {
      collectPhpFiles(fullPath, files);
    } else if (entry.isFile() && entry.name.endsWith('.php')) {
      files.push(fullPath);
    }
  }
  return files;
}

test('Theme references ONLY existing public static methods in Statement Collector Core', () => {
  const themePhpFiles = collectPhpFiles(themeDir);
  const corePublicApiPhp = readFileSync(resolve(coreDir, 'src', 'PublicApi.php'), 'utf8');

  const referencedMethods = new Set();
  const regex = /PublicApi::([a-zA-Z0-9_]+)/g;

  for (const file of themePhpFiles) {
    const content = readFileSync(file, 'utf8');
    let match;
    while ((match = regex.exec(content)) !== null) {
      const method = match[1];
      if ('class' === method) continue; // Skip PHP classname keyword
      referencedMethods.add(method);
    }
  }

  assert.ok(referencedMethods.size > 0, 'Theme must reference at least one PublicApi method');

  for (const method of referencedMethods) {
    const methodDefRegex = new RegExp(`public\\s+static\\s+function\\s+${method}\\b`);
    assert.ok(
      methodDefRegex.test(corePublicApiPhp),
      `PublicApi method "${method}" referenced by theme MUST exist as a public static method in Statement Core PublicApi.php`
    );
  }
});

test('Theme guards all Core calls with class_exists checks so Core absence cannot cause fatal error', () => {
  const themePhpFiles = collectPhpFiles(themeDir);

  for (const file of themePhpFiles) {
    const content = readFileSync(file, 'utf8');
    if (content.includes('PublicApi::')) {
      assert.ok(
        content.includes("class_exists( 'Statement\\Collector\\Core\\PublicApi' )") ||
          content.includes('class_exists( PublicApi::class )') ||
          content.includes('class_exists('),
        `Theme file ${file} referencing PublicApi MUST guard calls with class_exists`
      );
    }
  }
});

test('Theme maintains exactly four WooCommerce template overrides', () => {
  const wooOverrideDir = resolve(themeDir, 'woocommerce');
  const overrideFiles = collectPhpFiles(wooOverrideDir).map((f) =>
    f.substring(wooOverrideDir.length + 1).replace(/\\/g, '/')
  );

  const expectedOverrides = [
    'content-product.php',
    'content-single-product.php',
    'cart/cart.php',
    'checkout/form-checkout.php',
  ];

  assert.deepEqual(
    overrideFiles.sort(),
    expectedOverrides.sort(),
    'Theme MUST maintain exactly the four approved WooCommerce template overrides'
  );
});

test('Production Theme contains ZERO test fixture SKUs, IDs, or hard-coded test slugs', () => {
  const themePhpFiles = collectPhpFiles(themeDir);
  const forbiddenFixtureStrings = [
    'TEST-LD01-MJ',
    'TEST-LD01-SO',
    'TEST-LD01-TJ',
    'test-live-drop-01',
    'test-outerwear',
    'test-integration',
  ];

  for (const file of themePhpFiles) {
    const content = readFileSync(file, 'utf8');
    for (const forbidden of forbiddenFixtureStrings) {
      assert.ok(
        !content.includes(forbidden),
        `Production theme file ${file} MUST NOT contain test fixture reference: ${forbidden}`
      );
    }
  }
});

test('Production Theme preserves scarcity invariant with zero restock, waitlist, or serial number references', () => {
  const themePhpFiles = collectPhpFiles(themeDir);
  const forbiddenScarcityPatterns = [
    /collector[_-]?number/i,
    /serial[_-]?number/i,
    /certificate[_-]?number/i,
    /200 pieces/i,
    /001\/200/i,
    /(?<!never\s)restock(?!ed)/i, // Matches restock/waitlist except "Never Restocked" / "never restocked"
    /waitlist/i,
  ];

  for (const file of themePhpFiles) {
    const content = readFileSync(file, 'utf8');
    for (const pattern of forbiddenScarcityPatterns) {
      assert.ok(
        !pattern.test(content),
        `Forbidden pattern ${pattern} found in production theme file: ${file}`
      );
    }
  }
});

test('Theme relies on WooCommerce price formatting helpers and contains zero hard-coded currency symbols', () => {
  const themePhpFiles = collectPhpFiles(themeDir);

  for (const file of themePhpFiles) {
    const content = readFileSync(file, 'utf8');
    const codeWithoutComments = content.replace(/\/\*[\s\S]*?\*\/|\/\/.*/g, '');
    assert.doesNotMatch(
      codeWithoutComments,
      /[$£€]\s*\d+\.?\d*/,
      `Theme file ${file} MUST NOT hard-code currency symbols into template output`
    );
  }
});

test('Single Product summary template omits Add to Bag for terminal states (SOLD_OUT, ARCHIVED)', () => {
  const summaryPhp = readFileSync(resolve(themeDir, 'template-parts', 'product', 'summary.php'), 'utf8');

  assert.match(summaryPhp, /in_array\(\s*\$state\s*,\s*array\(\s*['"]SOLD_OUT['"]\s*,\s*['"]ARCHIVED['"]\s*\)/);
  assert.match(summaryPhp, /statement-badge--<\?php/);
  assert.match(summaryPhp, /woocommerce_template_single_add_to_cart/);

  const elseBranchMatch = summaryPhp.match(/<\?php\s*else\s*:\s*\?>[\s\S]*?woocommerce_template_single_add_to_cart/);
  assert.ok(elseBranchMatch, 'woocommerce_template_single_add_to_cart MUST be rendered only in non-terminal branch');
});

test('Front page selects LIVE products only and handles empty release state gracefully', () => {
  const homeIncPhp = readFileSync(resolve(themeDir, 'inc', 'home.php'), 'utf8');

  assert.match(homeIncPhp, /PublicApi::is_publicly_live\(\s*\$product\s*\)/, 'Front page must filter candidates with PublicApi::is_publicly_live()');
  assert.match(homeIncPhp, /return\s+\$empty;/, 'Front page must return empty array if Core PublicApi is unavailable');
});

test('Theme layout files contain required WordPress head, footer, and body_open hooks', () => {
  const headerPhp = readFileSync(resolve(themeDir, 'header.php'), 'utf8');
  const footerPhp = readFileSync(resolve(themeDir, 'footer.php'), 'utf8');

  assert.ok(headerPhp.includes('wp_head()'), 'Header template MUST include wp_head()');
  assert.ok(headerPhp.includes('wp_body_open()'), 'Header template MUST include wp_body_open()');
  assert.ok(footerPhp.includes('wp_footer()'), 'Footer template MUST include wp_footer()');
});
