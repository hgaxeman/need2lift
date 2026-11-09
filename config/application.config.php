<?php

return [
    'modules' => [
        'Application',
    ],
    'module_listener_options' => [
        'module_paths' => [
            './module',
            './vendor',
        ],
        'config_glob_paths' => [
            'config/autoload/{,*.}{global,local}.php',
        ],
        'config_cache_enabled' => false,
        'module_map_cache_enabled' => false,
    ],
];
