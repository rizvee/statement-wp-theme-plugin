# Statement Child Theme Customization Guide

---

## 1. Overview
The Statement Collector theme is designed with full child-theme awareness. All asset resolution uses `get_theme_file_uri()`, template locating uses `locate_template()`, and required components use `get_theme_file_path()`.

---

## 2. Setting Up a Child Theme

1. Locate `tools/statement-collector-child/` (or install `dist/statement-collector-child-0.1.0.zip`).
2. Upload the child theme folder to `wp-content/themes/statement-collector-child`.
3. In `wp-admin > Appearance > Themes`, activate **Statement Collector's Piece Child**.

---

## 3. Safe Customization Practices

- **Never edit parent theme files directly**: All overrides belong in the child theme.
- **Custom CSS**: Place custom CSS rules in `style.css`.
- **Custom PHP Logic**: Place filters and action hook listeners in `functions.php`.
- **Template Part Overrides**: Copy any template part (e.g. `template-parts/header/site-header.php`) to the exact relative path in your child theme folder to override it safely.
