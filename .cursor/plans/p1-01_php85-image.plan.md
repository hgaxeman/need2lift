---
name: p1-php85-image
overview: "Move the runtime from PHP 7.4 to 8.5 in one step: base image php:8.5-apache, Composer ignores only the PHP requirement, and display_errors stays off. Zend keeps serving the same replies."
todos:
  - id: verify
    content: Verify the composer:2.2 build stage runs on PHP 8.5 and that the golden suite passes against the current image; save both outputs
    status: completed
  - id: pr1-php85
    content: "PR 1: Dockerfile base php:8.5-apache, Bullseye workaround removed, Composer with --ignore-platform-req=php (Approval: runtime image change)"
    status: completed
  - id: validate
    content: Run the Validation checks; golden files unchanged; update MIGRATION.md and the master plan; one commit per PR
    status: completed
isProject: false
---

# p1-php85-image: Raise the runtime to PHP 8.5

This is todo `p1-php85-image` from [the master plan](need2lift-migration.plan.md) (section "Phase 1", `p1-php85-image`). Read [ADR-0004](../../docs/adr/0004-target-php-85.md), [ADR-0005](../../docs/adr/0005-incremental-one-process.md), [ADR-0008](../../docs/adr/0008-single-php-raise.md) and [ADR-0012](../../docs/adr/0012-runtime-and-delivery.md) first.

## Preconditions

- Phase 0 is completed: golden suite, PHPStan and Rector config. There is no CI (ADR-0013).
- Verify at start: `composer.json` still has `"php": "^7.4"`, so the image needs the Composer override to install the locked Zend packages.

## Current state (verified)

Measured on 2026-11-09 at commit `e21c4b7`.

- `Dockerfile` line 1: `FROM composer:2.2 AS composer`. Line 3: `FROM php:7.4-apache`.
- `Dockerfile` lines 5-15: the Bullseye archive workaround (`sed` on `/etc/apt/sources.list`, `Acquire::Check-Valid-Until`), `apt-get install unzip libicu-dev`, `docker-php-ext-install intl`.
- `Dockerfile` lines 17-22: `a2enmod rewrite`, `ServerName localhost`, and `display_errors=Off`, `log_errors=On` in `/usr/local/etc/php/conf.d/errors.ini`.
- `Dockerfile` line 25: `COPY docker/vhost.conf`. Lines 33-34: `composer install --no-dev --no-interaction --prefer-dist --no-progress` and `chown -R www-data:www-data`.
- Probe 2026-11-09: the Zend application returns the same replies on PHP 8.2, 8.4 and 8.5 as on 7.4. Deprecations go to stderr only, from `vendor/zendframework/`.
- Rector 2026-11-09: PHP 8.5 upgrade set reports 0 files and 0 errors.

## Open questions (ask before the change)

- Whether `composer:2.2` is replaced by a newer Composer image. The stage only copies the Composer binary, so it is not changed here unless verify fails. (verify)

## Changes

One commit (pull request) per item, in this order.

1. **PR 1: PHP 8.5 image.** **Approval** (runtime image change). Change line 3 to `FROM php:8.5-apache`. Remove the archive workaround in lines 5-15 and keep `apt-get install unzip libicu-dev` and `docker-php-ext-install intl`. Verify that `apt-get` works on the Debian release of `php:8.5-apache`; the package names may differ (verify). Keep `a2enmod rewrite`, the `ServerName`, the `errors.ini` with `display_errors=Off` and `log_errors=On`, and the vhost copy. Change the Composer command to `composer install --no-dev --no-interaction --prefer-dist --no-progress --ignore-platform-req=php`. Do not change `composer.json` or `composer.lock`.
   Done when the image builds, the golden suite passes unchanged, and `display_errors` is off in the running container.

## Tests

- The golden suite must pass unchanged, including the greeting with the 8.5 version, normalised.
- Check `docker compose exec app php -i | grep -i display_errors` shows `Off`.
- Check `docker compose exec app php -m` lists `intl`, `mbstring`, `json`, `curl`, `openssl`, `xml`, `zip` (the extensions the Zend packages use). Any missing extension fails the todo.

## Validation

- `docker compose -f docker-compose.yml -f docker-compose.test.yml up -d --build`, then the golden command from [the master plan](need2lift-migration.plan.md#validation).
- `vendor/bin/phpstan analyse --level=max --no-progress`.
- `TMPDIR=~/.cache/rector rector process --config rector.php --dry-run --no-progress-bar`. The config still targets 7.4 until p1-02.
- `git diff --stat` shows only `Dockerfile`, `MIGRATION.md` and the master plan.
- Record the runtime change and the golden result in `MIGRATION.md`, then set the todo `completed` in the master plan.

## Rollback

Revert the commit and rebuild: `docker compose build --no-cache`. The image returns to `php:7.4-apache`. There is no deploy, so a rollback is a rebuild of the reverted commit (ADR-0013).
