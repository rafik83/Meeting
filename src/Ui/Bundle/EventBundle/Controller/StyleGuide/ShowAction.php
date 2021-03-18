<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\StyleGuide;

use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class ShowAction
{
    /** @var EngineInterface */
    private $engine;

    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function __invoke(Request $request, EventDomain $eventDomain): Response
    {
        $event = $eventDomain->getEvent();

        return new Response(
            $this->engine->render(
                'EventBundle:StyleGuide:show.html.twig',
                [
                    'event' => $event,
                ]
            )
        );
    }
}
