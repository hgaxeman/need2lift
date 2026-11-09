---
status: superseded by ADR-0013
---

# The Compose image is production; GitHub Actions gates it

No other runtime is described: the `Dockerfile`, which Compose publishes on `8080:80`, is treated as production for this migration. Rollback is reverting the merge and rebuilding the image.

The base image moves from `php:7.4-apache` to the official `php:8.5-apache`, dropping the Debian Bullseye archive workaround. It keeps `intl`, `mod_rewrite`, the vhost, and `display_errors=Off` ([ADR-0005](0005-incremental-one-process.md)).

CI is GitHub Actions, added once the repository has a remote. A workflow builds the image, runs the golden tests against it, runs PHPStan and the Rector dry run, and blocks network egress except to package registries. Making it a required check needs approval.
