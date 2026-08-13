# Design System Foundation

`theme.json` v3 is the canonical source for palette, font-family, fluid type, spacing, and content-width presets. Theme CSS consumes WordPress-generated `--wp--preset--*` and global layout variables instead of maintaining a parallel token set.

## Canonical contract

- Primary message: **Crafted. Limited. Never Restocked.**
- Palette presets: Gallery Ivory, Warm White, Ink Navy, Near Black, Soft Graphite, Border Grey, and accent-only Brass.
- Display stack: Instrument Serif with system serif fallbacks. UI stack: Inter with system sans-serif fallbacks. No webfont files or external font requests are bundled.
- Normal content width is `760px`; wide/editorial width is `1440px`; page gutters and type/spacing scales are fluid.
- Presentation should support the permanence and clarity of limited releases without suggesting replenishment or public production totals.
- Accessibility and responsive behavior are architectural requirements, not later decoration.
- Prefer lightweight CSS tokens, semantic HTML, native interactions, responsive images, and selective native blocks.
- Avoid Elementor/page-builder dependencies, unnecessary frontend frameworks, oversized bundles, and animation libraries for trivial effects.

`base.css` owns document/accessibility defaults; `layout.css` owns the small `statement-*` layout primitive set. Branded components begin in later milestones.
