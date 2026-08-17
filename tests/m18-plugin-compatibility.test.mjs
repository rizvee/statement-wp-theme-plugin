import test from "node:test";
import assert from "node:assert/strict";
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const root = path.resolve(__dirname, "..");

test("M18: Plugin compatibility adapters and standards", () => {
	const themePath = path.join(root, "wp-content/themes/statement-collector-theme");
	const compatPath = path.join(themePath, "inc/compatibility");

	// 1. Compatibility adapter modules exist
	const compatModules = [
		"woocommerce.php",
		"woo-blocks.php",
		"elementor.php",
		"gutenberg.php",
		"seo.php",
		"jetpack.php",
		"forms.php",
		"caching.php"
	];

	for (const mod of compatModules) {
		assert.ok(fs.existsSync(path.join(compatPath, mod)), `Missing compatibility adapter: ${mod}`);
	}

	// 2. HPOS declaration in Core & WooCommerce support in Theme
	const coreCode = fs.readFileSync(path.join(root, "wp-content/plugins/statement-collector-core/statement-collector-core.php"), "utf8");
	assert.ok(coreCode.includes("custom_order_tables"), "Core declares HPOS custom_order_tables compatibility");
	assert.ok(coreCode.includes("declare_compatibility"), "Core declares FeaturesUtil compatibility");

	const wooCode = fs.readFileSync(path.join(compatPath, "woocommerce.php"), "utf8");
	assert.ok(wooCode.includes("'woocommerce'"), "Theme declares WooCommerce core theme support");

	// 3. Elementor Theme Location registration in elementor.php
	const elemCode = fs.readFileSync(path.join(compatPath, "elementor.php"), "utf8");
	assert.ok(elemCode.includes("elementor/theme/register_locations"), "Elementor theme location registration hooked");
	assert.ok(elemCode.includes("'header'"), "Elementor header location declared");
	assert.ok(elemCode.includes("'footer'"), "Elementor footer location declared");

	// 4. Gutenberg Block Patterns in gutenberg.php
	const gbCode = fs.readFileSync(path.join(compatPath, "gutenberg.php"), "utf8");
	assert.ok(gbCode.includes("statement/editorial-hero"), "Statement editorial hero pattern registered");
	assert.ok(gbCode.includes("statement/brand-cta"), "Statement brand CTA pattern registered");

	// 5. Scoped WooCommerce blocks stylesheet
	assert.ok(fs.existsSync(path.join(themePath, "assets/css/woo-blocks.css")), "Scoped woo-blocks.css exists");
	const wooBlocksCss = fs.readFileSync(path.join(themePath, "assets/css/woo-blocks.css"), "utf8");
	assert.ok(wooBlocksCss.includes(".wc-block-components-button"), "Styles Cart/Checkout block buttons");
});
