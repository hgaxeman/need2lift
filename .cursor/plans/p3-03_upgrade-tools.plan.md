---
name: p3-upgrade-tools
overview: After Zend is gone, upgrade PHPUnit and Rector to versions that support PHP 8.5, and keep PHPStan on a release that does too. The golden suite is the check.
todos:
  - id: verify
    content: Verify each tool's latest release supports PHP 8.5 (PHPUnit, PHPStan, Rector) against its release notes; save the version list
    status: completed
  - id: pr1-phpunit
    content: "PR 1: Upgrade PHPUnit to a release that supports PHP 8.5 (Approval: dependency change)"
    status: completed
  - id: pr2-rector-phpstan
    content: "PR 2: Upgrade Rector and PHPStan, with the Rector dry run empty on the new version (Approval: dependency change)"
    status: completed
  - id: validate
    content: Run the Validation checks; golden files unchanged; update MIGRATION.md and the master plan; one commit per PR
    status: completed
isProject: false
---

# p3-upgrade-tools: Upgrade the test and analysis tools

This is todo `p3-upgrade-tools` from [the master plan](need2lift-migration.plan.md) (section "Phase 3", `p3-upgrade-tools`). Read [ADR-0010](../../docs/adr/0010-black-box-harness.md) and [ADR-0006](../../docs/adr/0006-safety-net.md) first.

## Preconditions

- `p3-remove-zend` is completed: the image runs PHP 8.5 with Symfony only.

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`, before this todo.

- PHPUnit 9.6 is the runner (p0-01). Its support for PHP 8.5 is not verified (ADR-0010).
- Rector 2.8.4 is pinned (p0-04). Its dry run on PHP 8.5 reported 0 files.
- PHPStan is pinned to the release chosen in p0-03 (verify its PHP 8.5 support then).

## Open questions (ask before the change)

- None. Upgrades go to the newest release that supports PHP 8.5, verified at the time of the change.

## Changes

One commit (pull request) per item, in this order.

1. **PR 1: PHPUnit.** **Approval** (dependency change). Upgrade `phpunit/phpunit` to the newest release that supports PHP 8.5. Update the test configuration only if the release requires it. The tests stay the same.
   Done when the golden suite passes on the new PHPUnit with the same golden files.
2. **PR 2: Rector and PHPStan.** **Approval** (dependency change). Upgrade `rector/rector` to the newest pinned release that supports PHP 8.5 and `phpstan/phpstan` likewise. Accept only dry-run changes that the new Rector proposes for the five project files, reviewed line by line; any proposed change to a golden reply is rejected.
   Done when the Rector dry run is empty and PHPStan reports zero errors on the new versions.

## Tests

- The golden suite must pass unchanged on both PRs.
- A version change that changes a golden reply is rejected, not accepted into the golden files.

## Validation

- `composer show phpunit/phpunit rector/rector phpstan/phpstan` records the versions.
- `BASE_URL=http://localhost:8080 vendor/bin/phpunit -c tests/phpunit.xml.dist` after `docker compose up -d --build`.
- `vendor/bin/phpstan analyse --level=max --no-progress`.
- `TMPDIR=~/.cache/rector rector process --config rector.php --dry-run --no-progress-bar`.
- `git diff --stat -- tests/golden` shows no change.
- Record the versions in `MIGRATION.md`, then set the todo `completed`.

## Rollback

Revert the PR. Each tool version is independent of the application, so reverting does not affect the image.
