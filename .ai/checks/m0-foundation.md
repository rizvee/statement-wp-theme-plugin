# Repository Foundation Check

Run:

```text
node scripts/verify-foundation.mjs
git diff --check
git status --short --branch
```

Pass requires:

- required repository files and directories exist
- architecture and permanent no-restock signals are present in their canonical context files
- theme and plugin runtime roots contain only the exact approved milestone file set
- no obvious credential pattern or generated release ZIP is tracked in the working tree
- Git output shows only intended repository-foundation files
- `TASKS.md` and `current-state.md` match the observed state
- first-party PHP lint runs when source exists; unavailable Composer/WP-CLI checks remain explicit limitations

Manually review the context files together for contradictions; automated keyword checks cannot prove semantic coherence.
