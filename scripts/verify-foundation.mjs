import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { extname, join, relative, resolve } from 'node:path';
import { lintPhp } from './php-lint.mjs';

const root = resolve(import.meta.dirname, '..');
const failures = [];
const notes = [];

const requiredFiles = [
  'AGENTS.md',
  'MEMORY.md',
  'TASKS.md',
  'RUNBOOK.md',
  'README.md',
  '.gitignore',
  '.ai/context/current-state.md',
  '.ai/context/project-brief.md',
  '.ai/context/architecture.md',
  '.ai/context/business-rules.md',
  '.ai/context/design-system.md',
  '.ai/context/deployment-rules.md',
  '.ai/checks/m0-foundation.md',
  '.ai/prompts/milestone-task.md',
  '.ai/skills/repository-verification.md',
  'scripts/lib/resolve-php.mjs',
  'scripts/php-lint.mjs',
  'scripts/package-child-theme.mjs',
  'scripts/verify-git-runtime-tracking.mjs',
  'tests/m1-skeleton.test.mjs',
  'tests/m2-design-system.test.mjs',
  'tests/m3-global-navigation.test.mjs',
  'tests/m4-domain-model.test.mjs',
  'tests/m5-homepage.test.mjs',
  'tests/m6-catalog.test.mjs',
  'tests/m7-product-detail.test.mjs',
  'tests/m8-cart-bag.test.mjs',
  'tests/m9-checkout.test.mjs',
  'tests/m10-private-access.test.mjs',
  'tests/m11-terminal-archive.test.mjs',
  'tests/m12-collector-provenance.test.mjs',
  'tests/m17-theme-extensibility.test.mjs',
  'tests/m18-plugin-compatibility.test.mjs',
  'tests/m19-acceptance-hardening.test.mjs',
  'tests/visual-sprint-07.test.mjs',
  'tests/visual-sprint-09.test.mjs',
  'tests/drop-pdp-ux.test.mjs',
  'tests/account-ui.test.mjs',
  'tests/php/test-public-fixture-isolation.php',
  'tests/php/test-client-demo-collision.php',
  'tests/php/test-hpos-compatibility.php',
  'tests/php/test-theme-security-and-extensibility.php',
  'tests/php/m1-bootstrap-smoke.php',
  'tests/php/m4-release-state.php',
  'tests/php/m4-purchasability.php',
  'tests/php/m4-product-admin.php',
  'tests/php/m4-drop-history.php',
  'tests/php/m4-woo-absence-smoke.php',
  'tests/php/m5-public-api.php',
  'tests/php/m5-home-data.php',
  'tests/php/m5-home-absence.php',
  'tests/php/m5-home-woo-absence.php',
  'tests/php/m6-catalog-visibility.php',
  'tests/php/m6-drop-privacy.php',
  'tests/php/m6-catalog-absence.php',
  'tests/php/m7-product-access.php',
  'tests/php/m7-add-to-cart.php',
  'tests/php/m7-product-absence.php',
  'tests/php/m8-cart-integrity.php',
  'tests/php/m8-bag-count.php',
  'tests/php/m8-cart-absence.php',
  'tests/php/m9-checkout-lifecycle.php',
  'tests/php/m9-checkout-absence.php',
  'tests/php/m10-persistence-secrets.php',
  'tests/php/m10-grants-sessions-tokens.php',
  'tests/php/m10-dropconfig-precheck.php',
  'tests/php/m10-eligibility-gate.php',
  'tests/php/m10-atomicity-api-protection.php',
  'tests/php/m11-terminal-archive.php',
  'tests/php/m12-collector-provenance.php',
  'tests/php/test-secret-vault.php',
  'tests/php/test-drop-config-fixture-recovery.php',
  'tests/php/test-marketing-signup.php',
  'tests/php/test-lifecycle-override.php',
  'tests/php/test-admin-product-edit-lifecycle.php',
  'tests/php/test-client-demo-collision.php',
  'tests/php/test-public-fixture-isolation.php',
  'tests/php/test-theme-options-security.php',
  '.ai/context/private-access-m10.md',
  '.ai/context/archive-terminal-m11.md',
  '.ai/context/collector-provenance-m12.md',
  'docs/premium-theme-architecture-study.md',
  'docs/theme-hooks-reference.md',
  'docs/child-theme-customization-guide.md',
  'docs/plugin-compatibility-matrix.md',
  'docs/theme-customization-guide.md',
  'docs/theme-setup-guide.md',
  'scripts/package-client-demo.mjs',
  'tests/m15-client-demo.test.mjs',
  'tests/visual-sprint-07.test.mjs',
  'docs/visual-sprint-07-release-notes.md',
  'docs/statement-new-assets-sprint-07.md',
  'docs/client-feedback-2026-08-18.md',
  'tools/statement-client-demo/statement-client-demo.php',
  'tools/statement-client-demo/src/AdminPage.php',
  'tools/statement-client-demo/src/AssetRegistry.php',
  'tools/statement-client-demo/src/OwnershipClassifier.php',
  'tools/statement-client-demo/src/DemoSeederService.php',
  'tools/statement-client-demo/src/ManifestService.php',
  'tools/statement-collector-child/style.css',
  'tools/statement-collector-child/functions.php',
  'tools/statement-collector-child/README.md',
];

