<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Response;

trait OptionsTrait
{
    private function setCorsHeaders(Response $response)
    {
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'X-API-Key');
        $response->headers->set('Access-Control-Allow-Methods', 'GET');
    }
}
