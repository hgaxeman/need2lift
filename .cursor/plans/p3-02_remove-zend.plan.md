---
name: p3-remove-zend
overview: "Delete the Zend module, its packages, the front controller's Zend bootstrap, and the Composer override in the Dockerfile. Tighten require.php to the runtime. After this, the image runs PHP 8.5 with Symfony only."
todos:
  - id: verify
    content: Verify that no request reaches Zend (bridge list covers every path) and that the golden suite passes; save the output
    status: pending
  - id: pr1-remove
    content: "PR 1: Delete module/, config/application.config.php, Zend packages, Composer override, and tighten require.php (Approval: PHP constraint change, dependency removal)"
    status: pending
  - id: validate
    content: Run the Validation checks; golden files unchanged; update MIGRATION.md and the master plan; one commit per PR
    status: pending
isProject: false
---

# p3-remove-zend: Remove Zend Framework 2

This is todo `p3-remove-zend` from [the master plan](need2lift-migration.plan.md) (section "Phase 3", `p3-remove-zend`). Read [ADR-0005](../../docs/adr/0005-incremental-one-process.md), [ADR-0008](../../docs/adr/0008-single-php-raise.md) and [ADR-0011](../../docs/adr/0011-symfony-footprint.md) first.

## Preconditions

- `p3-symfony-unknown-and-failures` is completed: Symfony answers every request.
- Verify at start: `git grep -n "Zend\\\\" -- public config module` finds only the files this todo removes.

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`.

- `module/Application/` holds `Module.php`, `config/module.config.php`, `src/Controller/HelloController.php`. Zend is referenced in all three.
- `config/application.config.php` lines 4-6 and lines 8-17 load Zend's module manager.
- `public/index.php` lines 3-9 use `Zend\Mvc\Application` (line 3) and bootstrap it.
- `composer.json`: `"php": "^7.4"` on line 4. Twelve `zendframework/*` lines in `require`.
- `composer.lock` holds 23 `zendframework/*` packages, plus transitive dependencies.
- `Dockerfile` installs with `--ignore-platform-req=php` after p1-01. That override is removed here.
- Rector 2026-11-09: PHP 8.5 blockers are the root `php ^7.4` and 22 Zend packages. Those blockers disappear with this todo.

## Open questions (ask before the change)

- None. Removal of Zend and the override are settled in ADR-0008 and ADR-0011.

## Changes

One commit (pull request) per item, in this order.

1. **PR 1: Remove Zend.** **Approval** (PHP constraint change, dependency removal). Delete `module/`, `config/application.config.php`, and the Zend bootstrap in `public/index.php`; `public/index.php` keeps only the Symfony front controller. Run `composer remove zendframework/zend-mvc` and the other eleven `zendframework/*` requirements, so the lock file drops the Zend packages and the transitive packages they brought in. Set `"php"` in `composer.json` to `^8.5`. Remove `--ignore-platform-req=php` from the `Dockerfile` Composer command. Update `rector.php` only if a path it lists is gone. Delete `tests/fixtures/zz-test-routes.local.php` only if p3-01 has moved the failure route to a Symfony test kernel; otherwise leave it.
   Done when `composer validate` passes, `composer install` succeeds on the image without the override, and `git grep -i zend` finds nothing outside `docs/`, `MIGRATION.md` and `.cursor/`.

## Tests

- The golden suite passes unchanged, including `/api/hello` on PHP 8.5.
- A boot check on the image: `curl -si http://localhost:8080/api/hello` returns 200 and the greeting.
- Confirm the Zend packages are gone from `vendor/` after a clean install: `ls vendor/zendframework` fails.

## Validation

- `composer validate --strict`.
- `docker compose build --no-cache`, then `docker compose -f docker-compose.yml -f docker-compose.test.yml up -d`, then the golden command.
- `vendor/bin/phpstan analyse --level=max --no-progress`.
- `TMPDIR=~/.cache/rector rector process --config rector.php --dry-run --no-progress-bar`.
- `git grep -i zend` as above.
- Record the removal, the new `require.php` and the lock file change in `MIGRATION.md`, then set the todo `completed`.

## Rollback

Revert the commit and rebuild: `docker compose build --no-cache`. The Zend packages return from the lock file, and the image goes back to the override. This is a rebuild, not a deploy: nothing outside the repository changes (ADR-0013).
