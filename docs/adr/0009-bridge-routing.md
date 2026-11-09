---
status: accepted
---

# Route to Symfony by an explicit path list; Zend keeps the rest until removal

During the bridge, `public/index.php` sends a request to Symfony only when its exact path is on an explicit list. Every other request still goes to Zend. The hello request (`/api/hello`) is the first and only path to move. Unknown requests and dispatch failures stay with Zend until the removal step. There, Symfony takes them over and their golden files must still pass unchanged.

## Considered options

- Symfony first, and Zend only when Symfony answers 404. Rejected: a route missing on the Symfony side would fall through silently.

## Consequences

- The list matches the path exactly and case-sensitively. `/api/hello/` and `/API/HELLO` are not on it, so they stay unknown requests (404 `Not found`, [ADR-0002](0002-frozen-contracts.md)).
- Measured on Symfony 7.4.23 (2026-11-09): the default `RedirectableCompiledUrlMatcher` answers `GET /api/hello/` with a 301 redirect to `/api/hello` when only route `/api/hello` exists. That redirect is disabled by using `CompiledUrlMatcher` instead (`App\DependencyInjection\DisableTrailingSlashRedirectPass`). With redirects off, Symfony returns 404 for `/api/hello/`. During Phase 2 the bridge list still keeps `/api/hello/` on Zend, so the golden 404 `Not found` is unchanged either way.
