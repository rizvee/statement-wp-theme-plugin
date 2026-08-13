# Repository Verification

Use this project skill after repository, theme, plugin, or packaging changes.

1. Inspect `git status --short --branch` and the affected diff.
2. Run `node scripts/verify-foundation.mjs` as the dependency-light baseline.
3. Run the narrowest milestone-specific tests.
4. If PHP is available, require `php -l` for changed PHP files; never claim syntax verification when it is unavailable.
5. For future release ZIPs, inspect package contents against `deployment-rules.md` before any upload.
6. Run `git diff --check`, review unexpected/unrelated files, and map each completion claim to command output.

Do not deploy, publish, push, activate packages, or modify external systems as part of verification.
