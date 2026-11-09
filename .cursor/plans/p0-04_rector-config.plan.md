---
name: p0-rector-config
overview: "Commit a rector.php that targets PHP 7.4, the runtime the code runs on now, and pin Rector at 2.8.4 so a dry run on the current code is empty. The dry run is run by hand as the check."
todos:
  - id: verify
    content: Verify the preconditions below; save the dry-run output to a file outside the repository
    status: pending
  - id: pr1-rector-config
    content: "PR 1: Add rector/rector 2.8.4 as dev dependency and commit rector.php targeting PHP 7.4 (Approval: new dev dependency)"
    status: pending
  - id: validate
    content: Run the Validation checks; golden files unchanged; update MIGRATION.md and the master plan; one commit per PR
    status: pending
isProject: false
---

# p0-rector-config: Committed Rector configuration

This is todo `p0-rector-config` from [the master plan](need2lift-migration.plan.md) (section "Phase 0", `p0-rector-config`). Read [ADR-0006](../../docs/adr/0006-safety-net.md) and the Rector rules in the legacy-lift skill's `PHASES.md` first.

## Preconditions

- `p0-golden-record` is completed.
- Verify at start: `rector/rector` 2.8.4 installs with Composer on the host PHP 8.5.4. It is not verified that it installs on PHP 7.4 inside a container; Rector runs on the host, and the target version is set in config, not by the runtime that executes Rector.

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`, with Rector 2.8.4 at `~/.local/bin/rector`.

- Type coverage level 0, PHP 7.4 target: 0 files would change.
- PHP 7.4 upgrade set: 0 files would change, 0 errors, no Composer blockers.
- Reports are in `~/.cache/rector/reports/need2lift/`, outside the repository.
- No `rector.php` exists in the repository. The generated configs are temporary and are not committed.

## Open questions (ask before the change)

- None. The pinned version and the PHP 7.4 target are settled in ADR-0006 and ADR-0008. The target moves to 8.5 in p1-02.

## Changes

One commit (pull request) per item, in this order.

1. **PR 1: Committed Rector config.** **Approval** (new dev dependency). Add `rector/rector` 2.8.4 as a dev dependency, pinned exactly with no caret. Add `rector.php` with `RectorConfig::configure()`, `withPaths` on `public`, `config` and `module`, `withSkip` on `*/vendor/*`, `withPhpVersion(PhpVersion::PHP_74)` and `withTypeCoverageLevel(0)`. Do not enable any further sets in this todo.
   Done when `TMPDIR=~/.cache/rector rector process --config rector.php --dry-run --no-progress-bar` reports no changes and exits 0.

## Tests

- The dry run is the test. Any proposed diff means the code or the config is wrong, and it is fixed before the todo completes.
- The golden suite must pass unchanged.

## Validation

- `TMPDIR=~/.cache/rector rector process --config rector.php --dry-run --no-progress-bar`.
- `BASE_URL=http://localhost:8080 vendor/bin/phpunit -c tests/phpunit.xml.dist` after `docker compose up -d --build`.
- `git diff --stat -- tests/golden` shows no change.
- Record the Rector version and the empty dry run in `MIGRATION.md`, then set the todo `completed` in the master plan.

## Rollback

Revert the commit and run `composer install` to restore the lock file.
