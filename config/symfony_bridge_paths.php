<?php

declare(strict_types=1);

/**
 * Paths sent to Symfony by public/index.php (ADR-0009).
 * "*" means every request goes to Symfony (Phase 3).
 *
 * @return list<string>
 */
return [
    \App\Bridge\SymfonyPathList::CATCH_ALL,
];
