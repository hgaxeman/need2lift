---
name: p1-rector-85
overview: "Move the committed Rector configuration from the PHP 7.4 target to PHP 8.5, so the dry run checks syntax and rules against the runtime the code now runs on."
todos:
  - id: verify
    content: Verify the Compose image runs PHP 8.5 (p1-php85-image completed); save the dry run output
    status: pending
  - id: pr1-rector-85
    content: "PR 1: rector.php targets PHP_85; dry run empty (no approval needed; the target follows the runtime)"
    status: pending
  - id: validate
    content: Run the Validation checks; update MIGRATION.md and the master plan; one commit per PR
    status: pending
isProject: false
---

# p1-rector-85: Rector targets PHP 8.5

This is todo `p1-rector-85` from [the master plan](need2lift-migration.plan.md) (section "Phase 1", `p1-rector-85`). Read [ADR-0006](../../docs/adr/0006-safety-net.md) and [ADR-0008](../../docs/adr/0008-single-php-raise.md) first.

## Preconditions

- `p1-php85-image` is completed. The Compose image runs PHP 8.5.
- `p0-rector-config` is completed: `rector.php` exists and targets `PHP_74`.

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`.

- `rector.php` targets `PhpVersion::PHP_74` (from p0-04).
- PHP 8.5 upgrade set, measured with `rector-php-upgrade-dry-run.sh <project> 8.5`: 0 files would change, 0 errors. Report `~/.cache/rector/reports/need2lift/php85-20261109-220554/`.

## Open questions (ask before the change)

- None.

## Changes

One commit (pull request) per item, in this order.

1. **PR 1: Target PHP 8.5.** Change `withPhpVersion` in `rector.php` from `PHP_74` to `PHP_85`. Add no new sets; sets are added one level or group per pull request after this.
   Done when the dry run reports no changes and exits 0.

## Tests

- The dry run is the test. An empty dry run confirms that the PHP 8.5 target does not change the five project files.
- The golden suite must pass unchanged.

## Validation

- `TMPDIR=~/.cache/rector rector process --config rector.php --dry-run --no-progress-bar`.
- `vendor/bin/phpstan analyse --level=max --no-progress`.
- The golden command from [the master plan](need2lift-migration.plan.md#validation).
- Record the empty dry run for PHP 8.5 in `MIGRATION.md`, then set the todo `completed` in the master plan.

## Rollback

Revert the commit to target `PHP_74` again.
