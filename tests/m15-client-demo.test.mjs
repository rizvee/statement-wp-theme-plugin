import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import test from 'node:test';
import { packageTheme } from '../scripts/package-theme.mjs';
import { packageClientDemo } from '../scripts/package-client-demo.mjs';
import { verifyPackage } from '../scripts/verify-package.mjs';

const root = resolve(import.meta.dirname, '..');

test('M15: Theme 0.13.0-rc.13 metadata and file structure', () => {
  const styleCss = readFileSync(join(root, 'wp-content/themes/statement-collector-theme/style.css'), 'utf8');
  assert.match(styleCss, /Version:\s*0\.13\.0-rc\.13/);

  const functionsPhp = readFileSync(join(root, 'wp-content/themes/statement-collector-theme/functions.php'), 'utf8');
  assert.match(functionsPhp, /define\(\s*['"]STATEMENT_COLLECTOR_THEME_VERSION['"]\s*,\s*['"]0\.13\.0-rc\.13['"]\s*\);/);

  const setupPhp = readFileSync(join(root, 'wp-content/themes/statement-collector-theme/inc/setup.php'), 'utf8');
  assert.match(setupPhp, /function protect_front_page_template/);
  assert.match(setupPhp, /add_filter\(\s*'template_include'/);

  const navPhp = readFileSync(join(root, 'wp-content/themes/statement-collector-theme/inc/navigation.php'), 'utf8');
  assert.match(navPhp, /function get_drops_url/);
  assert.match(navPhp, /function get_about_url/);
  assert.match(navPhp, /function get_contact_url/);
  assert.match(navPhp, /function get_journal_url/);
  assert.match(navPhp, /DROPS/);

  assert.ok(existsSync(join(root, 'wp-content/themes/statement-collector-theme/page-drops.php')));
  assert.ok(existsSync(join(root, 'wp-content/themes/statement-collector-theme/page-about.php')));
  assert.ok(existsSync(join(root, 'wp-content/themes/statement-collector-theme/page-contact.php')));
  assert.ok(existsSync(join(root, 'wp-content/themes/statement-collector-theme/inc/customizer.php')));
  assert.ok(existsSync(join(root, 'wp-content/themes/statement-collector-theme/assets/js/hero-slider.js')));
  assert.ok(existsSync(join(root, 'wp-content/themes/statement-collector-theme/template-parts/home/hero.php')));
  assert.ok(existsSync(join(root, 'wp-content/themes/statement-collector-theme/template-parts/product/size-guide.php')));
  assert.ok(existsSync(join(root, 'wp-content/themes/statement-collector-theme/template-parts/home/email-capture.php')));
});

test('M15: Client Demo plugin structure and security invariants', () => {
  const mainPhp = readFileSync(join(root, 'tools/statement-client-demo/statement-client-demo.php'), 'utf8');
  assert.match(mainPhp, /Version:\s*0\.2\.5/);
  assert.match(mainPhp, /namespace Statement\\ClientDemo/);

  const adminPhp = readFileSync(join(root, 'tools/statement-client-demo/src/AdminPage.php'), 'utf8');
  assert.match(adminPhp, /manage_woocommerce/);
  assert.match(adminPhp, /check_admin_referer/);

  const seederPhp = readFileSync(join(root, 'tools/statement-client-demo/src/DemoSeederService.php'), 'utf8');
  assert.match(seederPhp, /_statement_client_demo/);
  assert.match(seederPhp, /_statement_demo_price/);
  assert.match(seederPhp, /_statement_demo_measurements/);
  assert.match(seederPhp, /drop-001-monogram-study/);
  assert.match(seederPhp, /STMT-CD-D001-MJ/);
  assert.match(seederPhp, /STMT-CD-D001-PHJ/);

  const manifestPhp = readFileSync(join(root, 'tools/statement-client-demo/src/ManifestService.php'), 'utf8');
  assert.match(manifestPhp, /statement_client_demo_manifest_v2/);
  assert.match(manifestPhp, /statement_client_demo_rollback/);

  const registryPhp = readFileSync(join(root, 'tools/statement-client-demo/src/AssetRegistry.php'), 'utf8');
  assert.match(registryPhp, /get_assets/);
  assert.match(registryPhp, /statement-monogram-jacket-front\.jpg/);
  assert.match(registryPhp, /statement-panelled-hood-jacket-front\.jpg/);
});

test('M15: Package Theme 0.13.0-rc.13 and Client Demo 0.2.5', () => {
  const themePkg = packageTheme('0.13.0-rc.13');
  assert.equal(themePkg.version, '0.13.0-rc.13');
  assert.ok(themePkg.sizeBytes > 0);
  assert.ok(existsSync(themePkg.path));

  const themeVerify = verifyPackage(themePkg.path, '0.13.0-rc.13');
  assert.ok(themeVerify.ok, `Theme package verification failed: ${themeVerify.errors.join(', ')}`);

  const demoPkg = packageClientDemo('0.2.5');
  assert.equal(demoPkg.version, '0.2.5');
  assert.ok(demoPkg.sizeBytes > 0);
  assert.ok(existsSync(demoPkg.path));

  const demoVerify = verifyPackage(demoPkg.path, '0.2.5');
  assert.ok(demoVerify.ok, `Demo package verification failed: ${demoVerify.errors.join(', ')}`);
});
