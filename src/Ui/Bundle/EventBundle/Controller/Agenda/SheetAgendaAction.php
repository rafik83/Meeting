<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class SheetAgendaAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var EngineInterface */
    private $engine;

    public function __construct(QueryBusInterface $queryBus, EngineInterface $engine)
    {
        $this->queryBus = $queryBus;
        $this->engine = $engine;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        UserDomain $userDomain
    ): Response {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();
        $participant = $sheet->getUserParticipant($user);

        if (null !== $participant) {
            $participant = $sheet->getFirstParticipant();
        }

        /** @var AgendaView $agenda */
        $agenda = $this->queryBus->handle(
            new AgendaViewQuery(
                $eventDomain->getEvent(),
                $sheet,
                $participant,
                $request->getLocale(),
                $user,
                true
            )
        );

        return new Response(
            $this->engine->render(
                '@Event/Agenda/sheet_agenda.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'agenda' => $agenda,
                    'participant' => $participant,
                ]
            )
        );
    }
}
