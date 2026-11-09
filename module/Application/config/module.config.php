<?php

namespace Application;

return [
    'router' => [
        'routes' => [
            'api-hello' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/api/hello',
                    'defaults' => [
                        'controller' => Controller\HelloController::class,
                        'action' => 'hello',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            Controller\HelloController::class => Controller\HelloController::class,
        ],
    ],
];
