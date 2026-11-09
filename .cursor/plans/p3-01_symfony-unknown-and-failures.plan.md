---
name: p3-symfony-unknown-and-failures
overview: "Symfony answers every request, so the bridge list is no longer needed for unknown requests and dispatch failures. Unknown requests get 404 Not found and dispatch failures get Error, both as text/plain. Every golden file must pass unchanged."
todos:
  - id: verify
    content: Verify what Symfony 7.4 returns for an unknown path today (status, content type, body); save it for comparison
    status: pending
  - id: pr1-unknown
    content: "PR 1: Symfony returns 404 Not found for unknown requests, text/plain; charset=utf-8"
    status: pending
  - id: pr2-failures
    content: "PR 2: Symfony returns Error for dispatch failures, forcing status below 400 to 500 and keeping other statuses at or above 400, and the test-only failure route answers through Symfony"
    status: pending
  - id: validate
    content: Run the Validation checks; golden files unchanged; update MIGRATION.md and the master plan; one commit per PR
    status: pending
isProject: false
---

# p3-symfony-unknown-and-failures: Symfony answers unknown requests and failures

This is todo `p3-symfony-unknown-and-failures` from [the master plan](need2lift-migration.plan.md) (section "Phase 3", `p3-symfony-unknown-and-failures`). Read [ADR-0002](../../docs/adr/0002-frozen-contracts.md) and [ADR-0009](../../docs/adr/0009-bridge-routing.md) first.

## Preconditions

- `p2-hello-symfony` is completed. Symfony serves `/api/hello`; Zend serves every other path.
- Verify at start: the golden suite passes with the bridge in place.

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`.

- Unknown requests today: Zend's 404 is turned into `Not found` by `module/Application/Module.php` (`onBootstrap`, lines 16-40).
- Dispatch-error behaviour today: `Module.php` lines 22-38. A status below 400 becomes 500; the body is `Not found` when the status is 404, and `Error` otherwise; content type is always `text/plain; charset=utf-8`.
- The test-only route `/test/dispatch-failure` is defined in `tests/fixtures/zz-test-routes.local.php` and mounted by `docker-compose.test.yml` (p0-02). Symfony must answer it too.
- Symfony 7.4's default 404 is an HTML page. It must be replaced, not wrapped.

## Open questions (ask before the change)

- None. The replies are frozen in ADR-0002.

## Changes

One commit (pull request) per item, in this order.

1. **PR 1: Unknown requests.** Add a Symfony listener on `kernel.exception` for `NotFoundHttpException` that returns status 404, `Content-Type: text/plain; charset=utf-8` and body `Not found`. Add every path to the bridge list in `public/index.php` for Symfony, so Symfony answers everything and Zend is no longer reached for an ordinary request. Zend remains in the code for dispatch failures until p3-02.
   Done when every golden case for `/api/hello/`, `/API/HELLO`, `/nope` and the query-string request passes unchanged.
2. **PR 2: Dispatch failures.** Add a `kernel.exception` listener for every other exception, returning content type `text/plain; charset=utf-8` and body `Error`. Status: if the exception carries a status at or above 400, keep it; otherwise 500. For a 404 status, return body `Not found`. Route the test-only `/test/dispatch-failure` through Symfony's kernel.
   Done when the failure golden case passes unchanged (status 500, body `Error`).

## Tests

- The golden suite passes unchanged after each PR.
- Add a unit test for the status rule: an exception with status 418 keeps 418 and body `Error`; an exception with no status becomes 500; a status of 404 gives `Not found`. This mirrors the rule in ADR-0002. The test does not change any golden file.

## Validation

- `docker compose -f docker-compose.yml -f docker-compose.test.yml up -d --build`, then the golden command.
- `docker compose up -d --build` on the default compose file, then the guard test from p0-02, which expects `/test/dispatch-failure` to return 404 on the default image.
- `vendor/bin/phpstan analyse --level=max --no-progress`.
- `git diff --stat -- tests/golden` shows no change.
- Record the Symfony-side replies in `MIGRATION.md`, then set the todo `completed`.

## Rollback

Revert the commit for the PR in question. Each PR is independent: reverting PR 2 leaves unknown requests with Symfony, and reverting PR 1 sends them back to Zend.
