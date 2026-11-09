<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    $map = [
        'Golden\\' => __DIR__ . '/Golden/',
        'Bridge\\' => __DIR__ . '/Bridge/',
    ];

    foreach ($map as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $path = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (is_file($path)) {
            require $path;
        }

        return;
    }
});
