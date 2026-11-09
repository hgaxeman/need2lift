---
status: accepted
---

# A minimal Symfony 7.4 install

Symfony arrives as `symfony/framework-bundle`, `symfony/runtime`, `symfony/dotenv` and `symfony/yaml`, all at 7.4, with Symfony Flex. Twig, Doctrine, Monolog and the security bundle are not installed ([ADR-0007](0007-out-of-scope.md)). New code lives under `src/` in the `App\` namespace, next to `module/Application/src/`, until Zend is removed. `APP_SECRET` comes from the environment and is never committed.
