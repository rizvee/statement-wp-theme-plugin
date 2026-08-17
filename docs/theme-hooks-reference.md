# Statement Theme Hooks & Extension Reference

**Author:** Statement Engineering
**Version:** 0.13.0-rc.9
**Namespace:** `statement_theme_`

---

## 1. Action Hooks

| Hook Name | Location | Parameters | Description |
| :--- | :--- | :--- | :--- |
| `statement_theme_before_header` | `header.php` | None | Fires immediately before the site header begins. Useful for promo announcement bars. |
| `statement_theme_after_header` | `header.php` | None | Fires immediately after the header terminates. |
| `statement_theme_before_main` | Page templates | None | Fires immediately before `#primary` main content container. |
| `statement_theme_after_main` | Page templates | None | Fires immediately after `#primary` main content container closes. |
| `statement_theme_before_product_card` | `template-parts/product/card.php` | `$product` (WC_Product) | Fires inside product card before image media. |
| `statement_theme_after_product_card` | `template-parts/product/card.php` | `$product` (WC_Product) | Fires inside product card after body and price. |
| `statement_theme_before_footer` | `footer.php` | None | Fires immediately before site footer markup. |
| `statement_theme_after_footer` | `footer.php` | None | Fires immediately after site footer closes. |

---

## 2. Filter Hooks

| Filter Name | Default Value | Return Type | Description |
| :--- | :--- | :--- | :--- |
| `statement_theme_shop_columns` | `3` (or Customizer) | `int` (2, 3, or 4) | Controls desktop grid column count in Shop and catalog views. |
| `statement_theme_show_breadcrumbs` | `false` | `bool` | Controls whether WooCommerce breadcrumb trail is rendered. |
| `statement_theme_design_tokens_css` | Root CSS variable block | `string` | Filters inline CSS custom properties injected in `wp_head`. |

---

## 3. Usage Examples

```php
// Add top announcement banner in Child Theme
add_action( 'statement_theme_before_header', function() {
    echo '<div class="statement-banner" style="background:#111;color:#fff;text-align:center;font-size:11px;padding:8px 12px;letter-spacing:0.15em;text-transform:uppercase;">COMPLIMENTARY DOMESTIC EXPRESS DELIVERY ON DROP 001</div>';
} );

// Enforce 2-column editorial shop layout programmatically
add_filter( 'statement_theme_shop_columns', function( $cols ) {
    return 2;
} );
```

