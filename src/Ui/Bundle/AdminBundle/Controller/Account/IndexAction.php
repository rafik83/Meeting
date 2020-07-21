<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Account;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class IndexAction
{
    /** @var EngineInterface */
    private $engine;

    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function __invoke(): Response
    {
        return new Response($this->engine->render('AdminBundle:Account:index.html.twig'));
    }
}
