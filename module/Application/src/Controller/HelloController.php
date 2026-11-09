<?php

namespace Application\Controller;

use RuntimeException;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Controller\AbstractActionController;

class HelloController extends AbstractActionController
{
    public function helloAction(): HttpResponse
    {
        $response = $this->getResponse();
        if (!$response instanceof HttpResponse) {
            throw new RuntimeException('Expected an HTTP response');
        }

        $response->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=utf-8');
        $response->setContent(sprintf(
            'Hello from PHP %s',
            PHP_VERSION
        ));

        return $response;
    }
}
