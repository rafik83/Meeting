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
use Proximum\Vimeet\Application\Query\Agenda\SheetsAvailableBySlotQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $this->checkAccess($eventDomain, $sheet);

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
        $this->checkAccess($eventDomain, $sheet);

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

    /**
     * @param EventDomain      $eventDomain
     * @param UserInterface    $user
     * @param Sheet            $excludedSheet
     * @param SlotAvailability $slot
     *
     * @return JsonResponse
     */
    public function countSheetsAvailableBySlotAction(
        EventDomain $eventDomain,
        UserInterface $user,
        Sheet $excludedSheet,
        SlotAvailability $slot
    ): JsonResponse {
        $this->checkAccess($eventDomain, $excludedSheet);

        $participant = $excludedSheet->getUserParticipant($user);

        if ($participant === null || $participant->getSheet() !== $excludedSheet) {
            return new JsonResponse(['response' => 'participant not found'], 404);
        }

        $query = new SheetsAvailableBySlotQuery($eventDomain->getEvent(), $excludedSheet, $slot);
        $this
            ->get('query.agenda.sheets_available_by_slot_query_handler')
            ->handle($query);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet $sheet
     */
    private function checkAccess(EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(AgendaAccessVoter::PERMISSION, $eventDomain->getEvent());
    }
}
