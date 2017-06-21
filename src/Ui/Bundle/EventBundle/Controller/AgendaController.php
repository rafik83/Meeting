<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class AgendaController extends Controller
{
    /**
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param UserInterface $user
     *
     * @return RedirectResponse
     */
    public function indexAction(EventDomain $eventDomain, Sheet $sheet, UserInterface $user)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(AgendaAccessVoter::PERMISSION, $eventDomain->getEvent());

        $participant = $sheet->getUserParticipant($user);

        if (null !== $participant) {
            return $this->redirectToRoute(
                'event_agenda_participant',
                ['participant' => $participant->getId(), 'sheet' => $sheet->getId()]
            );
        }

        return $this->redirectToRoute(
            'event_agenda_participant',
            ['participant' => $sheet->getFirstParticipant()->getId(), 'sheet' => $sheet->getId()]
        );
    }

    /**
     * @param EventDomain   $eventDomain
     * @param Request       $request
     * @param Participant   $participant
     * @param Sheet         $sheet
     * @param UserInterface $user
     *
     * @return Response
     */
    public function participantAction(
        EventDomain $eventDomain,
        Request $request,
        Participant $participant,
        Sheet $sheet,
        UserInterface $user
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(AgendaAccessVoter::PERMISSION, $eventDomain->getEvent());

        if ($participant->getSheet() !== $sheet) {
            throw $this->createNotFoundException('This participant is not in this sheet');
        }

        /** @var AgendaView $agenda */
        $agenda = $this->get('tactician.commandbus.query')->handle(new AgendaViewQuery(
            $eventDomain->getEvent(),
            $sheet,
            $participant,
            $request->getLocale(),
            $user
        ));

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $participant->getSheet()->getType(),
            TipTranslationViewQueryHandler::CONTEXT_AGENDA,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        return $this->render('EventBundle:Agenda:index.html.twig', [
            'event'               => $eventDomain->getEvent(),
            'agenda'              => $agenda,
            'sheet'               => $sheet,
            'tipTranslationViews' => $tipTranslationViews,
        ]);
    }
}
