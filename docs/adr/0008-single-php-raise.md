---
status: accepted
---

# Raise PHP once, from 7.4 to 8.5, ignoring only Composer's PHP requirement

The runtime moves from PHP 7.4 straight to 8.5, in one step. On 2026-11-09 the locked Zend packages returned the same frozen responses on 8.2, 8.4 and 8.5 ([ADR-0005](0005-incremental-one-process.md)), so intermediate runtimes would be shipped by nobody and would prove nothing more.

The locked Zend packages declare PHP `^5.6 || ^7.0` (or `^7.1`), so Composer refuses them on PHP 8. Until Zend is removed, the image installs with `--ignore-platform-req=php`. Every other platform check, extensions included, stays on. The override is deleted in the same change that removes Zend.

## Considered options

- One minor version at a time, 8.0 through 8.5. Rejected: six runtimes that nobody ships.
- A stepping stone through Laminas MVC 3.8 on PHP 8.4. Rejected: the whole namespace move would be thrown away when Symfony takes over.
- `--ignore-platform-reqs`, which ignores everything. Rejected: it would also hide missing extensions.
