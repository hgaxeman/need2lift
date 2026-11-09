---
name: p2-hello-symfony
overview: "Symfony serves /api/hello with the same greeting, status and content type. Zend stops answering that path. The golden files for the greeting pass unchanged, and the trailing-slash behaviour is checked against Symfony 7.4."
todos:
  - id: verify
    content: Verify whether Symfony 7.4 redirects /api/hello/ to /api/hello; save the Symfony response for that path
    status: pending
  - id: pr1-hello
    content: "PR 1: Symfony controller for api-hello returning the greeting; /api/hello added to the bridge list (ADR-0009)"
    status: pending
  - id: validate
    content: Run the Validation checks; golden files unchanged; update MIGRATION.md and the master plan; one commit per PR
    status: pending
isProject: false
---

# p2-hello-symfony: Symfony serves the greeting

This is todo `p2-hello-symfony` from [the master plan](need2lift-migration.plan.md) (section "Phase 2", `p2-hello-symfony`). Read [ADR-0002](../../docs/adr/0002-frozen-contracts.md), [ADR-0009](../../docs/adr/0009-bridge-routing.md) and [ADR-0010](../../docs/adr/0010-black-box-harness.md) first.

## Preconditions

- `p2-bridge-router` is completed. The list in `public/index.php` is empty.
- Verify at start: the golden suite passes against the current Zend path.

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`.

- `HelloController.php` lines 9-19: `helloAction` sets `Content-Type: text/plain; charset=utf-8` and the body `Hello from PHP %s` with `PHP_VERSION`. Status is the framework default (200).
- Route `api-hello`: `module.config.php` lines 8-16, `/api/hello`, no method constraint.
- Golden cases from p0-02 cover `GET`, `POST`, `PUT`, `DELETE`, `OPTIONS`, a query string, `Accept: application/json`, `/api/hello/` and `/API/HELLO`.
- Symfony's behaviour on `/api/hello/` is not verified. ADR-0009 records it as unverified. The golden case for `/api/hello/` is the check: it expects 404 `Not found`.

## Open questions (ask before the change)

- None blocking. If Symfony's router redirects `/api/hello/` to `/api/hello` (verify), the redirect is switched off in routing configuration, not in the golden file. The golden file does not change.

## Changes

One commit (pull request) per item, in this order.

1. **PR 1: Symfony serves the greeting.** Add a Symfony controller for route `api-hello` at path `/api/hello` with no method constraint. It returns status 200, `Content-Type: text/plain; charset=utf-8` and body `Hello from PHP ` followed by `PHP_VERSION`. Add `/api/hello` to the bridge list in `public/index.php`. Keep the route strict: no trailing-slash variant, no method restriction. If Symfony 7.4 redirects `/api/hello/`, disable that behaviour in routing configuration (verify the setting).
   Done when every golden file under `tests/golden/` passes unchanged, with `/api/hello` answered by Symfony and everything else by Zend.

## Tests

- The golden suite passes unchanged. The `/api/hello/` and `/API/HELLO` cases confirm the strict match.
- Add a test that proves the request reached Symfony and not Zend, for example a response header set only by the Symfony controller. Keep the header internal and do not add it to any golden file.

## Validation

- `docker compose -f docker-compose.yml -f docker-compose.test.yml up -d --build`, then the golden command.
- `vendor/bin/phpstan analyse --level=max --no-progress` over `src/` and the rest.
- `TMPDIR=~/.cache/rector rector process --config rector.php --dry-run --no-progress-bar`.
- `git diff --stat -- tests/golden` shows no change.
- Record the trailing-slash result for Symfony 7.4 in `MIGRATION.md` and in ADR-0009 (replace "verify" with the measured outcome), then set the todo `completed`.

## Rollback

Revert the commit. The bridge list returns to empty and Zend serves `/api/hello` again.
