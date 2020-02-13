<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Agenda;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class SheetAgendaAction
{
    /** @var EngineInterface */
    private $engine;

    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function __invoke(
        EventDomain $eventDomain,
        Sheet $sheet,
        UserDomain $userDomain
    ): Response {
        $event = $eventDomain->getEvent();

        return new Response($this->engine->render('@Event/Agenda/sheet_agenda.html.twig', ['event' => $event, 'sheet' => $sheet]));
    }
}
