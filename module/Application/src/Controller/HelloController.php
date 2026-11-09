<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class HelloController extends AbstractActionController
{
    public function helloAction()
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=utf-8');
        $response->setContent(sprintf(
            'Hello from PHP %s',
            PHP_VERSION
        ));

        return $response;
    }
}
