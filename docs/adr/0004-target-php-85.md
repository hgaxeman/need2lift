---
status: accepted
---

# Finish on PHP 8.5

The final runtime is PHP 8.5: active support until 31 December 2027 and security support until 31 December 2029 (php.net, checked 2026-11-09). It runs with Symfony 7.4 ([ADR-0003](0003-target-symfony-lts.md)).

## Considered options

- PHP 8.4. Rejected: active support ends 31 December 2026, so the migration would land on a branch already in security-only support.
- PHP 8.6. Rejected: it was at release candidate 5 on 2026-11-09, with general availability planned for 19 November 2026.
