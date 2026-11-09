<?php

declare(strict_types=1);

/**
 * Paths sent to Symfony by public/index.php (ADR-0009).
 * Exact, case-sensitive match on the path only (query string ignored).
 *
 * @return list<string>
 */
return [
    '/api/hello',
];
