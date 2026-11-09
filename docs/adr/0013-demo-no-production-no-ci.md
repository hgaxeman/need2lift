---
status: accepted
---

# Demo project: no production runtime, no CI/CD, checks run by hand

The repository is a demo. It has no production runtime and no deploy path, and it gets no CI/CD. The Compose image is the only runtime. The checks in the [master plan](../../.cursor/plans/need2lift-migration.plan.md#validation) are run by hand. This supersedes [ADR-0012](0012-runtime-and-delivery.md) and the CI clauses of [ADR-0006](0006-safety-net.md). The owner decided it on 2026-11-09.

The base-image decision from ADR-0012 still stands: the image moves to `php:8.5-apache`, keeping `intl`, `mod_rewrite`, the vhost and `display_errors=Off` ([ADR-0005](0005-incremental-one-process.md)).

## Consequences

- p0-ci is cancelled. Nothing needs a git remote.
- The "default image" is the image the repository builds without the test overlay. The guard test in [ADR-0010](0010-black-box-harness.md) checks that image.
- Rollback is reverting a commit and rebuilding the image locally.

## Considered options

- GitHub Actions, as ADR-0012 set out. Rejected: a demo has no runtime to protect, and CI would need a remote and a required check for little gain.
