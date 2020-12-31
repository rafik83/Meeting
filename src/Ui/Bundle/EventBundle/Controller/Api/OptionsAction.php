<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Response;

class OptionsAction
{
    use OptionsTrait;

    public function __invoke(): Response
    {
        $response = new Response('', 204);
        $this->setCorsHeaders($response);

        return $response;
    }
}