const requiredDirectories = [
  '.ai/session-logs',
  'wp-content/themes/statement-collector-theme',
  'wp-content/plugins/statement-collector-core',
  'docs',
  'releases',
  'scripts',
  'tests',
  'tools/statement-collector-child',
];

function fail(message) {
  failures.push(message);
}

function text(path) {
  return readFileSync(join(root, path), 'utf8');
}

function walk(directory, output = []) {
  if (!existsSync(directory)) return output;
  const ignoredDirs = new Set(['.git', '.local-tools', 'node_modules', 'vendor', 'dist', 'tmp', 'coverage']);
  for (const entry of readdirSync(directory, { withFileTypes: true })) {
    if (ignoredDirs.has(entry.name)) continue;
    const fullPath = join(directory, entry.name);
    if (entry.isDirectory()) walk(fullPath, output);
    else output.push(fullPath);
  }
  return output;
}

for (const path of requiredFiles) {
  if (!existsSync(join(root, path)) || !statSync(join(root, path)).isFile()) fail(`Missing file: ${path}`);
}

for (const path of requiredDirectories) {
  if (!existsSync(join(root, path)) || !statSync(join(root, path)).isDirectory()) fail(`Missing directory: ${path}`);
}

const contentSignals = [
  ['.ai/context/business-rules.md', 'NEVER RESTOCKED'],
  ['.ai/context/business-rules.md', 'PERMANENT ARCHIVE'],
  ['.ai/context/business-rules.md', 'normal WooCommerce stock manipulation'],
  ['.ai/context/business-rules.md', 'statement_drop'],
  ['.ai/context/business-rules.md', 'backward transitions are rejected'],
  ['.ai/context/business-rules.md', 'positive stock or later stock adjustments cannot reopen purchasing'],
  ['.ai/context/business-rules.md', 'immutable through normal product saves'],
  ['.ai/context/current-state.md', 'canonical parent Statement release state'],
  ['.ai/context/business-rules.md', 'Normal public Homepage, Shop, and Drop storefront exposure is restricted to `LIVE`'],
  ['.ai/context/current-state.md', 'selected homepage pieces are bounded to four'],
  ['.ai/context/current-state.md', 'before result counts and pagination are calculated'],
  ['.ai/context/architecture.md', 'WooCommerce remains responsible for native catalog query mechanics'],
  ['.ai/context/architecture.md', 'Core owns direct product visibility and Add-to-Cart eligibility'],
  ['.ai/context/architecture.md', 'Core revalidates restored and current cart lines'],
  ['.ai/context/business-rules.md', 'remain canonically `LIVE` to remain in the normal public cart'],
  ['.ai/context/current-state.md', 'one generic lifecycle-neutral notice'],
  ['.ai/context/business-rules.md', 'final checkout validation before normal public order creation'],
  ['.ai/context/architecture.md', 'final cart-check boundary'],
  ['.ai/context/business-rules.md', 'Ordinary public product pages and Add-to-Cart requests are also restricted to canonical `LIVE` state'],
  ['.ai/context/architecture.md', 'statement-collector-theme'],
  ['.ai/context/architecture.md', 'statement-collector-core'],
  ['.ai/context/deployment-rules.md', 'https://mystatement.store/'],
  ['.ai/context/deployment-rules.md', 'Never auto-deploy'],
  ['TASKS.md', 'M1 — Theme Skeleton + Core Plugin Skeleton'],
  ['TASKS.md', 'M4 — Drop Architecture + Product Metadata'],
  ['TASKS.md', 'M6 — Shop + Drop Storefront'],
  ['TASKS.md', 'M8 — Cart + Bag Experience'],
  ['TASKS.md', 'M9 — Checkout'],
];

