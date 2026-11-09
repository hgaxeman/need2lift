---
status: accepted
---

# Golden files are black-box HTTP tests against the Compose image

Golden-file tests send real HTTP requests to a base URL taken from an environment variable. They run against the Apache image that Compose builds, so `.htaccess`, the vhost and PHP settings are covered as well. The same tests then run unchanged against every runtime and both frameworks. The runner is PHPUnit 9.6. The runner's own PHP is independent of the server's, since the tests only send HTTP. Running it inside a `php:7.4-cli` container is known to work; running it on PHP 8.5 is not verified. The runner is upgraded after Zend is removed.

A dispatch failure cannot be reached through any public request today. Its golden file uses a route that always throws, registered only by test configuration and absent from the default image (built without the test overlay). A guard test checks that the default image does not answer that route with the dispatch-failure reply.

## Considered options

- In-process tests that call each framework's kernel. Rejected: they bypass Apache and would have to be rewritten for Symfony.
- Covering the failure reply only with a unit test of the error listener. Rejected: that test would not carry over to Symfony.
