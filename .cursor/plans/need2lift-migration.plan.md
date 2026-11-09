---
name: need2lift Zend to Symfony migration
overview: "Move the one-endpoint Zend Framework 2 application to Symfony 7.4 LTS on PHP 8.5, keeping the greeting and error replies unchanged. Phase 0 builds the safety net on the current PHP 7.4 image, Phase 1 raises PHP to 8.5 with Zend still serving, Phase 2 runs Symfony beside Zend and moves /api/hello, Phase 3 removes Zend. Each todo is one change that ends green."
todos:
  - id: p0-golden-harness
    content: "Phase 0: Black-box PHPUnit 9.6 runner against BASE_URL (Approval: new dev dependency)"
    status: completed
  - id: p0-golden-record
    content: "Phase 0: Golden files for every contract case plus a test-only route that fails dispatch; guard that the default image does not expose that route"
    status: completed
  - id: p0-static-analysis
    content: "Phase 0: PHPStan at maximum, zero errors, no baseline (Approval: new dev dependency)"
    status: completed
  - id: p0-rector-config
    content: "Phase 0: Committed rector.php targeting PHP 7.4, Rector pinned at 2.8.4 with an empty dry run (Approval: new dev dependency)"
    status: completed
  - id: p0-ci
    content: "Phase 0: CANCELLED. No CI: this is a demo project with no production runtime (ADR-0013)"
    status: cancelled
  - id: p1-php85-image
    content: "Phase 1: Base image php:8.5-apache, Composer ignores only the PHP requirement, display_errors stays off (Approval: runtime image change)"
    status: completed
  - id: p1-rector-85
    content: "Phase 1: Rector config pinned to PHP 8.5, dry run empty"
    status: completed
  - id: p2-symfony-install
    content: "Phase 2: Minimal Symfony 7.4 with Flex; code under App\\ in src/ (Approval: new dependencies, and composer.json PHP constraint if it must change)"
    status: completed
  - id: p2-bridge-router
    content: "Phase 2: public/index.php sends only the exact path /api/hello to Symfony; everything else stays with Zend"
    status: completed
  - id: p2-hello-symfony
    content: "Phase 2: Symfony serves the greeting; golden files pass unchanged; trailing-slash behaviour verified"
    status: completed
  - id: p3-symfony-unknown-and-failures
    content: "Phase 3: Symfony answers unknown requests and dispatch failures with the frozen replies"
    status: completed
  - id: p3-remove-zend
    content: "Phase 3: Delete module/ and Zend packages, remove the Composer override, tighten require.php (Approval: PHP constraint change, dependency removal)"
    status: completed
  - id: p3-upgrade-tools
    content: "Phase 3: Upgrade PHPUnit and Rector to versions that support PHP 8.5 (Approval: dependency change)"
    status: pending
isProject: false
---

# need2lift: Zend Framework 2 to Symfony 7.4 on PHP 8.5

## Guiding rule

External behaviour is frozen by the golden files, as set out in [ADR-0002](../../docs/adr/0002-frozen-contracts.md). A todo is not done if a golden file changed without approval. The greeting is `Hello from PHP <version>` at commit `e21c4b7`; the version is the only normalised value.

Background is in [MIGRATION.md](../../MIGRATION.md), `docs/adr/` and [GLOSSARY.md](../../GLOSSARY.md). Read the ADRs a todo cites before starting it.

## How to run this plan

