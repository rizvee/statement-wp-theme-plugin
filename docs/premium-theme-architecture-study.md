# Premium WordPress & WooCommerce Theme Architecture Study

**Author:** Statement Engineering
**Project:** Statement Collector's Piece (`statement-collector-theme` & `statement-collector-core`)
**Sprint:** Visual Sprint 05 (Extensibility & Premium Finish)
**Date:** 2026-08-18

---

## 1. Executive Summary

This architecture study distills engineering best practices and architectural patterns from modern enterprise WordPress themes (Astra, Avada, GeneratePress) and official WordPress/WooCommerce core guidelines into a bespoke, lightweight, and extensible framework tailored to the **Statement** luxury fashion identity.

---

## 2. Core Architectural Pillars

### Pillar 1: Layered Configuration Hierarchy
A mature theme follows a strict, predictable resolution order:
1. **Design Tokens (`theme.json` & CSS Custom Properties)**: Authoritative foundational values for colors, typography, spacing, and transition speeds.
2. **Global Customizer Defaults (`theme_mods`)**: User-customizable appearance values that dynamically override root CSS custom properties.
3. **Page-Level Design Overrides (Post Meta)**: Contextual layout, header transparency, or footer toggles applied per-page.
4. **Component-Level Inline Control**: Exact element rendering controlled by dedicated template parts.

### Pillar 2: Hook-Driven Public Extension API
To ensure third-party plugins, child themes, and agency developers can customize layout without modifying template files:
- All major layout boundaries provide `do_action('statement_theme_before_*')` and `do_action('statement_theme_after_*')`.
- Key visual parameters (shop columns, container widths, hero slides, breadcrumb visibility) pass through `apply_filters('statement_theme_*', $val)`.
- All hooks are strictly namespaced to `statement_theme_`.

### Pillar 3: Child-Theme First File Resolution
- **URI Resolution**: Use `get_theme_file_uri()` instead of `get_template_directory_uri()` for assets (CSS, JS, images) to allow child themes to override files seamlessly.
- **Path Resolution**: Use `get_theme_file_path()` instead of `get_template_directory()` for required PHP modules or templates.
- **Template Resolution**: Use `get_template_part()` and `locate_template()` to respect child theme template overrides.

### Pillar 4: Modular Compatibility Adapters
Rather than polluting the main `functions.php`, external plugin support is decoupled into self-contained compatibility adapters in `inc/compatibility/`:
- `woocommerce.php`: WooCommerce catalog, breadcrumbs, notices, and wrapper integration.
- `woo-blocks.php`: WooCommerce Blocks (`.wc-block-cart`, `.wc-block-checkout`) non-destructive styling.
- `elementor.php`: Official Elementor Theme Locations (`header`, `footer`, `single`, `archive`) and canvas/full-width templates.
- `gutenberg.php`: Block editor styling, wide alignment, block patterns, and color palette synchronization.
- `seo.php`: Coexistence with Yoast and Rank Math, preventing duplicate title/meta output.
- `forms.php`: Safe, scoped styling for Contact Form 7, WPForms, and MailPoet.
- `caching.php`: Safe operation under WP Rocket, LiteSpeed, Jetpack, and Page Optimize.

### Pillar 5: WooCommerce Upgrade-Safe Architecture & HPOS
- **Minimal Template Overrides**: Use WooCommerce hooks wherever possible; retain template overrides only when markup alterations cannot be achieved via action hooks.
- **Version Tracking**: Explicit `@version` annotations matching active WooCommerce versions (11.0.1) with automated drift tests.
- **HPOS (High-Performance Order Storage)**: All order metadata reading and writing in Statement Core uses official `WC_Order` CRUD methods (`get_meta()`, `update_meta_data()`, `save()`), completely avoiding raw `wp_postmeta` SQL. Declare `before_woocommerce_init` HPOS compatibility feature flag.

### Pillar 6: Strict Public QA & Fixture Isolation
Public-facing loops, REST endpoints, Store API queries, and archive listings must enforce an internal fixture barrier:
- Exclude `_statement_fixture = 1`.
- Exclude SKUs starting with `TEST-`.
- Exclude entities assigned to QA-only fixture terms.
- Preserve legitimate Client Demo products (`_statement_client_demo = 1`, `STMT-CD-*`).

---

## 3. Implementation Roadmap for Visual Sprint 05

1. **Core PublicApi Isolation**: Patch `PublicApi::get_archive_products()` and `Catalog\Visibility` to prevent QA fixtures leaking to public archive routes.
2. **Modular Theme Refactoring**: Split theme into `inc/setup.php`, `inc/assets.php`, `inc/hooks.php`, `inc/customizer.php`, `inc/design-tokens.php`, `inc/compatibility/`, and `inc/admin/`.
3. **Design Tokens & Global Customizer**: Authoritative CSS variables with Customizer controls across Global, Header, Footer, Home, Shop, Product, and Mobile.
4. **Starter Child Theme**: Provide `tools/statement-collector-child/` with package ZIP.
5. **Admin Setup & Health Screen**: Create `Appearance -> Statement` dashboard showing system status, prerequisite checks, and safe setup actions.
6. **Visual QA Perfection**: Systematically address the 14 screenshot issues across mobile, tablet, and desktop views.

