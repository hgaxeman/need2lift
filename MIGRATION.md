# need2lift: Zend Framework 2 to Symfony 7.4 on PHP 8.5

## Goal

Move the one-endpoint Zend Framework 2 application (`/api/hello`) to Symfony 7.4 LTS on PHP 8.5 with its external behaviour kept. The method is Rector's analyse, decouple, side by side, remove and upgrade, without the decouple phase: the application has no business logic to extract ([ADR-0001](docs/adr/0001-same-application.md), [ADR-0005](docs/adr/0005-incremental-one-process.md)). The runtime moves from PHP 7.4 to 8.5 in one step. Symfony then runs next to Zend in one process, takes over the greeting, and Zend is removed once the golden files pass on Symfony.

See [Contracts that must not change](#contracts-that-must-not-change).

## Target stack

| Layer | Target | Notes |
| --- | --- | --- |
| PHP | 8.5 | Active support until 31 Dec 2027, security until 31 Dec 2029 (php.net, measured 2026-11-09). [ADR-0004](docs/adr/0004-target-php-85.md) |
| Framework | Symfony 7.4 LTS | Bug fixes until Nov 2028, security until Nov 2029; requires PHP 8.2 or newer (symfony.com, measured 2026-11-09). [ADR-0003](docs/adr/0003-target-symfony-lts.md), [ADR-0011](docs/adr/0011-symfony-footprint.md) |
| Bridge framework | none | Zend serves until removal; Symfony joins in the same process. [ADR-0005](docs/adr/0005-incremental-one-process.md), [ADR-0009](docs/adr/0009-bridge-routing.md) |
| ORM | none | No database in use. [ADR-0007](docs/adr/0007-out-of-scope.md) |
| API | none | Plain text, one greeting; no new API. [ADR-0002](docs/adr/0002-frozen-contracts.md) |
| Console | none | No console commands exist. |
| Mail, templates, logging, HTTP clients, CORS, sanitising | none | None in use (inventory 2026-11-09). |
| Tests | PHPUnit 9.6, black-box HTTP golden files | Upgraded after Zend is removed. Runner on PHP 7.4 known to work; on 8.5 not verified. [ADR-0010](docs/adr/0010-black-box-harness.md) |
| Static analysis | PHPStan 2.3.5, maximum level, zero errors, no baseline, run by hand (no CI) | Runs on PHP 7.4 (Composer requires `php ^7.4` or `^8.0`). Verified 2026-11-09: 0 errors. [ADR-0006](docs/adr/0006-safety-net.md) |
| Refactoring | Rector 2.8.4, config committed, dry run must be empty | Installed 2026-11-09. [ADR-0006](docs/adr/0006-safety-net.md) |
| Coding standard | none chosen | Add one only if Rector's output needs it (verify at Phase 1). |

## Current state

Measured on 2026-11-09 in `/home/njegovan/PhpstormProjects/need2lift`, at commit `e21c4b7` ("hello php"), which sits on `d252383` ("init - php 7.4").

- **Runtime:** `Dockerfile` line 1 `FROM composer:2.2 AS composer`, line 3 `FROM php:7.4-apache`. `docker-compose.yml` builds it and publishes `8080:80`. No CI configuration and no production runtime: this is a demo project, and the Compose image is the only runtime ([ADR-0013](docs/adr/0013-demo-no-production-no-ci.md)). Host PHP for analysis is 8.5.4.
- **Size:** 5 PHP files outside `vendor/`. 4 test classes under `tests/Golden/`. 15 golden files in `tests/golden/` (13 public contract cases, 1 dispatch-failure case, 1 default-image absence guard).
- **Framework modules loaded:** `Application` only (`config/application.config.php` lines 4-6).
- **Composer:** `require.php` is `^7.4`. `composer.lock` holds 56 packages (27 prod, 29 dev), platform `^7.4`. Dev: `phpunit/phpunit` 9.6.38, `phpstan/phpstan` 2.3.5. 12 direct Zend packages; `zendframework/zend-mvc` 2.7.15. All Zend packages are marked abandoned in favour of `laminas/*`. No forks, no `dev-*` versions, no VCS or path repositories.
- **Vendor:** `vendor/` was installed on the host on 2026-11-09 with `composer install --ignore-platform-reqs`. It is gitignored. The image installs its own (`Dockerfile` lines 33-34).
- **Working tree:** `git status` shows only the new `GLOSSARY.md` and `docs/`, and `MIGRATION.md` once written.

### Zend usage

| Component | Refs | Replacement |
| --- | --- | --- |
| `Zend\Mvc` (`Application`, `MvcEvent`, `AbstractActionController`) | 8 | Symfony HTTP kernel and controllers |
| `Zend\EventManager` (`EventInterface`) | 2 | Symfony event listeners for exceptions |
| `Zend\ModuleManager` (`BootstrapListenerInterface`) | 2 | None; removed with the module |
| Direct packages with no explicit reference: config, http, i18n, loader, log, serializer, servicemanager, stdlib, view | 0 | Removed with Zend |

Counts from the inventory of 2026-11-09 across `public/`, `config/`, `module/`.

### Entry points to migrate

- **HTTP:** one route, `api-hello`, `Literal` `/api/hello`, no method constraint (`module/Application/config/module.config.php` lines 8-16). It dispatches to `HelloController::helloAction` (`module/Application/src/Controller/HelloController.php` lines 9-19). The dispatch-error listener is in `module/Application/Module.php` (`onBootstrap`, lines 16-40). Unknown requests get Zend's 404. The front controller is `public/index.php`. Apache rewrites non-file requests through `public/.htaccess`.
- **Console:** none.
- **Scheduled tasks:** none. No cron file, Kubernetes CronJob, CI schedule or infrastructure file.
- **Queue consumers, RPC endpoints:** none.

### Measured behaviour

Probe on 2026-11-09 at `e21c4b7`, serving the project read-only with PHP's built-in server in `php:<v>-cli` containers. Raw output is in `~/.cache/rector/reports/need2lift/probe-after-e21c4b7-20261109.txt`, outside the repository.

| Runtime | Greeting body | Other replies |
| --- | --- | --- |
| PHP 7.4.33 | `Hello from PHP 7.4.33` | `/api/hello/`, `/API/HELLO`, `/nope` (GET and POST): 404 `Not found` |
| PHP 8.2.35 | `Hello from PHP 8.2.35` | same |
| PHP 8.4.27 | `Hello from PHP 8.4.27` | same |
| PHP 8.5.12 | `Hello from PHP 8.5.12` | same |

On all four, the greeting is `text/plain; charset=utf-8` with status 200 for GET, POST, PUT, DELETE and OPTIONS, and for `/api/hello?x=1`. On PHP 8 the only stderr messages are deprecations inside `vendor/zendframework/`. None come from project code.

### PHP 8.5 readiness

Rector 2.8.4, measured 2026-11-09 at `e21c4b7`:

- Type-coverage level 0, PHP 7.4 target (`rector-intro.sh`): 0 files would change, 0 errors.
- PHP 7.4 upgrade set: 0 files would change, 0 errors. Composer blockers: none.
- PHP 8.5 upgrade set: 0 files would change, 0 errors.
- Composer blockers for PHP 8.5 (`composer why-not`, against the lock): the root `need2lift/hello` requires `php ^7.4`, and 22 locked `zendframework/*` packages require `^5.6 || ^7.0`, `^5.5 || ^7.0` (zend-mvc) or `^7.1` (zend-validator). Removal of the blockers is the Zend removal in Phase 3. Full list: `~/.cache/rector/reports/need2lift/php85-20261109-220554/composer.txt`.

## Contracts that must not change

Full reasoning is in [ADR-0002](docs/adr/0002-frozen-contracts.md) and [ADR-0009](docs/adr/0009-bridge-routing.md). Golden files will check each bullet.

- `GET`, `POST`, `PUT`, `DELETE` and `OPTIONS` on `/api/hello` return status 200, `Content-Type: text/plain; charset=utf-8` and body `Hello from PHP <version>`. `<version>` is the full PHP version of the running runtime; golden files normalise it with `\d+\.\d+\.\d+` and nothing else. The wording changed from the original `Hello from PHP 7.4, Zend Framework 2` in commit `e21c4b7`, made by the owner.
- `/api/hello?x=1` returns the same greeting.
- `/api/hello/`, `/API/HELLO` and `/nope`, with GET or POST, return status 404, `text/plain; charset=utf-8` and body `Not found`.
- A dispatch failure returns `text/plain; charset=utf-8` and body `Error`. A status below 400 is forced to 500. Any other status at or above 400 is preserved, except that 404 returns `Not found`.
- `Accept` headers do not change any reply. No authentication, CORS, application logging, console output or stored data.
- A request for a real file or directory under `public/` is served by Apache and never reaches the application.

Changing any of them needs sign-off.

## Migration plan

Every todo is one change that ends green. Plans live in `.cursor/plans/`.

### Phase 0: safety net (runtime `php:7.4-apache`)

1. **p0-golden-harness.** Black-box PHPUnit 9.6 runner against `BASE_URL`. (Approval: new dev dependency.) Plan: `p0-01_golden-harness.plan.md`.
2. **p0-golden-record.** Golden files for every contract case, plus a test-only route that fails dispatch. Plan: `p0-02_golden-record.plan.md`.
3. **p0-static-analysis.** PHPStan at maximum, zero errors, no baseline. (Approval: new dev dependency.) Plan: `p0-03_static-analysis.plan.md`.
4. **p0-rector-config.** Committed `rector.php` targeting PHP 7.4 for now, Rector pinned at 2.8.4. (Approval: new dev dependency.) Plan: `p0-04_rector-config.plan.md`.
5. **p0-ci.** Cancelled: demo project, no CI ([ADR-0013](docs/adr/0013-demo-no-production-no-ci.md)). Plan kept for history: `p0-05_ci.plan.md`.

### Phase 1: raise PHP to 8.5 (Zend still serves)

6. **p1-php85-image.** Base image `php:8.5-apache`; Composer installs with `--ignore-platform-req=php`; `display_errors` stays off. (Approval: runtime image change.) Plan: `p1-01_php85-image.plan.md`.
7. **p1-rector-85.** Rector config pinned to PHP 8.5. Plan: `p1-02_rector-85.plan.md`.

### Phase 2: Symfony side by side

8. **p2-symfony-install.** Minimal Symfony 7.4 with Flex. (Approval: new dependencies; conditional on the `composer.json` PHP constraint.) Plan: `p2-01_symfony-install.plan.md`.
9. **p2-bridge-router.** `public/index.php` sends only `/api/hello` to Symfony. Plan: `p2-02_bridge-router.plan.md`.
10. **p2-hello-symfony.** Symfony serves the greeting and matches the golden files unchanged. Plan: `p2-03_hello-symfony.plan.md`.

### Phase 3: remove Zend

11. **p3-symfony-unknown-and-failures.** Symfony answers unknown requests and dispatch failures. Plan: `p3-01_symfony-unknown-and-failures.plan.md`.
12. **p3-remove-zend.** Delete the module, the Zend packages and the Composer override; tighten `require.php`. (Approval: PHP constraint change, dependency removal.) Plan: `p3-02_remove-zend.plan.md`.
13. **p3-upgrade-tools.** PHPUnit upgraded to a version that supports PHP 8.5 (verify). (Approval: dependency change.) Plan: `p3-03_upgrade-tools.plan.md`.

## Decisions

| ADR | Decision |
| --- | --- |
| 0001 | Same application; behaviour frozen, no redesign |
| 0002 | Freeze the greeting (`Hello from PHP <version>`), the 404 and dispatch-failure replies; edited on 2026-11-09 for commit `e21c4b7` |
| 0003 | Symfony 7.4 LTS is the target framework |
| 0004 | PHP 8.5 is the final runtime |
| 0005 | Incremental move in one process; PHP raised before Symfony joins |
| 0006 | Golden files, PHPStan at maximum, pinned Rector dry run, all before any route moves |
| 0007 | Out of scope: endpoints, auth, CORS, negotiation, database, mail, deploy pipeline, greeting wording |
| 0008 | Raise PHP once, 7.4 to 8.5; Composer ignores only the PHP requirement until Zend is removed |
| 0009 | Explicit path list routes to Symfony; Zend keeps the rest until removal |
| 0010 | Golden files are black-box HTTP tests against the Compose image |
| 0011 | Minimal Symfony 7.4: framework bundle, runtime, dotenv, yaml; code in `App\` under `src/` |
| 0012 | Superseded by ADR-0013 |
| 0013 | Demo project: no production runtime, no CI/CD; checks run by hand; base image `php:8.5-apache` |

## Tooling in place

| What | Where | Use |
| --- | --- | --- |
| Rector 2.8.4 | `~/.local/bin/rector` | Run with `TMPDIR=~/.cache/rector`. The scripts set this. |
| Rector scripts | `~/.cursor/skills/legacy-lift/scripts/` | `rector-intro.sh`, `rector-php-upgrade-dry-run.sh`, `check-system.sh` |
| Probe script | `~/.cache/rector/reports/need2lift/probe.sh` | `bash probe.sh <project> 7.4 8.2 8.4 8.5`. Serves each runtime and prints status, content type and body. Outside the repository. |
| Host vendor | `vendor/` (gitignored) | `composer install --ignore-platform-reqs` on the host, for Rector only |
| Docker | server 29.9.3, compose 5.7.0 | `docker compose build`, `docker compose up -d` |
| Host PHP | 8.5.4 | Runs Rector and the analysis commands |
| PHPUnit 9.6.38 | `vendor/bin/phpunit` (dev dependency) | Black-box golden runner. Verified 2026-11-09 on host PHP 8.5.4 and in `php:7.4-cli` with `--network host` (`host.docker.internal` does not resolve on this Linux host). 15 files in `tests/golden/`. Default image: `BASE_URL=http://localhost:8080 vendor/bin/phpunit -c tests/phpunit.xml.dist`. Failure route: `docker compose -f docker-compose.yml -f docker-compose.test.yml up -d --build`, then `GOLDEN_FAILURE_ROUTE=1 BASE_URL=http://localhost:8080 vendor/bin/phpunit -c tests/phpunit.xml.dist`. Re-record only with `GOLDEN_RECORD=1` (needs approval). |
| PHPStan 2.3.5 | `vendor/bin/phpstan` (dev dependency), `phpstan.neon` | `vendor/bin/phpstan analyse --level=max --no-progress`. Paths: `public`, `config`, `module`. 0 errors, no baseline. Verified on host PHP 8.5.4 and `php:7.4-cli`. |

Rector from the project root, with the committed config once p0-04 lands: `TMPDIR=~/.cache/rector rector process --config rector.php --dry-run`.

## Still missing

- Nothing blocking. The project needs no git remote, no production runtime and no CI ([ADR-0013](docs/adr/0013-demo-no-production-no-ci.md)).
- **`Dockerfile` Composer stage.** `FROM composer:2.2` is untested on PHP 8.5. It is a check to run, not missing data (verify in p1-01).

## Approval needed

The repository has no contributing guide, so the approval list is the one agreed in the grilling (Q11):

- Changing a frozen contract or a golden file (any todo that edits `tests/golden/`).
- Changing the `composer.json` PHP constraint or the image's PHP version (p1-01, p2-01 if needed, p3-02).
- Adding a dependency that is not needed to reach Symfony 7.4 on PHP 8.5: PHPUnit (p0-01), PHPStan (p0-03), Rector (p0-04), PHPUnit upgrade (p3-03). Symfony packages (p2-01) are needed and still listed for approval because they add to `composer.json`.
- Removing the `--ignore-platform-req=php` override (p3-02).

## References

- Rector, Zend migration guide: https://getrector.com/upgrade/zend
- PHP supported versions: https://www.php.net/supported-versions.php
- Symfony 7.4 release: https://symfony.com/releases/7.4
- Laminas MVC end-of-life schedule (why Laminas was not chosen): https://getlaminas.org/blog/2026-03-06-laminas-mvc-eol-schedule.html
