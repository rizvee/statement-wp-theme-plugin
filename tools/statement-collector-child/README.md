# Statement Collector's Piece Child Theme

A lightweight, upgrade-safe starter child theme for **Statement Collector's Piece**.

## Installation

1. Ensure parent theme **Statement Collector's Piece** (`statement-collector-theme`) is installed in `wp-content/themes/`.
2. Upload and activate `statement-collector-child` via **Appearance > Themes**.
3. Add your bespoke CSS to `style.css` and custom PHP hooks to `functions.php`.

## Customization with Extension Hooks

The parent theme exposes official layout hooks:
- `statement_theme_before_header`
- `statement_theme_after_header`
- `statement_theme_before_main`
- `statement_theme_after_main`
- `statement_theme_before_product_card`
- `statement_theme_after_product_card`
- `statement_theme_before_footer`
- `statement_theme_after_footer`

Filters:
- `statement_theme_shop_columns`
- `statement_theme_show_breadcrumbs`
- `statement_theme_design_tokens_css`
