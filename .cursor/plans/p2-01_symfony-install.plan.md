---
name: p2-symfony-install
overview: Install a minimal Symfony 7.4 with Flex next to the Zend application, so the kernel boots on PHP 8.5 and the golden suite still passes on the Zend path. Raising composer.json's PHP constraint is decided here only if Composer requires it.
todos:
  - id: verify
    content: Verify whether Composer resolves Symfony 7.4 with the locked Zend packages at require.php ^7.4 (dry run, no commit); save the output
    status: completed
  - id: pr1-symfony
    content: "PR 1: Add symfony/framework-bundle, runtime, dotenv and yaml at 7.4, with Flex; kernel boots; no route changed (Approval: new dependencies; PHP constraint if Composer requires it)"
    status: completed
  - id: validate
    content: Run the Validation checks; golden files unchanged; update MIGRATION.md and the master plan; one commit per PR
    status: completed
isProject: false
---

# p2-symfony-install: Minimal Symfony 7.4

This is todo `p2-symfony-install` from [the master plan](need2lift-migration.plan.md) (section "Phase 2", `p2-symfony-install`). Read [ADR-0003](../../docs/adr/0003-target-symfony-lts.md), [ADR-0009](../../docs/adr/0009-bridge-routing.md) and [ADR-0011](../../docs/adr/0011-symfony-footprint.md) first.

## Preconditions

- Phase 1 is completed: the image runs PHP 8.5, and `rector.php` targets 8.5.
- Verify at start: Composer's resolution with Symfony and Zend together (see Changes, step 0).

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`.

- `composer.json` line 4: `"php": "^7.4"`. Lines 5-18: Zend packages `zendframework/*`, pinned.
- `composer.lock` `platform.php` is `^7.4`. 27 locked packages, none from Symfony.
- Composer 2.11.1 on the host. Symfony 7.4 packages require PHP `>=8.2` (symfony.com, measured 2026-11-09).
- `public/index.php` lines 3-9 bootstrap Zend only, with `chdir(dirname(__DIR__))` on line 5 and `require __DIR__ . '/../vendor/autoload.php'` on line 7.
- `config/application.config.php` lines 4-6 load module `Application`.

## Open questions (ask before the change)

- If Composer cannot resolve Symfony with `require.php` at `^7.4`, the choice is: raise `require.php` to `^8.2` now with the Zend packages left in place, or wait for p3-02. Recommendation: raise it here, since the image already ignores the PHP requirement for Zend, and state it as an Approval item. Decide before PR 1.

## Changes

One commit (pull request) per item, in this order.

0. **Verify resolution (no commit).** In a scratch copy of the project under `~/.cache/rector/scratch`, run `composer require symfony/framework-bundle:^7.4 symfony/runtime:^7.4 symfony/dotenv:^7.4 symfony/yaml:^7.4 --no-interaction` with the platform PHP set to 8.5. Record whether it resolves, and what it says about the PHP requirement.
1. **PR 1: Symfony install.** **Approval** (new dependencies; and the PHP constraint if step 0 required it). Add the four packages at 7.4 with Flex, which writes `config/bundles.php` and `.env`. Add `config/` for Symfony as `config/packages/` and `config/routes.yaml`. Create `src/` with the `App\` namespace. Add `APP_SECRET` to the environment for the Compose service, not to any committed file. Add `public/index.php` changes only if the kernel needs a second entry point; the bridge itself is p2-02. Do not add any route.
   Done when `docker compose exec app php bin/console about` (or the equivalent) reports Symfony 7.4 and the kernel boots on PHP 8.5, and the golden suite passes unchanged against the Zend-only path.

## Tests

- The golden suite must pass unchanged. Symfony is not yet reached by any request.
- A kernel boot check: `php bin/console about` exits 0 on the image.

## Validation

- `docker compose -f docker-compose.yml -f docker-compose.test.yml up -d --build`, then the golden command.
- `vendor/bin/phpstan analyse --level=max --no-progress`, extended to `src/`. Add `src` to `phpstan.neon` in this PR.
- `TMPDIR=~/.cache/rector rector process --config rector.php --dry-run --no-progress-bar`, with `src` added to the Rector paths.
- `git diff --stat -- tests/golden` shows no change.
- Record the Symfony version, the resolution outcome and the PHP constraint in `MIGRATION.md`, then set the todo `completed`.

## Rollback

Revert the commit and run `composer install`. If the PHP constraint was raised, restore `^7.4` and the previous lock file in the same revert. The image is unchanged by this todo.
