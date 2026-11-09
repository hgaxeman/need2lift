<?php

namespace Application;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\Mvc\MvcEvent;

class Module implements BootstrapListenerInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
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
