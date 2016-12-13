<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Unavailability\Add;
use Proximum\Vimeet\Application\Command\Unavailability\Remove;
use Proximum\Vimeet\Application\Command\Unavailability\Update;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability\AddUnavailabilityType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability\UpdateUnavailabilityType;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated See ProgramController and HappeningController
 */
class ScheduleController extends Controller
{
    /**
     * @param Request   $request
     * @param EventDomain $eventDomain
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function displayAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$this->get('domain.key_dates.checker.schedule_access_checker')
            ->allowedToAccess($eventDomain->getEvent())
        ){
            throw $this->createNotFoundException();
        }

        if ($sheet->getEvent()->getId() !== $eventDomain->getEvent()->getId()
            || null === $sheet->getUserParticipant($this->getUser())
        ) {
            throw $this->createNotFoundException('The current User is not allowed to see this schedule');
        }

        $participantSchedules = $this
            ->get('vimeet.application.components.schedule.schedule_builder')
            ->buildForSheet($sheet, $request->getLocale());

        return $this->render('EventBundle:Schedule:display.html.twig', [
            'event'                => $eventDomain->getEvent(),
            'participantSchedules' => $participantSchedules,
            'sheet'                => $sheet,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventDomain $eventDomain
     * @param Sheet     $sheet
     *
     * @return RedirectResponse|Response
     */
    public function addUnavailabilityAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if ($sheet->getEvent() !== $eventDomain->getEvent() || null === $sheet->getUserParticipant($this->getUser())) {
            throw $this->createNotFoundException('The current User is not allowed to create an unavailibity');
        }

        $command = new Add();
        $form    = $this->createForm(AddUnavailabilityType::class, $command, [
            'action' => $this->generateUrl('event_sheet_schedule_add_unavailability', ['sheet' => $sheet->getId()]),
            'method' => 'POST',
            'sheet'  => $sheet,
            'locale' => $request->getLocale(),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', 'flash.event.schedule.unavailability.add.success');

            return $this->redirectToRoute('event_sheet_schedule', ['sheet' => $sheet->getId()]);
        }

        return $this->render('EventBundle:Schedule:addUnavailability.html.twig', [
            'event' => $eventDomain->getEvent(),
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request        $request
     * @param EventDomain      $eventDomain
     * @param Sheet          $sheet
     * @param Unavailability $unavailability
     *
     * @return RedirectResponse|Response
     */
    public function updateUnavailabilityAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Unavailability $unavailability)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if ($sheet->getEvent()->getId() !== $eventDomain->getEvent()->getId()
            || null === $sheet->getUserParticipant($this->getUser())
        ) {
            throw $this->createNotFoundException('The current User is not allowed to edit this unavailibity');
        }

        $command = new Update($unavailability);
        $form    = $this->createForm(UpdateUnavailabilityType::class, $command, [
            'action' => $this->generateUrl('event_sheet_schedule_update_unavailability', [
                'sheet'          => $sheet->getId(),
                'unavailability' => $unavailability->getId(),
            ]),
            'method' => 'POST',
            'sheet'  => $sheet,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', 'flash.event.schedule.unavailability.update.success');

            return $this->redirectToRoute('event_sheet_schedule', ['sheet' => $sheet->getId()]);
        }

        return $this->render('EventBundle:Schedule:updateUnavailability.html.twig', [
            'event' => $eventDomain->getEvent(),
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param EventDomain      $eventDomain
     * @param Sheet          $sheet
     * @param Unavailability $unavailability
     *
     * @return RedirectResponse|Response
     */
    public function removeUnavailabilityAction(EventDomain $eventDomain, Sheet $sheet, Unavailability $unavailability)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if ($sheet->getEvent()->getId() !== $eventDomain->getEvent()->getId()
            || null === $sheet->getUserParticipant($this->getUser())
        ) {
            throw $this->createNotFoundException('The current User is not allowed to remove this unavailibity');
        }

        $command = new Remove($unavailability);
        $this->get('tactician.commandbus')->handle($command);
        $this->addFlash('success', 'flash.event.schedule.unavailability.remove.success');

        return $this->redirectToRoute('event_sheet_schedule', ['sheet' => $sheet->getId()]);
    }
}
