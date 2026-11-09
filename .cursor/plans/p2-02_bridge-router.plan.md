---
name: p2-bridge-router
overview: public/index.php sends a request to Symfony only when its path is exactly /api/hello. Every other request still goes to Zend. No reply changes, because Symfony has no route yet and returns nothing.
todos:
  - id: verify
    content: Verify the bridge is an explicit list with exact matching; save the golden run before the change
    status: completed
  - id: pr1-bridge
    content: "PR 1: Bridge in public/index.php with an explicit path list (ADR-0009)"
    status: completed
  - id: validate
    content: Run the Validation checks; golden files unchanged; update MIGRATION.md and the master plan; one commit per PR
    status: completed
isProject: false
---

# p2-bridge-router: Explicit path list in the front controller

This is todo `p2-bridge-router` from [the master plan](need2lift-migration.plan.md) (section "Phase 2", `p2-bridge-router`). Read [ADR-0009](../../docs/adr/0009-bridge-routing.md) first.

## Preconditions

- `p2-symfony-install` is completed: the Symfony kernel boots.
- Verify at start: `curl -si http://localhost:8080/api/hello` still returns the greeting with status 200 before the change.

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`.

- `public/index.php` lines 3-9: `use Zend\Mvc\Application;`, `chdir(dirname(__DIR__))`, `require` of `vendor/autoload.php`, then `Application::init(require __DIR__ . '/../config/application.config.php')->run();`.
- `public/.htaccess`: `RewriteCond` for existing files, directories and symlinks; everything else goes to `index.php`. So every application request reaches `index.php`, including `/api/hello/` and `/nope`.
- The routes that Zend answers are in `module/Application/config/module.config.php` lines 8-16.

## Open questions (ask before the change)

- None. The explicit list and exact matching are settled in ADR-0009.

## Changes

One commit (pull request) per item, in this order.

1. **PR 1: Bridge.** Before the Zend bootstrap in `public/index.php`, check the request path against a fixed list, which contains `/api/hello` and nothing else. Match exactly and case-sensitively, on the path without the query string. A match boots Symfony's kernel and returns its response. Anything else runs the existing Zend bootstrap unchanged. In this PR the list is empty, so no request reaches Symfony and no reply changes. `/api/hello` is added to the list in p2-03, the todo that gives Symfony the route.
   Done when every golden file passes unchanged with the list empty, and a request to any path returns the same reply as before.

## Tests

- The golden suite must pass unchanged with the list empty.
- Add a unit-level check that the list matches exactly: `/api/hello` is on it, `/api/hello/`, `/API/HELLO` and `/api/hello?x=1` (after the query is stripped, `/api/hello` again) are handled as the rules in ADR-0009 say. Keep the check in `tests/` and do not change any golden file.

## Validation

- `docker compose -f docker-compose.yml -f docker-compose.test.yml up -d --build`, then the golden command.
- `vendor/bin/phpstan analyse --level=max --no-progress`.
- `TMPDIR=~/.cache/rector rector process --config rector.php --dry-run --no-progress-bar`.
- `git diff --stat -- tests/golden` shows no change.
- Record the bridge in `MIGRATION.md`, then set the todo `completed`.

## Rollback

Revert the commit. `public/index.php` returns to the Zend-only bootstrap.