for (const [path, signal] of contentSignals) {
  if (existsSync(join(root, path)) && !text(path).includes(signal)) fail(`Missing locked signal in ${path}: ${signal}`);
}

const runtimeRoots = [
  'wp-content/themes/statement-collector-theme',
  'wp-content/plugins/statement-collector-core',
];
const approvedRuntimeFiles = [
  'wp-content/themes/statement-collector-theme/style.css',
  'wp-content/themes/statement-collector-theme/functions.php',
  'wp-content/themes/statement-collector-theme/index.php',
  'wp-content/themes/statement-collector-theme/header.php',
  'wp-content/themes/statement-collector-theme/footer.php',
  'wp-content/themes/statement-collector-theme/page.php',
  'wp-content/themes/statement-collector-theme/single.php',
  'wp-content/themes/statement-collector-theme/404.php',
  'wp-content/themes/statement-collector-theme/front-page.php',
  'wp-content/themes/statement-collector-theme/template-canvas.php',
  'wp-content/themes/statement-collector-theme/template-full-width.php',
  'wp-content/themes/statement-collector-theme/page-drops.php',
  'wp-content/themes/statement-collector-theme/page-archive.php',
  'wp-content/themes/statement-collector-theme/theme.json',
  'wp-content/themes/statement-collector-theme/assets/css/base.css',
  'wp-content/themes/statement-collector-theme/assets/css/layout.css',
  'wp-content/themes/statement-collector-theme/assets/css/header.css',
  'wp-content/themes/statement-collector-theme/assets/css/footer.css',
  'wp-content/themes/statement-collector-theme/assets/css/home.css',
  'wp-content/themes/statement-collector-theme/assets/css/product-card.css',
  'wp-content/themes/statement-collector-theme/assets/css/catalog.css',
  'wp-content/themes/statement-collector-theme/assets/css/product.css',
  'wp-content/themes/statement-collector-theme/assets/css/cart.css',
  'wp-content/themes/statement-collector-theme/assets/css/checkout.css',
  'wp-content/themes/statement-collector-theme/assets/css/account.css',
  'wp-content/themes/statement-collector-theme/assets/css/woo-blocks.css',
  'wp-content/themes/statement-collector-theme/assets/js/navigation.js',
  'wp-content/themes/statement-collector-theme/assets/js/hero-slider.js',
  'wp-content/themes/statement-collector-theme/assets/js/product.js',
  'wp-content/themes/statement-collector-theme/assets/images/statement-logo.png',
  'wp-content/themes/statement-collector-theme/assets/images/statement-hero-slide-monogram-arch.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-hero-slide-monogram-golden.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-hero-slide-hood-arch.jpg',
  'wp-content/themes/statement-collector-theme/assets/video/statement-hero-mobile-monogram.mp4',
  'wp-content/themes/statement-collector-theme/assets/images/statement-monogram-jacket-model-front.webp',
  'wp-content/themes/statement-collector-theme/assets/images/statement-monogram-jacket-product-front.webp',
  'wp-content/themes/statement-collector-theme/assets/images/statement-monogram-jacket-model-side.webp',
  'wp-content/themes/statement-collector-theme/assets/images/statement-monogram-jacket-model-back.webp',
  'wp-content/themes/statement-collector-theme/assets/images/statement-monogram-jacket-product-front-02.webp',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-model-front.webp',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-product-front.webp',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-model-side.webp',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-product-front-02.webp',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-branding-detail.webp',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-product-front-04.webp',
  'wp-content/themes/statement-collector-theme/assets/images/statement-monogram-jacket-front.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-monogram-jacket-back.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-monogram-jacket-side.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-monogram-jacket-flatlay-concrete.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-monogram-jacket-collar-detail.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-monogram-jacket-flatlay-slate.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-front.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-side.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-back.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-cathedral-front.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-embroidery-detail.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-panelled-hood-jacket-night-34.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-brand-leather-patch.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-brand-leather-badge.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-brand-insignia-vector.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-brand-insignia-gold.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-brand-wordmark.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-collector-dust-bag.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-collector-patch-palm.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-crafted-not-mass-made-poster.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-hero-slide-hood-01.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-hero-slide-hood-02.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-hero-slide-monogram-01.jpg',
  'wp-content/themes/statement-collector-theme/assets/images/statement-hero-slide-monogram-02.jpg',
  'wp-content/themes/statement-collector-theme/inc/assets.php',
  'wp-content/themes/statement-collector-theme/inc/customizer.php',
  'wp-content/themes/statement-collector-theme/inc/navigation.php',
  'wp-content/themes/statement-collector-theme/inc/home.php',
  'wp-content/themes/statement-collector-theme/inc/catalog.php',
  'wp-content/themes/statement-collector-theme/inc/product.php',
  'wp-content/themes/statement-collector-theme/inc/cart.php',
  'wp-content/themes/statement-collector-theme/inc/checkout.php',
  'wp-content/themes/statement-collector-theme/inc/setup.php',
  'wp-content/themes/statement-collector-theme/inc/design-tokens.php',
  'wp-content/themes/statement-collector-theme/inc/hooks.php',
  'wp-content/themes/statement-collector-theme/inc/page-meta.php',
  'wp-content/themes/statement-collector-theme/inc/woocommerce.php',
  'wp-content/themes/statement-collector-theme/inc/compatibility/woocommerce.php',
  'wp-content/themes/statement-collector-theme/inc/compatibility/woo-blocks.php',
  'wp-content/themes/statement-collector-theme/inc/compatibility/elementor.php',
  'wp-content/themes/statement-collector-theme/inc/compatibility/gutenberg.php',
  'wp-content/themes/statement-collector-theme/inc/compatibility/seo.php',
  'wp-content/themes/statement-collector-theme/inc/compatibility/jetpack.php',
  'wp-content/themes/statement-collector-theme/inc/compatibility/forms.php',
  'wp-content/themes/statement-collector-theme/inc/compatibility/caching.php',
  'wp-content/themes/statement-collector-theme/inc/admin/health.php',
  'wp-content/themes/statement-collector-theme/inc/admin/options-export.php',
  'wp-content/themes/statement-collector-theme/inc/admin/setup-screen.php',
  'wp-content/themes/statement-collector-theme/page-about.php',
  'wp-content/themes/statement-collector-theme/page-contact.php',
  'wp-content/themes/statement-collector-theme/template-parts/header/site-header.php',
  'wp-content/themes/statement-collector-theme/template-parts/header/mobile-navigation.php',
  'wp-content/themes/statement-collector-theme/template-parts/header/search-dialog.php',
  'wp-content/themes/statement-collector-theme/template-parts/footer/site-footer.php',
  'wp-content/themes/statement-collector-theme/template-parts/home/hero.php',
  'wp-content/themes/statement-collector-theme/template-parts/home/active-drop.php',
  'wp-content/themes/statement-collector-theme/template-parts/home/drops-list.php',
  'wp-content/themes/statement-collector-theme/template-parts/home/editorial.php',
  'wp-content/themes/statement-collector-theme/template-parts/home/lookbook.php',
  'wp-content/themes/statement-collector-theme/template-parts/home/products.php',
  'wp-content/themes/statement-collector-theme/template-parts/home/brand-object.php',
  'wp-content/themes/statement-collector-theme/template-parts/home/principle.php',
  'wp-content/themes/statement-collector-theme/template-parts/home/archive-link.php',
  'wp-content/themes/statement-collector-theme/template-parts/home/email-capture.php',
  'wp-content/themes/statement-collector-theme/template-parts/product/card.php',
  'wp-content/themes/statement-collector-theme/template-parts/product/gallery.php',
  'wp-content/themes/statement-collector-theme/template-parts/product/summary.php',
  'wp-content/themes/statement-collector-theme/template-parts/product/details.php',
  'wp-content/themes/statement-collector-theme/template-parts/product/size-guide.php',
  'wp-content/themes/statement-collector-theme/taxonomy-statement_drop.php',
  'wp-content/themes/statement-collector-theme/woocommerce/content-product.php',
  'wp-content/themes/statement-collector-theme/woocommerce/content-single-product.php',
  'wp-content/themes/statement-collector-theme/woocommerce/cart/cart.php',
  'wp-content/themes/statement-collector-theme/woocommerce/checkout/form-checkout.php',
  'wp-content/plugins/statement-collector-core/statement-collector-core.php',
  'wp-content/plugins/statement-collector-core/src/Plugin.php',
  'wp-content/plugins/statement-collector-core/src/PublicApi.php',
  'wp-content/plugins/statement-collector-core/src/Marketing/SignupService.php',
  'wp-content/plugins/statement-collector-core/src/Admin/LifecycleV2Admin.php',
  'wp-content/plugins/statement-collector-core/src/Catalog/Visibility.php',
  'wp-content/plugins/statement-collector-core/src/Cart/Integrity.php',
  'wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php',
  'wp-content/plugins/statement-collector-core/src/Product/Admin.php',
  'wp-content/plugins/statement-collector-core/src/Product/Access.php',
  'wp-content/plugins/statement-collector-core/src/Product/Metadata.php',
  'wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php',
  'wp-content/plugins/statement-collector-core/src/Release/LifecycleOverrideService.php',
  'wp-content/plugins/statement-collector-core/src/Release/Purchasability.php',
  'wp-content/plugins/statement-collector-core/src/Access/SecretVault.php',
  'wp-content/plugins/statement-collector-core/src/Access/Secrets.php',
  'wp-content/plugins/statement-collector-core/src/Access/Crypto.php',
  'wp-content/plugins/statement-collector-core/src/Access/Schema.php',
  'wp-content/plugins/statement-collector-core/src/Access/GrantService.php',
  'wp-content/plugins/statement-collector-core/src/Access/SessionService.php',
  'wp-content/plugins/statement-collector-core/src/Access/TokenService.php',
  'wp-content/plugins/statement-collector-core/src/Access/RateLimiter.php',
  'wp-content/plugins/statement-collector-core/src/Access/ConsentService.php',
  'wp-content/plugins/statement-collector-core/src/Access/DropConfig.php',
  'wp-content/plugins/statement-collector-core/src/Access/DropConfigAdmin.php',
  'wp-content/plugins/statement-collector-core/src/Access/Precheck.php',
  'wp-content/plugins/statement-collector-core/src/Access/EligibilityService.php',
  'wp-content/plugins/statement-collector-core/src/Access/MakeDropLive.php',
  'wp-content/plugins/statement-collector-core/src/Access/PrivateAccessGate.php',
  'wp-content/plugins/statement-collector-core/src/Access/OrderAudit.php',
  'wp-content/plugins/statement-collector-core/src/Access/EmailAccessGranted.php',
  'wp-content/plugins/statement-collector-core/src/Access/EmailAccessReminder.php',
  'wp-content/plugins/statement-collector-core/src/Access/ReminderService.php',
  'wp-content/plugins/statement-collector-core/src/Access/UnsubscribeService.php',
  'wp-content/plugins/statement-collector-core/src/Access/AdminUi.php',
  'wp-content/plugins/statement-collector-core/src/Access/RetentionService.php',
  'wp-content/plugins/statement-collector-core/src/Access/CacheHardening.php',
  'wp-content/plugins/statement-collector-core/src/Order/Provenance.php',
  'wp-content/plugins/statement-collector-core/src/Order/Completion.php',
  'wp-content/plugins/statement-collector-core/src/Order/AdminOrderView.php',
  'wp-content/plugins/statement-collector-core/src/Order/CustomerOrderView.php',
  'wp-content/plugins/statement-collector-core/src/Order/EmailIntegration.php',
  'wp-content/plugins/statement-collector-core/views/private-404.php',
];
const runtimeFiles = runtimeRoots.flatMap((runtimeRoot) => walk(join(root, runtimeRoot)))
  .map((path) => relative(root, path).replaceAll('\\', '/'));
