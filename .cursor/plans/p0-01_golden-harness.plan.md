---
name: p0-golden-harness
overview: Add a black-box PHPUnit 9.6 runner that sends real HTTP requests to BASE_URL. It runs against the Compose image, so .htaccess and the vhost are covered. Nothing in the greeting or error contract changes yet.
todos:
  - id: verify
    content: Verify the preconditions below; save the output of `docker compose ps` and `curl -si http://localhost:8080/api/hello` as the baseline
    status: completed
  - id: pr1-golden-harness
    content: "PR 1: Add phpunit/phpunit ^9.6 as dev dependency, tests/phpunit.xml.dist and a smoke test (Approval: new dev dependency)"
    status: completed
  - id: validate
    content: Run the Validation checks; update MIGRATION.md and the master plan; one commit per PR
    status: completed
isProject: false
---

# p0-golden-harness: Black-box PHPUnit runner

This is todo `p0-golden-harness` from [the master plan](need2lift-migration.plan.md) (section "Phase 0", `p0-golden-harness`). Read [ADR-0010](../../docs/adr/0010-black-box-harness.md) first.

## Preconditions

- Verify at start: `docker compose up -d --build` builds the current image and `curl -si http://localhost:8080/api/hello` answers with status 200.
- Verify at start: `composer.json` still has `"php": "^7.4"` and `composer.lock` is unchanged (`git diff --stat composer.json composer.lock` is empty).

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`.

- `docker-compose.yml`: service `app`, `build: .`, port `8080:80`. No test service.
- `Dockerfile` line 3: `FROM php:7.4-apache`. Line 33: `composer install --no-dev`, so dev dependencies are not in the image. The harness must run outside the image.
- `composer.json`: no `require-dev`. Adding one changes the lock file.
- `.dockerignore` excludes `.git`, `.idea`, `.agents` and `vendor`. A `tests/` directory is not copied into the image.
- The greeting is produced by `module/Application/src/Controller/HelloController.php` lines 13-16, with `PHP_VERSION` in the body.

## Changes

One commit (pull request) per item, in this order.

1. **PR 1: Black-box PHPUnit runner.** **Approval** (new dev dependency; adding it changes `composer.json` and `composer.lock`). Add `phpunit/phpunit` ^9.6 with `composer require --dev phpunit/phpunit:^9.6`. Add `tests/phpunit.xml.dist`, with a test suite named `golden` and a `tests/Golden/` directory. Add one smoke test that sends `GET /api/hello` to `getenv('BASE_URL')` and asserts status 200. Use a plain HTTP client (curl), which every runtime has. No doubles, because the code has no transports.
   Done when `BASE_URL=http://localhost:8080 vendor/bin/phpunit -c tests/phpunit.xml.dist` passes and the test fails with a clear message when `BASE_URL` is unset.

## Tests

- The smoke test is the only test in this todo. It asserts status and nothing else; golden files come in p0-02.
- A failure means the harness or the running image is wrong, never a reason to change the test.

## Validation

- `docker compose up -d --build`, then the golden command above.
- Runner in a container: `docker run --rm -v "$PWD":/app -w /app -e BASE_URL=http://host.docker.internal:8080 php:7.4-cli vendor/bin/phpunit -c tests/phpunit.xml.dist` (verify the host name on Linux; `--network host` is the fallback).
- `git diff --stat` shows only `composer.json`, `composer.lock`, `tests/` and `MIGRATION.md`. No file under `module/` or `public/` changes.
- Record the PHPUnit version and runtime in `MIGRATION.md`, then set the todo `completed` in the master plan.

## Rollback

Revert the commit and run `composer install` to restore the lock file. The image is not affected because dev dependencies are not installed in it.
