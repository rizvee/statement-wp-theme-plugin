import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { packageTheme } from '../scripts/package-theme.mjs';
import { packagePlugin } from '../scripts/package-plugin.mjs';
import { verifyPackage } from '../scripts/verify-package.mjs';
import { packageAll } from '../scripts/package-all.mjs';

const root = resolve(import.meta.dirname, '..');
const themeRoot = join(root, 'wp-content', 'themes', 'statement-collector-theme');
const coreRoot = join(root, 'wp-content', 'plugins', 'statement-collector-core');
const demoRoot = join(root, 'tools', 'statement-client-demo');

test('Sprint 11: Flagship Luxury Convergence & Theme Version 0.13.0-rc.24', async (t) => {
	await t.test('Theme is canonical 0.13.0-rc.24 across style.css and functions.php', () => {
		const styleCss = readFileSync(join(themeRoot, 'style.css'), 'utf8');
		assert.match(styleCss, /Version:\s*0\.13\.0-rc\.24/);

		const functionsPhp = readFileSync(join(themeRoot, 'functions.php'), 'utf8');
		assert.match(functionsPhp, /define\(\s*['"]STATEMENT_COLLECTOR_THEME_VERSION['"]\s*,\s*['"]0\.13\.0-rc\.24['"]\s*\);/);
		assert.match(functionsPhp, /require_once\s+STATEMENT_COLLECTOR_THEME_PATH\s*\.\s*'inc\/icons\.php'/, 'functions.php must include inc/icons.php');
	});

	await t.test('Thin-line SVG icon subsystem is defined and exported', () => {
		const iconsPhp = readFileSync(join(themeRoot, 'inc', 'icons.php'), 'utf8');
		assert.match(iconsPhp, /function get_statement_icon/, 'inc/icons.php must define get_statement_icon');
		assert.match(iconsPhp, /function render_statement_icon/, 'inc/icons.php must define render_statement_icon');
		assert.match(iconsPhp, /'search'\s*=>/, 'Must support search icon');
		assert.match(iconsPhp, /'account'\s*=>/, 'Must support account icon');
		assert.match(iconsPhp, /'bag'\s*=>/, 'Must support bag icon');
		assert.match(iconsPhp, /'size-guide'\s*=>/, 'Must support size guide ruler icon');
		assert.match(iconsPhp, /'instagram'\s*=>/, 'Must support instagram icon');
	});
});

test('Sprint 11: Split Luxury Header & Full-Screen Mobile Takeover', async (t) => {
	await t.test('Header template renders split layout with micro-labels and SVG icons', () => {
		const headerPhp = readFileSync(join(themeRoot, 'template-parts', 'header', 'site-header.php'), 'utf8');
		assert.match(headerPhp, /statement-primary-navigation/, 'Must have navigation section');
		assert.match(headerPhp, /statement-brand/, 'Must have centered brand section');
		assert.match(headerPhp, /statement-header-utilities/, 'Must have utilities section');
		assert.match(headerPhp, /render_statement_icon\(\s*'search'/, 'Must render search icon');
		assert.match(headerPhp, /render_statement_icon\(\s*'account'/, 'Must render account icon');
		assert.match(headerPhp, /render_statement_icon\(\s*'bag'/, 'Must render bag icon');
		assert.match(headerPhp, /statement-header-bag-pill/, 'Must render bag count badge');
	});

	await t.test('Mobile navigation template renders full-screen takeover modal with numbered index', () => {
		const mobilePhp = readFileSync(join(themeRoot, 'template-parts', 'header', 'mobile-navigation.php'), 'utf8');
		assert.match(mobilePhp, /statement-mobile-dialog/, 'Must render mobile dialog');
		assert.match(mobilePhp, /statement-mobile-dialog__close/, 'Must have close button');
		assert.match(mobilePhp, /statement-mobile-dialog__meta/, 'Must have metadata header');
		assert.match(mobilePhp, /render_mobile_primary_navigation/, 'Must render mobile primary navigation');
		assert.match(mobilePhp, /statement-mobile-dialog__channels/, 'Must have concierge channels');

		const navPhp = readFileSync(join(themeRoot, 'inc', 'navigation.php'), 'utf8');
		assert.match(navPhp, /statement-mobile-nav-index/, 'Must format numbered indices in mobile navigation');
	});

	await t.test('Header CSS provides luxury styling, glassmorphism, and compact scroll transition', () => {
		const headerCss = readFileSync(join(themeRoot, 'assets', 'css', 'header.css'), 'utf8');
		assert.match(headerCss, /\.statement-site-header\.is-scrolled/, 'Must support compact scrolled header state');
		assert.match(headerCss, /\.statement-mobile-dialog/, 'Must style mobile dialog takeover');
		assert.match(headerCss, /\.statement-dialog::backdrop/, 'Must style backdrop filter');
	});
});

test('Sprint 11: 3-Chapter Cinematic Editorial Hero System', async (t) => {
	await t.test('Hero template configures 3 curated editorial chapters and studio assets', () => {
		const heroPhp = readFileSync(join(themeRoot, 'template-parts', 'home', 'hero.php'), 'utf8');
		assert.match(heroPhp, /statement-black-nwhite-hoodie-n-jacket-product-front\.webp/, 'Must include Slide 1 genesis shot');
		assert.match(heroPhp, /statement-black-nwhite-hoodie-n-jacket-product-front-02\.webp/, 'Must include Slide 2 collection identity shot');
		assert.match(heroPhp, /statement-black-nwhite-hoodie-n-jacket-product-front-03\.webp/, 'Must include Slide 3 relic tension study shot');
		assert.match(heroPhp, /NOT MASS PRODUCED\./, 'Must feature minimal luxury copy');
		assert.match(heroPhp, /render_statement_icon\(\s*'arrow-left'/, 'Must use SVG arrow-left control');
		assert.match(heroPhp, /render_statement_icon\(\s*'arrow-right'/, 'Must use SVG arrow-right control');
	});

	await t.test('New studio assets exist in theme assets directory', () => {
		const img1 = join(themeRoot, 'assets', 'images', 'statement-black-nwhite-hoodie-n-jacket-product-front.webp');
		const img2 = join(themeRoot, 'assets', 'images', 'statement-black-nwhite-hoodie-n-jacket-product-front-02.webp');
		const img3 = join(themeRoot, 'assets', 'images', 'statement-black-nwhite-hoodie-n-jacket-product-front-03.webp');
		assert.ok(existsSync(img1), 'Hero slide 1 asset exists');
		assert.ok(existsSync(img2), 'Hero slide 2 asset exists');
		assert.ok(existsSync(img3), 'Hero slide 3 asset exists');
	});
});

test('Sprint 11: PDP Experience & Size Guide Modal', async (t) => {
	await t.test('Size guide dialog renders thin-line ruler icon and accessible dialog', () => {
		const sizeGuidePhp = readFileSync(join(themeRoot, 'template-parts', 'product', 'size-guide.php'), 'utf8');
		assert.match(sizeGuidePhp, /render_statement_icon\(\s*'size-guide'/, 'Must render size-guide icon');
		assert.match(sizeGuidePhp, /statement-size-guide-dialog/, 'Must render HTML5 dialog');
		assert.match(sizeGuidePhp, /render_statement_icon\(\s*'close'/, 'Must render close icon');
	});

	await t.test('Product details accordion renders thin-line SVG plus/minus indicators', () => {
		const detailsPhp = readFileSync(join(themeRoot, 'template-parts', 'product', 'details.php'), 'utf8');
		assert.match(detailsPhp, /render_statement_icon\(\s*'plus'/, 'Must render plus icon');
		assert.match(detailsPhp, /render_statement_icon\(\s*'minus'/, 'Must render minus icon');
	});
});

test('Sprint 11: Master 4-Column Luxury Footer', async (t) => {
	await t.test('Footer template renders 4-column structured layout with approved brand signature', () => {
		const footerPhp = readFileSync(join(themeRoot, 'template-parts', 'footer', 'site-footer.php'), 'utf8');
		assert.match(footerPhp, /statement-site-footer__grid/, 'Must render footer grid');
		assert.match(footerPhp, /statement-site-footer__col--brand/, 'Must have brand column');
		assert.match(footerPhp, /statement-site-footer__col--nav/, 'Must have navigation column');
		assert.match(footerPhp, /statement-site-footer__col--services/, 'Must have services column');
		assert.match(footerPhp, /statement-site-footer__col--channels/, 'Must have channels column');
		assert.match(footerPhp, /Crafted\.\s*Limited\.\s*Never Restocked\./, 'Must feature exact approved brand signature');
		assert.match(footerPhp, /render_statement_icon\(\s*'instagram'/, 'Must render instagram icon');
		assert.match(footerPhp, /render_statement_icon\(\s*'email'/, 'Must render email icon');
	});

	await t.test('Footer CSS provides luxury 4-column grid layout', () => {
		const footerCss = readFileSync(join(themeRoot, 'assets', 'css', 'footer.css'), 'utf8');
		assert.match(footerCss, /\.statement-site-footer__grid/, 'Must style footer grid');
		assert.match(footerCss, /\.statement-site-footer__col-heading/, 'Must style column headings');
	});
});

test('Sprint 11: Absolute Scarcity Invariant & Zero Public Production Cap Regression', async (t) => {
	await t.test('Zero forbidden production-limit strings across theme, core, and demo templates', () => {
		const forbiddenPatterns = [
			/50\s*pieces/i,
			/100\s*pieces/i,
			/200\s*pieces/i,
			/EDITION:\s*\d+/i,
			/Limited Edition\s*\/\s*\d+/i,
			/\/\s*50\b/,
			/production cap/i,
			/lifetime total/i,
			/individually numbered/i,
			/remaining production/i,
		];

		const filesToCheck = [
			join(themeRoot, 'template-parts', 'home', 'hero.php'),
			join(themeRoot, 'taxonomy-statement_drop.php'),
			join(themeRoot, 'page-archive.php'),
			join(themeRoot, 'template-parts', 'product', 'summary.php'),
			join(themeRoot, 'template-parts', 'product', 'card.php'),
			join(themeRoot, 'template-parts', 'footer', 'site-footer.php'),
			join(themeRoot, 'template-parts', 'header', 'mobile-navigation.php'),
			join(themeRoot, 'page-about.php'),
			join(themeRoot, 'page-contact.php'),
		];

		for (const file of filesToCheck) {
			if (existsSync(file)) {
				const content = readFileSync(file, 'utf8');
				for (const pattern of forbiddenPatterns) {
					assert.ok(!pattern.test(content), `Forbidden production count pattern ${pattern} found in ${file}`);
				}
			}
		}
	});

	await t.test('Spec sheet uses RELEASE PIECES to avoid ambiguous production count interpretation', () => {
		const dropTaxonomy = readFileSync(join(themeRoot, 'taxonomy-statement_drop.php'), 'utf8');
		assert.match(dropTaxonomy, /RELEASE PIECES/, 'taxonomy-statement_drop.php must use unambiguous RELEASE PIECES label');
	});
});

test('Sprint 11: Deterministic Packaging & Verification', async (t) => {
	await t.test('Theme 0.13.0-rc.24 packages and verifies with 100% clean check', () => {
		const themeResult = packageTheme('0.13.0-rc.24');
		assert.ok(existsSync(themeResult.path), 'Theme ZIP must exist in dist/');
		assert.equal(themeResult.version, '0.13.0-rc.24');

		const verifyResult = verifyPackage(themeResult.path, '0.13.0-rc.24');
		assert.ok(verifyResult.ok, `Theme verification must pass: ${verifyResult.errors?.join(', ')}`);
		assert.equal(verifyResult.headerVersion, '0.13.0-rc.24');
		assert.equal(verifyResult.constantVersion, '0.13.0-rc.24');
	});
});
