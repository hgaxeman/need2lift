---
name: p0-golden-record
overview: Record the golden files for every contract case, plus a test-only route that fails dispatch. The route exists only in a Compose test overlay, so the default image does not expose it.
todos:
  - id: verify
    content: Verify the preconditions below; save the probe output for comparison
    status: completed
  - id: pr1-golden-files
    content: "PR 1: Record golden files in tests/golden/ for the non-failure cases (Approval: first freeze of the contract)"
    status: completed
  - id: pr2-failure-route
    content: "PR 2: Test-only dispatch-failure route via docker-compose.test.yml and tests/fixtures/zz-test-routes.local.php, plus a guard test on the default image"
    status: completed
  - id: validate
    content: Run the Validation checks; golden files unchanged after recording; update MIGRATION.md and the master plan; one commit per PR
    status: completed
isProject: false
---

# p0-golden-record: Golden files and the dispatch-failure route

This is todo `p0-golden-record` from [the master plan](need2lift-migration.plan.md) (section "Phase 0", `p0-golden-record`). Read [ADR-0002](../../docs/adr/0002-frozen-contracts.md), [ADR-0009](../../docs/adr/0009-bridge-routing.md) and [ADR-0010](../../docs/adr/0010-black-box-harness.md) first.

## Preconditions

- `p0-golden-harness` is completed: `tests/phpunit.xml.dist` exists and the smoke test passes.
- Verify at start: the Compose image answers `/api/hello` with 200 and the greeting `Hello from PHP 7.4.33` (the image's PHP patch version).

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`, with the probe in `~/.cache/rector/reports/need2lift/probe-after-e21c4b7-20261109.txt`.

- The greeting: `module/Application/src/Controller/HelloController.php` lines 13-16. Body is `Hello from PHP %s` with `PHP_VERSION`.
- Route: `module/Application/config/module.config.php` lines 8-16; no method constraint.
- Dispatch-error listener: `module/Application/Module.php`, `onBootstrap`. Status below 400 becomes 500, status 404 gives body `Not found`, other errors give `Error`. Content type is always `text/plain; charset=utf-8`.
- Config glob: `config/application.config.php` lines 12-14 read `config/autoload/{,*.}{global,local}.php`. The directory is absent from the repository and from the image. A file named `zz-test-routes.local.php` matches the pattern.
- No public request reaches the dispatch-error branch that returns `Error` today. The only way to reach it is an exception thrown during dispatch. Measured in the inventory, no route in the repository's config throws.
- `public/.htaccess`: non-file requests go to `index.php`. Real files are served directly.

## Open questions (ask before the change)

- None blocking. The assumed mechanism (Compose override mounting a `*.local.php` file into `config/autoload/`) is the least intrusive way to add a route that exists only in tests. If the throwing controller cannot be registered without an autoloaded class, the todo falls back to a closure registered under `controllers.factories` in the same test file. Either way the default image is unchanged.

## Changes

One commit (pull request) per item, in this order.

1. **PR 1: Golden files for the public contract.** **Approval** (recording golden files is the first freeze of the contract). Create `tests/golden/` with one file per case, storing the status, the content type and the body. Cases:
   - `GET`, `POST`, `PUT`, `DELETE`, `OPTIONS` on `/api/hello`;
   - `/api/hello?x=1`;
   - `/api/hello` with `Accept: application/json`;
   - `/api/hello/`, `/API/HELLO`, `/nope` with `GET` and `POST`.
   The runner normalises only `\d+\.\d+\.\d+` inside `Hello from PHP`. Recording writes files only when `GOLDEN_RECORD=1` is set, so a normal run never rewrites a golden file.
   Done when every case matches its recorded file on two consecutive runs.
2. **PR 2: Test-only dispatch failure.** Add `docker-compose.test.yml` at the repository root. It adds `./tests/fixtures/zz-test-routes.local.php:/var/www/html/config/autoload/zz-test-routes.local.php:ro` to service `app`. The fixture adds a route `test-dispatch-failure` at `/test/dispatch-failure` whose controller throws during dispatch. Add a golden file for it: status 500, body `Error`, content type `text/plain; charset=utf-8`. Add a guard test that runs against the default compose file only (no overlay) and expects `/test/dispatch-failure` to return 404 `Not found`.
   Done when the golden case passes under `docker compose -f docker-compose.yml -f docker-compose.test.yml up -d --build`, and the guard passes under plain `docker compose up -d --build`.

## Tests

- Golden files must not change between runs unless `GOLDEN_RECORD=1` is set. A changed golden file is a contract change and needs sign-off.
- The guard test is a contract test on the default image: it proves the failure route is not in the image built without the test overlay.
- A failing golden case means the change is wrong, not the test.

## Validation

- `docker compose -f docker-compose.yml -f docker-compose.test.yml up -d --build`, then `BASE_URL=http://localhost:8080 vendor/bin/phpunit -c tests/phpunit.xml.dist`.
- `docker compose up -d --build` on the default compose file, then the guard test.
- `git diff --stat -- tests/golden` after the golden run shows no change.
- `git diff --stat` shows no file under `module/`, `public/`, `config/` or `Dockerfile`.
- Record the golden file count in `MIGRATION.md`, then set the todo `completed` in the master plan.

## Rollback

Delete `tests/golden/`, `tests/fixtures/` and `docker-compose.test.yml` and revert the commit. No file under `module/`, `public/`, `config/` or `Dockerfile` changes in this todo, so the image is unaffected.
