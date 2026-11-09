---
status: accepted
---

# Move to Symfony 7.4, the current LTS

The target framework is Symfony 7.4, the long-term support release current on 2026-11-09 (latest patch seen that day: 7.4.23). It requires PHP 8.2 or newer, receives bug fixes until November 2028, and security fixes until November 2029. Patch releases inside 7.4 stay on that line. The final PHP version is a separate decision.

## Considered options

- Mezzio 3.28.1 (10 June 2026; PHP 8.2 through 8.5). Rejected: the chosen stack is Symfony.
- Laminas MVC 3.8.0, the rename that keeps the MVC shape. Rejected: it is security-only until 31 December 2028, requires PHP 8.1 through 8.4, and will not gain PHP 8.5. This application has one action and no views, so that shape is not worth preserving.
- Symfony 6.4, an older LTS still maintained on 2026-11-09 (security fixes until November 2027). Rejected: 7.4 is the current LTS and the longer support window.

## Consequences

No maintained Symfony release runs on the image the repository builds today, `php:7.4-apache`. Symfony 6.4 requires PHP 8.1, and Symfony 7.4 requires PHP 8.2. The framework cannot be installed on the current runtime. Every locked `zendframework/*` package refuses PHP 8 as well, so the two upgrades meet in the middle.
