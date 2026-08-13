# Deployment Rules

- Integration/production URL: `https://mystatement.store/`
- Hosting: WordPress.com Atomic.
- The current production site remains Coming Soon/private.
- This local Git repository is the source of truth. Never edit production files directly.
- Never auto-deploy or modify the live site from this repository workflow.
- Releases are manually uploaded, versioned ZIPs:
  - `statement-collector-theme-vX.Y.Z.zip`
  - `statement-collector-core-vX.Y.Z.zip`
- Future packages must exclude `.git`, repository AI/dev files, runtime-unnecessary tests, local caches, secrets, `node_modules`, and unnecessary build artifacts.
- The Assembler/old Elementor homepage remains the rollback fallback until the replacement is verified.
- Elementor removal is a later launch milestone, not a prerequisite for local development.
- Upload, activation, production changes, deployment, and rollback actions require an explicit release task and confirmation.
