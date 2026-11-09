<?php

/**
 * Test-only overlay. Mounted into config/autoload/ by docker-compose.test.yml.
 * Absent from the default image, so /test/dispatch-failure does not exist there.
 */

use Zend\Mvc\Controller\AbstractActionController;

return [
    'router' => [
        'routes' => [
            'test-dispatch-failure' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/test/dispatch-failure',
                    'defaults' => [
                        'controller' => 'TestDispatchFailure',
                        'action' => 'fail',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'TestDispatchFailure' => static function () {
                return new class extends AbstractActionController {
                    public function failAction()
                    {
                        throw new RuntimeException('intentional dispatch failure');
                    }
                };
            },
        ],
    ],
];