const hasRuntime = approvedRuntimeFiles.some((path) => runtimeFiles.includes(path));

if (hasRuntime) {
  for (const path of approvedRuntimeFiles) {
    if (!runtimeFiles.includes(path)) fail(`Missing approved runtime file: ${path}`);
  }
  const unexpected = runtimeFiles.filter((path) => !approvedRuntimeFiles.includes(path));
  if (unexpected.length) fail(`Unexpected runtime file: ${unexpected.join(', ')}`);
} else {
  const unexpected = runtimeFiles.filter((path) => !path.endsWith('.gitkeep'));
  if (unexpected.length) fail(`Pre-M1 runtime root contains premature implementation: ${unexpected.join(', ')}`);
}

const allFiles = walk(root);
const textualExtensions = new Set(['', '.css', '.html', '.ini', '.js', '.json', '.jsx', '.md', '.mjs', '.php', '.ps1', '.sh', '.text', '.toml', '.ts', '.tsx', '.txt', '.xml', '.yaml', '.yml']);
const privateKeyPattern = new RegExp('-----BE' + 'GIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----', 'i');
const openAiPattern = new RegExp('\\b' + 'sk-' + '[A-Za-z0-9_-]{20,}\\b');
const githubPattern = new RegExp('\\bgh[pousr]_[A-Za-z0-9]{20,}\\b');
const awsPattern = new RegExp('\\bAKIA[A-Z0-9]{16}\\b');
const assignmentPattern = new RegExp("(?:pass" + "word|api[_-]?key|client[_-]?secret|access[_-]?token)\\s*[:=]\\s*[\"'][^\"'\\r\\n]{8,}[\"']", 'i');
const secretPatterns = [privateKeyPattern, openAiPattern, githubPattern, awsPattern, assignmentPattern];