- One todo per plan-mode session, one commit or pull request per todo. Branches are `migrate/<slug>` from `master`, with no ticket ids (the grilling answer Q12).
- Read `MIGRATION.md` and the ADRs the todo cites before editing. There is no `AGENTS.md`, `CLAUDE.md` or contributing guide.
- Every todo ends with [Validation](#validation) passing. Record new measurements in `MIGRATION.md`.
- Stop and ask before any step marked **Approval**: [MIGRATION.md, Approval needed](../../MIGRATION.md#approval-needed).
- Nothing in the project talks to real systems today. Keep it that way; the suite must pass with network egress blocked, except for package registries during installs.

## Validation

Run from the repository root unless stated otherwise. There is no CI, so these checks are run by hand (ADR-0013).

- Image: `docker compose build && docker compose up -d`, then wait until `curl -s http://localhost:8080/api/hello` answers.
- Golden tests (from p0-01 onwards): `BASE_URL=http://localhost:8080 vendor/bin/phpunit -c tests/phpunit.xml.dist`. The runner may run in a `php:7.4-cli` container.
- Static analysis (from p0-03): `vendor/bin/phpstan analyse --level=max --no-progress`.
- Rector (from p0-04): `TMPDIR=~/.cache/rector rector process --config rector.php --dry-run --no-progress-bar`. The dry run must report no changes.
- Scope: `git status --porcelain` lists only the files the todo names. No file under `tests/golden/` changes without approval.
- Rules: tests green, golden files unchanged, PHPStan without new errors, Rector dry run clean with the committed config.

## Phase 0: safety net (runtime `php:7.4-apache`)

### p0-golden-harness

- Add `tests/` with a PHPUnit 9.6 configuration and one smoke test that sends a request to `BASE_URL` and reads the status, content type and body.
- Add `phpunit/phpunit` ^9.6 as a dev dependency. (Approval: new dev dependency.)
- Done when the smoke test passes against the running Compose image and the runner works in a `php:7.4-cli` container.

### p0-golden-record

- Record golden files in `tests/golden/`, one per case: `GET`, `POST`, `PUT`, `DELETE` and `OPTIONS` on `/api/hello`; `/api/hello?x=1`; a request with `Accept: application/json`; `/api/hello/`; `/API/HELLO`; `/nope` with GET and POST; and the dispatch failure.
- The dispatch failure uses a test-only route `/test/dispatch-failure`. Its controller throws. The route is registered by `docker-compose.test.yml`, which mounts `tests/fixtures/zz-test-routes.local.php` into the container as `config/autoload/zz-test-routes.local.php`. The production image gets no such file, so the route does not exist there. (Assumed mechanism; see the todo's open questions.)
- The version in the greeting is normalised with `\d+\.\d+\.\d+`. Nothing else is.
- Done when the golden files match the current behaviour and a guard test shows that `/test/dispatch-failure` returns 404 on the default image.
- (Approval: recording the golden files is the first freeze of the contract.)

### p0-static-analysis

- Add `phpstan/phpstan` as a dev dependency with a `phpstan.neon` at maximum level, analysing the project code (`module/`, `config/`, `public/`), not `vendor/`. No baseline file. (Approval: new dev dependency.)
- Done when `phpstan analyse --level=max` reports zero errors. Fix code only where the fix does not change a golden reply.

### p0-rector-config

- Add `rector/rector` 2.8.4 as a dev dependency, pinned exactly. Commit `rector.php` with `withPhpVersion(PhpVersion::PHP_74)`, `withTypeCoverageLevel(0)` and the upgrade sets the first measurements used. (Approval: new dev dependency.)
- Done when the dry run reports no changes on the current code.

### p0-ci (cancelled)

- Cancelled. The project is a demo with no CI and no production runtime ([ADR-0013](../../docs/adr/0013-demo-no-production-no-ci.md)). The checks run by hand, as in [Validation](#validation).
- The plan file `p0-05_ci.plan.md` is kept for history and is not to be started.

## Phase 1: raise PHP to 8.5 (Zend still serves)

### p1-php85-image

- In `Dockerfile`, change the base to `php:8.5-apache`, remove the Bullseye archive workaround, keep `intl`, `mod_rewrite`, the vhost and `display_errors=Off`. Install Composer with `--ignore-platform-req=php` so the locked Zend packages install. Other platform checks stay on. (Approval: runtime image change.)
- Verify at start: the `composer:2.2` build stage runs on PHP 8.5 (verify).
- Done when the golden suite passes unchanged against the new image.

### p1-rector-85

- Change `rector.php` to `PHP_85` and rerun the dry run. Done when the dry run is empty.

## Phase 2: Symfony side by side

### p2-symfony-install

- Add `symfony/framework-bundle`, `symfony/runtime`, `symfony/dotenv` and `symfony/yaml` at 7.4 with Flex. Add `config/` for Symfony under the project root, `src/` for `App\`, and `APP_SECRET` from the environment only. (Approval: new dependencies.)
- Verify at start: whether Composer resolves Symfony 7.4 alongside the locked Zend packages while `require.php` is `^7.4`. If it does not, raising `require.php` is needed and falls under the Approval list.
- Done when the Symfony kernel boots on PHP 8.5 and the golden suite still passes against the Zend-only path.

### p2-bridge-router

- `public/index.php` sends a request to Symfony only when the path is exactly `/api/hello`. Everything else still goes to Zend. Matching is case-sensitive.
- Done when the golden suite passes unchanged and a request to any other path behaves as before.

### p2-hello-symfony

- Symfony serves the greeting from a controller that produces the same status, content type and body. The route name is `api-hello`.
- Verify at start: whether Symfony answers `/api/hello/` with a redirect (verify against 7.4). If it does, switch it off so `/api/hello/` stays a 404 `Not found` through the golden file. Symfony does not answer unknown requests until p3-01, so this 404 still comes from Zend.
- Done when the greeting golden files pass unchanged with Symfony answering the path.

## Phase 3: remove Zend

### p3-symfony-unknown-and-failures

- Symfony answers unknown requests with status 404, `text/plain; charset=utf-8` and body `Not found`. Symfony answers dispatch failures with `text/plain; charset=utf-8` and body `Error`, forcing status below 400 to 500 and keeping other statuses at or above 400.
- Done when every golden file passes with Symfony answering every request.

### p3-remove-zend

- Delete `module/`, `config/application.config.php` and the Zend packages in `composer.json`. Remove the `--ignore-platform-req=php` override from the `Dockerfile`. Set `require.php` to the PHP version the code runs on. Remove the Zend bootstrap lines from `public/index.php`. The test-only route stays in the test overlay until the guard test in p0-02 is rewritten for Symfony. (Approval: PHP constraint change, dependency removal.)
- Done when `composer validate` passes, the golden suite passes, and `git grep -i zend` finds nothing outside `docs/`, `MIGRATION.md` and `.cursor/`.

### p3-upgrade-tools

- Upgrade PHPUnit and Rector to versions that support PHP 8.5. Verify each version's PHP support before the change (verify). (Approval: dependency change.)
- Done when the golden suite, PHPStan and the Rector dry run pass on PHP 8.5.

## Prerequisites the user must provide

None. The project is a demo: no git remote, no production runtime and no CI (ADR-0013).

## Out of scope

As [ADR-0007](../../docs/adr/0007-out-of-scope.md): no endpoints, authentication, CORS, content negotiation, database, mail, deploy pipeline, or change to the greeting wording. The greeting wording is now `Hello from PHP <version>`, set in commit `e21c4b7`; further change is out of scope.
