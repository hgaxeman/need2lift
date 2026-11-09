<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;

/**
 * Symfony's default RedirectableCompiledUrlMatcher 301s /api/hello/ to /api/hello.
 * ADR-0009 / frozen contract: /api/hello/ must stay a 404 Not found, so disable redirects.
 */
final class DisableTrailingSlashRedirectPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('router.default')) {
            return;
        }

        $definition = $container->getDefinition('router.default');
        $options = $definition->getArgument(2);
        if (!is_array($options)) {
            return;
        }

        $options['matcher_class'] = CompiledUrlMatcher::class;
        $definition->setArgument(2, $options);
    }
}
