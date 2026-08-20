import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { packageTheme } from '../scripts/package-theme.mjs';
import { packagePlugin } from '../scripts/package-plugin.mjs';
import { packageFixtures } from '../scripts/package-fixtures.mjs';
import { packageClientDemo } from '../scripts/package-client-demo.mjs';
import { verifyPackage } from '../scripts/verify-package.mjs';
import { packageAll } from '../scripts/package-all.mjs';

const root = resolve(import.meta.dirname, '..');
const themeRoot = join(root, 'wp-content', 'themes', 'statement-collector-theme');
const coreRoot = join(root, 'wp-content', 'plugins', 'statement-collector-core');
const fixtureRoot = join(root, 'tools', 'statement-integration-fixtures');
const demoRoot = join(root, 'tools', 'statement-client-demo');

test('Sprint 10: Version Synchronization & Metadata Integrity', async (t) => {
	await t.test('Theme is canonical 0.13.0-rc.24', () => {
		const styleCss = readFileSync(join(themeRoot, 'style.css'), 'utf8');
		assert.match(styleCss, /Version:\s*0\.13\.0-rc\.24/);

		const functionsPhp = readFileSync(join(themeRoot, 'functions.php'), 'utf8');
		assert.match(functionsPhp, /define\(\s*['"]STATEMENT_COLLECTOR_THEME_VERSION['"]\s*,\s*['"]0\.13\.0-rc\.24['"]\s*\);/);
	});

	await t.test('Integration Fixtures is canonical 0.3.5 with dynamic admin heading', () => {
		const fixtureEntry = readFileSync(join(fixtureRoot, 'statement-integration-fixtures.php'), 'utf8');
		assert.match(fixtureEntry, /Version:\s*0\.3\.5/);
		assert.match(fixtureEntry, /STATEMENT_INTEGRATION_FIXTURES_VERSION',\s*'0\.3\.5'/);

		const gatewayPhp = readFileSync(join(fixtureRoot, 'src', 'StatementQaGateway.php'), 'utf8');
		assert.match(gatewayPhp, /public\s+const\s+VERSION\s*=\s*'0\.3\.5';/);

		const adminPhp = readFileSync(join(fixtureRoot, 'src', 'AdminPage.php'), 'utf8');
		assert.doesNotMatch(adminPhp, /v0\.3\.3/, 'AdminPage must not contain hardcoded v0.3.3');
		assert.match(adminPhp, /STATEMENT_INTEGRATION_FIXTURES_VERSION/, 'AdminPage must dynamically use version constant');
	});

	await t.test('Core Plugin is canonical 0.13.0-rc.15', () => {
		const coreEntry = readFileSync(join(coreRoot, 'statement-collector-core.php'), 'utf8');
		assert.match(coreEntry, /Version:\s*0\.13\.0-rc\.15/);
		assert.match(coreEntry, /STATEMENT_COLLECTOR_CORE_VERSION',\s*'0\.13\.0-rc\.15'/);
	});

	await t.test('Client Demo is canonical 0.2.7', () => {
		const demoEntry = readFileSync(join(demoRoot, 'statement-client-demo.php'), 'utf8');
		assert.match(demoEntry, /Version:\s*0\.2\.7/);
		assert.match(demoEntry, /STATEMENT_CLIENT_DEMO_VERSION',\s*'0\.2\.7'/);
	});
});

test('Sprint 10: Signature Hero Rebuild & Interaction Rail', async (t) => {
	await t.test('Hero template renders fine-line progress rail and semantic controls', () => {
		const heroPhp = readFileSync(join(themeRoot, 'template-parts', 'home', 'hero.php'), 'utf8');
		assert.match(heroPhp, /statement-hero-slider__rail/, 'Must contain progress rail container');
		assert.match(heroPhp, /statement-hero-slider__rail-segment/, 'Must contain progress rail segments');
		assert.match(heroPhp, /statement-hero-slider__rail-fill/, 'Must contain rail fill element');
		assert.match(heroPhp, /statement-hero-slider__counter/, 'Must contain numerical slide counter');
	});

	await t.test('Hero CSS provides luxury fine-line styling and cubic-bezier transitions', () => {
		const homeCss = readFileSync(join(themeRoot, 'assets', 'css', 'home.css'), 'utf8');
		assert.match(homeCss, /\.statement-hero-slider__rail-segment/, 'Must style rail segments');
		assert.match(homeCss, /\.statement-hero-slider__rail-fill/, 'Must style rail fill transitions');
	});

	await t.test('Hero JS controller operates rail and video synchronization', () => {
		const heroJs = readFileSync(join(themeRoot, 'assets', 'js', 'hero-slider.js'), 'utf8');
		assert.match(heroJs, /initHeroSlider/, 'Must initialize hero slider');
		assert.match(heroJs, /syncVideos/, 'Must manage video play/pause lifecycle');
		assert.match(heroJs, /prefers-reduced-motion/, 'Must respect accessibility reduced motion');
	});
});

test('Sprint 10: Drop Lookbook & Release Dossier Architecture', async (t) => {
	await t.test('Drop taxonomy template renders both lookbook diptych and collection register', () => {
		const dropPhp = readFileSync(join(themeRoot, 'taxonomy-statement_drop.php'), 'utf8');
		assert.match(dropPhp, /statement-drop-document__meta-bar/, 'Must contain meta bar');
		assert.match(dropPhp, /statement-drop-document__overview/, 'Must contain editorial overview & spec');
		assert.match(dropPhp, /statement-drop-lookbook/, 'Must contain lookbook pieces section');
		assert.match(dropPhp, /statement-register-list/, 'Must contain collection register list');
	});

	await t.test('Catalog CSS styles lookbook diptych grid', () => {
		const catalogCss = readFileSync(join(themeRoot, 'assets', 'css', 'catalog.css'), 'utf8');
		assert.match(catalogCss, /\.statement-drop-lookbook__grid/, 'Must define lookbook diptych grid');
	});
});

test('Sprint 10: PDP Commerce Pass & Clean Script Architecture', async (t) => {
	await t.test('Size guide template contains ZERO inline script tags', () => {
		const sizeGuidePhp = readFileSync(join(themeRoot, 'template-parts', 'product', 'size-guide.php'), 'utf8');
		assert.doesNotMatch(sizeGuidePhp, /<script/i, 'size-guide.php must contain no inline script');
		assert.match(sizeGuidePhp, /statement-size-guide-dialog/, 'Must render accessible dialog element');
	});

	await t.test('product.js manages size guide modal and sticky CTA bar cleanly', () => {
		const productJs = readFileSync(join(themeRoot, 'assets', 'js', 'product.js'), 'utf8');
		assert.match(productJs, /statement-size-guide-open/, 'Must handle size guide open trigger');
		assert.match(productJs, /showModal/, 'Must invoke HTML5 dialog showModal');
		assert.match(productJs, /statement-mobile-sticky-bar/, 'Must manage mobile sticky CTA');
	});

	await t.test('Product card template supports secondary gallery image hover reveal', () => {
		const cardPhp = readFileSync(join(themeRoot, 'template-parts', 'product', 'card.php'), 'utf8');
		assert.match(cardPhp, /get_gallery_image_ids/, 'Must query gallery images');
		assert.match(cardPhp, /statement-piece__image--secondary/, 'Must output secondary hover image markup');

		const cardCss = readFileSync(join(themeRoot, 'assets', 'css', 'product-card.css'), 'utf8');
		assert.match(cardCss, /\.statement-piece__image--secondary/, 'Must style secondary hover image');
	});
});

test('Sprint 10: Text-Only About & Contact Architectural Guarantee', async (t) => {
	await t.test('About page is 100% text-only with zero images and authentic claims', () => {
		const aboutPhp = readFileSync(join(themeRoot, 'page-about.php'), 'utf8');
		assert.doesNotMatch(aboutPhp, /<img/i);
		assert.doesNotMatch(aboutPhp, /<picture/i);
		assert.doesNotMatch(aboutPhp, /<svg/i);
		assert.match(aboutPhp, /CRAFTED\.\s*LIMITED\.\s*NEVER\s*RESTOCKED\./);
	});

	await t.test('Contact page is 100% text-only with info@mystatement.store and @statement.au', () => {
		const contactPhp = readFileSync(join(themeRoot, 'page-contact.php'), 'utf8');
		assert.doesNotMatch(contactPhp, /<img/i);
		assert.doesNotMatch(contactPhp, /<picture/i);
		assert.doesNotMatch(contactPhp, /<svg/i);
		assert.match(contactPhp, /info@mystatement\.store/);
		assert.match(contactPhp, /@statement\.au/);
	});
});

test('Sprint 10: Full Package Orchestration & Single-Root Verifier', () => {
	const themePkg = packageTheme('0.13.0-rc.24');
	assert.ok(existsSync(themePkg.path));
	const themeVerify = verifyPackage(themePkg.path, '0.13.0-rc.24');
	assert.ok(themeVerify.ok, `Theme verification failed: ${themeVerify.errors?.join(', ')}`);

	const pluginPkg = packagePlugin('0.13.0-rc.15');
	assert.ok(existsSync(pluginPkg.path));
	const pluginVerify = verifyPackage(pluginPkg.path, '0.13.0-rc.15');
	assert.ok(pluginVerify.ok, `Plugin verification failed: ${pluginVerify.errors?.join(', ')}`);

	const fixturePkg = packageFixtures('0.3.5');
	assert.ok(existsSync(fixturePkg.path));

	const demoPkg = packageClientDemo('0.2.7');
	assert.ok(existsSync(demoPkg.path));
	const demoVerify = verifyPackage(demoPkg.path, '0.2.7');
	assert.ok(demoVerify.ok, `Demo package verification failed: ${demoVerify.errors?.join(', ')}`);

	const { manifest } = packageAll({ silent: true });
	assert.equal(manifest.theme.header_version, '0.13.0-rc.24');
	assert.equal(manifest.plugin.header_version, '0.13.0-rc.15');
	assert.equal(manifest.deployment_authorized, false);
});
