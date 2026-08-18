import test from "node:test";
import assert from "node:assert/strict";
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const root = path.resolve(__dirname, "..");

test("M17: Theme modular structure and extension hooks", () => {
	const themePath = path.join(root, "wp-content/themes/statement-collector-theme");

	// 1. Modular PHP files exist
	const requiredIncFiles = [
		"inc/setup.php",
		"inc/design-tokens.php",
		"inc/hooks.php",
		"inc/page-meta.php",
		"inc/customizer.php",
		"inc/product.php",
		"inc/cart.php",
		"inc/checkout.php",
		"inc/assets.php",
		"inc/navigation.php",
		"inc/home.php",
		"inc/catalog.php",
		"inc/admin/health.php",
		"inc/admin/options-export.php",
		"inc/admin/setup-screen.php"
	];

	for (const f of requiredIncFiles) {
		const full = path.join(themePath, f);
		assert.ok(fs.existsSync(full), `Required theme inc file missing: ${f}`);
	}

	// 2. Extension hooks are registered in inc/hooks.php
	const hooksCode = fs.readFileSync(path.join(themePath, "inc/hooks.php"), "utf8");
	const requiredHooks = [
		"statement_theme_before_header",
		"statement_theme_after_header",
		"statement_theme_before_main",
		"statement_theme_after_main",
		"statement_theme_before_product_card",
		"statement_theme_after_product_card",
		"statement_theme_before_footer",
		"statement_theme_after_footer",
		"statement_theme_shop_columns",
		"statement_theme_show_breadcrumbs",
		"statement_theme_design_tokens_css"
	];

	for (const hook of requiredHooks) {
		assert.ok(hooksCode.includes(hook), `Hook ${hook} not defined in inc/hooks.php`);
	}

	// 3. Child-theme file resolution
	const assetsCode = fs.readFileSync(path.join(themePath, "inc/assets.php"), "utf8");
	assert.ok(assetsCode.includes("get_theme_file_uri"), "Assets must use get_theme_file_uri for child theme inheritance");

	// 4. Starter Child Theme package
	const childPath = path.join(root, "tools/statement-collector-child");
	assert.ok(fs.existsSync(path.join(childPath, "style.css")), "Child theme style.css exists");
	assert.ok(fs.existsSync(path.join(childPath, "functions.php")), "Child theme functions.php exists");
	assert.ok(fs.existsSync(path.join(childPath, "README.md")), "Child theme README.md exists");

	const childStyle = fs.readFileSync(path.join(childPath, "style.css"), "utf8");
	assert.ok(childStyle.includes("Template: statement-collector-theme"), "Child theme references parent template statement-collector-theme");
	assert.ok(childStyle.includes("Version: 0.1.0"), "Child theme version is 0.1.0");

	// 5. Theme version bump
	const themeStyle = fs.readFileSync(path.join(themePath, "style.css"), "utf8");
	assert.ok(themeStyle.includes("Version: 0.13.0-rc.14"), "Theme style.css is version 0.13.0-rc.14");

	const functionsPhp = fs.readFileSync(path.join(themePath, "functions.php"), "utf8");
	assert.ok(functionsPhp.includes("STATEMENT_COLLECTOR_THEME_VERSION', '0.13.0-rc.14'"), "Theme constant is 0.13.0-rc.14");
});

