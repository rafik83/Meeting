<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\StyleGuide;

use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ShowAction
{
    /** @var Environment */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, EventDomain $eventDomain): Response
    {
        $event = $eventDomain->getEvent();

        return new Response(
            $this->twig->render(
                'EventBundle:StyleGuide:show.html.twig',
                [
                    'event' => $event,
                ]
            )
        );
    }
}
