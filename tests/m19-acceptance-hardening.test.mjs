import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import { resolve, join } from 'node:path';
import { execSync } from 'node:child_process';

const root = resolve(import.meta.dirname, '..');

test('M19 Acceptance Hardening & Production Convergence Suite', async (t) => {
  await t.test('1. Version Synchronization across Core, Theme, Demo, and Child Theme', () => {
    const themeStyle = readFileSync(join(root, 'wp-content/themes/statement-collector-theme/style.css'), 'utf8');
    assert.match(themeStyle, /Version:\s*0\.13\.0-rc\.19/, 'Theme style.css must be 0.13.0-rc.19');

    const themeFuncs = readFileSync(join(root, 'wp-content/themes/statement-collector-theme/functions.php'), 'utf8');
    assert.match(themeFuncs, /STATEMENT_COLLECTOR_THEME_VERSION',\s*'0\.13\.0-rc\.19'/, 'Theme functions.php constant must be 0.13.0-rc.19');

    const coreEntry = readFileSync(join(root, 'wp-content/plugins/statement-collector-core/statement-collector-core.php'), 'utf8');
    assert.match(coreEntry, /Version:\s*0\.13\.0-rc\.15/, 'Core plugin entry header must be 0.13.0-rc.15');
    assert.match(coreEntry, /STATEMENT_COLLECTOR_CORE_VERSION',\s*'0\.13\.0-rc\.15'/, 'Core plugin constant must be 0.13.0-rc.15');

    const demoEntry = readFileSync(join(root, 'tools/statement-client-demo/statement-client-demo.php'), 'utf8');
    assert.match(demoEntry, /Version:\s*0\.2\.7/, 'Client demo header must be 0.2.7');
    assert.match(demoEntry, /STATEMENT_CLIENT_DEMO_VERSION',\s*'0\.2\.7'/, 'Client demo constant must be 0.2.7');

    const childStyle = readFileSync(join(root, 'tools/statement-collector-child/style.css'), 'utf8');
    assert.match(childStyle, /Version:\s*0\.1\.0/, 'Child theme style.css must be 0.1.0');
  });

  await t.test('2. Core HPOS Compatibility & Theme Boundaries', () => {
    const coreEntry = readFileSync(join(root, 'wp-content/plugins/statement-collector-core/statement-collector-core.php'), 'utf8');
    assert.match(coreEntry, /before_woocommerce_init/, 'Core hooks before_woocommerce_init');
    assert.match(coreEntry, /custom_order_tables/, 'Core declares custom_order_tables compatibility');

    const themeWoo = readFileSync(join(root, 'wp-content/themes/statement-collector-theme/inc/compatibility/woocommerce.php'), 'utf8');
    assert.doesNotMatch(themeWoo, /custom_order_tables/, 'Theme does NOT declare custom_order_tables compatibility');
    assert.match(themeWoo, /add_theme_support\(\s*'woocommerce'/, 'Theme declares add_theme_support for woocommerce');
  });

  await t.test('3. Elementor & Page Builder Safe Templates', () => {
    const canvasPath = join(root, 'wp-content/themes/statement-collector-theme/template-canvas.php');
    assert.equal(existsSync(canvasPath), true, 'template-canvas.php must exist');
    const canvasContent = readFileSync(canvasPath, 'utf8');
    assert.match(canvasContent, /Template Name:\s*Statement Canvas/, 'template-canvas.php Template Name header');
    assert.match(canvasContent, /wp_head\(\)/, 'template-canvas.php calls wp_head()');
    assert.match(canvasContent, /wp_footer\(\)/, 'template-canvas.php calls wp_footer()');

    const fullWidthPath = join(root, 'wp-content/themes/statement-collector-theme/template-full-width.php');
    assert.equal(existsSync(fullWidthPath), true, 'template-full-width.php must exist');
    const fullWidthContent = readFileSync(fullWidthPath, 'utf8');
    assert.match(fullWidthContent, /Template Name:\s*Statement Full Width/, 'template-full-width.php Template Name header');
    assert.match(fullWidthContent, /get_header\(\)/, 'template-full-width.php calls get_header()');
    assert.match(fullWidthContent, /get_footer\(\)/, 'template-full-width.php calls get_footer()');

    const elementorAdapter = readFileSync(join(root, 'wp-content/themes/statement-collector-theme/inc/compatibility/elementor.php'), 'utf8');
    assert.match(elementorAdapter, /statement_theme_register_elementor_locations/, 'Elementor adapter exposes filter');
  });

  await t.test('4. Options Import & Customizer Sanitization', () => {
    const customizer = readFileSync(join(root, 'wp-content/themes/statement-collector-theme/inc/customizer.php'), 'utf8');
    assert.match(customizer, /statement_front_page_renderer/, 'Customizer includes statement_front_page_renderer');

    const optionsExport = readFileSync(join(root, 'wp-content/themes/statement-collector-theme/inc/admin/options-export.php'), 'utf8');
    assert.match(optionsExport, /statement_front_page_renderer/, 'OptionsExport includes statement_front_page_renderer in allowed keys');
    assert.match(optionsExport, /manage_options/, 'OptionsExport checks manage_options capability');
  });

  await t.test('5. Execute PHP Acceptance Suites', () => {
    const phpTests = [
      'tests/php/test-public-fixture-isolation.php',
      'tests/php/test-client-demo-collision.php',
      'tests/php/test-hpos-compatibility.php',
      'tests/php/test-theme-security-and-extensibility.php',
    ];

    for (const testFile of phpTests) {
      const output = execSync(`.local-tools\\php\\php.exe ${testFile}`, { cwd: root }).toString();
      assert.match(output, /PASS:/, `Execution of ${testFile} must pass cleanly`);
    }
  });
});
