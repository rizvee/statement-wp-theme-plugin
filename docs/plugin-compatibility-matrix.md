# Statement Collector Plugin Compatibility Matrix

**Updated:** 2026-08-18
**Theme Candidate:** `0.13.0-rc.9`
**Core Candidate:** `0.13.0-rc.12`

---

## 1. Compatibility Summary Table

| Plugin / Framework | Compatibility Level | Integration Mechanism | Status Notes |
| :--- | :--- | :--- | :--- |
| **WordPress Core 6.0–6.7+** | FULLY SUPPORTED | Native theme supports, HTML5 markup, theme.json v3 | Verified clean |
| **WooCommerce 8.0–11.0.1** | FULLY SUPPORTED | Native templates, HPOS declared, cart/checkout hooks | Verified clean |
| **WooPayments** | FULLY SUPPORTED | Unobtrusive payment gateway styling; iframe protection | Verified clean |
| **WooCommerce Blocks** | SUPPORTED | Scoped `.wc-block-*` typography/palette styling in `woo-blocks.css` | Verified clean |
| **Elementor Pro Theme Builder** | SUPPORTED | Official Theme Location API (`header`, `footer`, `single`, `archive`) | Verified clean |
| **Gutenberg Block Editor** | FULLY SUPPORTED | `theme.json` tokens, editor styles, wide alignment, block patterns | Verified clean |
| **Yoast SEO** | PASSIVE COMPATIBLE | Automatic fallback meta deactivation when Yoast is detected | Verified clean |
| **Rank Math SEO** | PASSIVE COMPATIBLE | Automatic fallback meta deactivation when Rank Math is detected | Verified clean |
| **Contact Form 7** | PASSIVE COMPATIBLE | Form inputs inherit theme design tokens; `wpcf7_autop` filtered | Verified clean |
| **WPForms** | PASSIVE COMPATIBLE | Form elements scoped to inherit sans typography and border styles | Verified clean |
| **MailPoet** | PASSIVE COMPATIBLE | Email signup inputs inherit theme palette without style conflicts | Verified clean |
| **WP Rocket / Page Optimize** | FULLY SUPPORTED | Strict script dependencies, zero inline eval, no-store headers respected | Verified clean |
| **LiteSpeed Cache** | FULLY SUPPORTED | Cache headers and Vary headers from Core Private Access preserved | Verified clean |
| **Jetpack** | PASSIVE COMPATIBLE | Responsive videos support declared; privacy query scrub active | Verified clean |

