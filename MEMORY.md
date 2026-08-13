# Stable Project Memory

- The local Git repository is the source of truth for `https://mystatement.store/`, hosted on WordPress.com Atomic.
- The custom standalone theme is `statement-collector-theme`; it has no parent theme and no Elementor dependency.
- Durable business/domain behavior belongs in `statement-collector-core`; presentation belongs in the theme.
- The immutable release promise is: one release → limited availability → sold out → never restocked → permanent archive.
- Primary brand message: **Crafted. Limited. Never Restocked.**
- Piece numbers may exist without exposing total production.
- Releases are manually uploaded versioned ZIPs; no repository workflow may auto-deploy or directly edit production.
- The existing Assembler/Elementor homepage remains a rollback fallback until its replacement is verified. Elementor removal is a later launch milestone.
- Runtime package names use `statement-collector-theme-vX.Y.Z.zip` and `statement-collector-core-vX.Y.Z.zip`.
