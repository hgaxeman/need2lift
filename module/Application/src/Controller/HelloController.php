<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class HelloController extends AbstractActionController
{
    public function helloAction()
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=utf-8');
        $response->setContent('Hello from PHP 7.4, Zend Framework 2');

        return $response;
    }
}