for (const path of allFiles) {
  const rel = relative(root, path).replaceAll('\\\\', '/');
  if (!textualExtensions.has(extname(path).toLowerCase()) || statSync(path).size > 2_000_000) continue;
  const contents = readFileSync(path, 'utf8');
  if (contents.includes('\0')) fail(`Unexpected NUL byte in text file: ${rel}`);
  if (contents.length && !contents.endsWith('\n')) fail(`Missing final newline: ${rel}`);
  if (contents.split(/\r?\n/).some((line) => /[ \t]+$/.test(line))) fail(`Trailing whitespace: ${rel}`);
  if (secretPatterns.some((pattern) => pattern.test(contents))) fail(`Possible secret pattern: ${rel}`);
}

const zips = allFiles.filter((path) => extname(path).toLowerCase() === '.zip').map((path) => relative(root, path));
if (zips.length) fail(`Generated ZIPs are out of scope: ${zips.join(', ')}`);

const phpLint = lintPhp({ log: false });
if (phpLint.available) {
  if (!phpLint.ok) {
    for (const failure of phpLint.failures) {
      fail(`PHP syntax failed: ${relative(root, failure.file)} (${failure.output})`);
    }
  }
  notes.push(`PHP ${phpLint.php.version} available via ${phpLint.php.source}; linted ${phpLint.files.length} PHP file(s).`);
} else {
  const message = `PHP unavailable; PHP lint not run (${phpLint.files.length} PHP file(s) present).`;
  if (phpLint.files.length) fail(message);
  else notes.push(`LIMITATION: ${message}`);
}

for (const note of notes) console.log(note);
if (failures.length) {
  for (const failure of failures) console.error(`FAIL: ${failure}`);
  console.error(`Foundation verification failed with ${failures.length} issue(s).`);
  process.exit(1);
}

console.log(`PASS: ${requiredFiles.length} required files and ${requiredDirectories.length} required directories found.`);
console.log(`PASS: locked architecture, business-rule, deployment, secret, package, and milestone scope checks passed.`);
