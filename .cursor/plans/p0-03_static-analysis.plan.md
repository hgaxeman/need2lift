---
name: p0-static-analysis
overview: Add PHPStan at maximum level over the project code, with zero errors and no baseline. Vendor code is not analysed.
todos:
  - id: verify
    content: Verify the preconditions below; save the first PHPStan run output to a file outside the repository
    status: completed
  - id: pr1-phpstan
    content: "PR 1: Add phpstan/phpstan as dev dependency and phpstan.neon at level max; fix errors without changing a golden reply (Approval: new dev dependency)"
    status: completed
  - id: validate
    content: Run the Validation checks; golden files unchanged; update MIGRATION.md and the master plan; one commit per PR
    status: completed
isProject: false
---

# p0-static-analysis: PHPStan at maximum level

This is todo `p0-static-analysis` from [the master plan](need2lift-migration.plan.md) (section "Phase 0", `p0-static-analysis`). Read [ADR-0006](../../docs/adr/0006-safety-net.md) first.

## Preconditions

- `p0-golden-record` is completed, so behaviour is frozen before code is touched.
- Verify at start: which PHPStan release runs on PHP 7.4. PHPStan 1.x supports 7.4; the version is to be confirmed with Composer (verify).

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`.

- Five PHP files outside `vendor/`: `public/index.php`, `config/application.config.php`, `module/Application/Module.php`, `module/Application/config/module.config.php`, `module/Application/src/Controller/HelloController.php`.
- No PHPStan configuration, no `phpstan.neon`, no baseline, and no CI to run it.
- `Module.php` uses `Zend\EventManager\EventInterface` and `Zend\Mvc\MvcEvent`. Their types come from `vendor/`; PHPStan will load them through the autoloader and report errors only in project files.
- The greeting in `HelloController.php` lines 13-16 concatenates `PHP_VERSION` through `sprintf`.

## Open questions (ask before the change)

- None. Maximum level and zero errors without a baseline are settled in ADR-0006.

## Changes

One commit (pull request) per item, in this order.

1. **PR 1: PHPStan at maximum.** **Approval** (new dev dependency). Add `phpstan/phpstan` as a dev dependency, pinned to a release that runs on PHP 7.4. Add `phpstan.neon` with `level: max`, `paths` set to `public`, `config` and `module`, and no `excludePaths` except `vendor/`. Do not add a baseline. Fix errors in project code only where the fix keeps every golden reply identical. Any error that cannot be fixed without changing a reply is reported to the user, not suppressed.
   Done when `vendor/bin/phpstan analyse --level=max --no-progress` reports zero errors and the golden suite is unchanged.

## Tests

- The golden suite must pass unchanged. A PHPStan fix that changes a reply is rejected.
- No `@phpstan-ignore` lines without a reason, and no baseline.

## Validation

- `vendor/bin/phpstan analyse --level=max --no-progress`.
- `BASE_URL=http://localhost:8080 vendor/bin/phpunit -c tests/phpunit.xml.dist` after `docker compose up -d --build`.
- `git diff --stat -- tests/golden` shows no change.
- Record the PHPStan version and error count (zero) in `MIGRATION.md`, then set the todo `completed` in the master plan.

## Rollback

Revert the commit and run `composer install` to restore the lock file. The image is unaffected.
