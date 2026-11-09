---
status: accepted
---

# Move incrementally, in one process, raising PHP before Symfony joins

The old application keeps serving every request while the runtime rises. Then Symfony 7.4 runs next to it in the same process, takes over the hello request once its golden files pass unchanged, and the Zend application is removed. Each step merges on its own.

The order is forced. No maintained Symfony release installs on PHP 7.4. Symfony 7.4 needs PHP 8.2, so PHP rises first and Symfony joins afterwards.

On 2026-11-09 the locked Zend packages were served read-only on PHP 7.4.33, 8.2.35, 8.4.27 and 8.5.12, using PHP's built-in server, at commit `e21c4b7`. Every probe returned the same status and content type. The greeting body differs only in the PHP version it names. The probe covered a hello request with GET, POST, PUT, DELETE, OPTIONS or a query string; `/api/hello/`; `/API/HELLO`; and `/nope` with GET or POST. On PHP 8 the only messages were deprecations inside `vendor/zendframework/`. The probe output is in `~/.cache/rector/reports/need2lift/probe-after-e21c4b7-20261109.txt`, outside the repository.

## Consequences

- `display_errors` must stay off on every PHP 8 runtime until Zend is removed. With it on, the deprecations are printed into the body, which then turns into HTML.
- The locked Zend packages declare `^5.6 || ^7.0` or `^7.1`, so Composer refuses them on PHP 8 unless that requirement is overridden. How to override is a separate decision.

## Considered options

- Rewrite and cut over in one step. Rejected: each incremental step can merge and be reverted on its own.
- Run Symfony next to Zend on PHP 7.4. Not possible: nothing maintained from Symfony installs there.
