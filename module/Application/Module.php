<?php

namespace Application;

use Zend\EventManager\EventInterface;
use Zend\Http\Response as HttpResponse;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\Mvc\MvcEvent;
use RuntimeException;

class Module implements BootstrapListenerInterface
{
    /**
     * @return array<array-key, mixed>
     */
    public function getConfig(): array
    {
        $config = include __DIR__ . '/config/module.config.php';
        if (!is_array($config)) {
            throw new RuntimeException('Module config must return an array');
        }

        return $config;
    }

    public function onBootstrap(EventInterface $event)
    {
        if (!$event instanceof MvcEvent) {
            return;
        }

        $event->getApplication()->getEventManager()->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            function (MvcEvent $errorEvent) {
                $response = $errorEvent->getResponse();
                if (!$response instanceof HttpResponse) {
                    return;
                }

                $status = $response->getStatusCode();
                if ($status < 400) {
                    $status = 500;
                    $response->setStatusCode($status);
                }

                $response->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=utf-8');
                $response->setContent($status === 404 ? 'Not found' : 'Error');
                $errorEvent->setResult($response);

                return $response;
            },
            -100
        );
    }
}
