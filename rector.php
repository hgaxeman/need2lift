<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/public',
        __DIR__ . '/config',
        __DIR__ . '/module',
    ])
    ->withSkip([
        '*/vendor/*',
    ])
    ->withPhpVersion(PhpVersion::PHP_85)
    ->withTypeCoverageLevel(0);
