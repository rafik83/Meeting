<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Application\View\Agenda\ParticipantView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class SheetAgendaAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        QueryBusInterface $queryBus,
        EngineInterface $engine
    ) {
        $this->authorizationChecker = $authorizationChecker;
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

        if (
            !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->authorizationChecker->isGranted(AgendaAccessVoter::PERMISSION, $event)
        ) {
            throw new AccessDeniedException();
        }

        $user = $userDomain->getUser();
        $participant = $sheet->getUserParticipant($user);

        if (null === $participant) {
            $participant = $sheet->getFirstParticipant();
            $user = $participant->getUser();
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

        $myParticipant = $sheet->getUserParticipant($user);
        $otherParticipants = $agenda->participants;

        if ($myParticipant !== null) {
            $otherParticipants = \array_filter($agenda->participants, function (ParticipantView $otherParticipant) use ($myParticipant) {
                return $myParticipant->getId() !== $otherParticipant->id;
            });
        }


        return new Response(
            $this->engine->render(
                '@Event/Agenda/sheet_agenda.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'agenda' => $agenda,
                    'myParticipant' => $myParticipant,
                    'otherParticipants' => $otherParticipants,
                    'participant' => $participant,
                    'tipTranslationViews' => $this->queryBus->handle(new TipTranslationViewQuery(
                        $sheet,
                        $user,
                        TipTranslationViewQueryHandler::CONTEXT_AGENDA,
                        $request->getLocale()
                    )),
                ]
            )
        );
    }
}
