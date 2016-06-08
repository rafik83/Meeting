<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Application\Command\Happening\Unparticipate;
use Proximum\Vimeet\Application\Command\Unavailability\Add;
use Proximum\Vimeet\Application\Command\Unavailability\Remove;
use Proximum\Vimeet\Application\Command\Unavailability\Update;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Happening\ParticipateHappeningType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability\AddUnavailabilityType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability\UpdateUnavailabilityType;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function displayAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($sheet->getEvent()->getId() !== $eventView->getId()
            || null === $sheet->getUserParticipant($this->getUser())
        ) {
            throw $this->createNotFoundException('The current User is not allowed to see this schedule');
        }

        $participantSchedules = $this
            ->get('vimeet.application.components.schedule.schedule_builder')
            ->buildForSheet($sheet, $request->getLocale());

        return $this->render('EventBundle:Schedule:display.html.twig', [
            'eventView'            => $eventView,
            'participantSchedules' => $participantSchedules,
            'sheet'                => $sheet,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return RedirectResponse|Response
     */
    public function addUnavailabilityAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($sheet->getEvent()->getId() !== $eventView->getId()
            || null === $sheet->getUserParticipant($this->getUser())
        ) {
            throw $this->createNotFoundException('The current User is not allowed to create an unavailibity');
        }

        $command = new Add();
        $form    = $this->createForm(AddUnavailabilityType::class, $command, [
            'action' => $this->generateUrl('event_sheet_schedule_add_unavailability', ['sheet' => $sheet->getId()]),
            'method' => 'POST',
            'sheet'  => $sheet,
            'locale' => $request->getLocale(),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', 'flash.event.schedule.unavailability.add.success');

            return $this->redirectToRoute('event_sheet_schedule', ['sheet' => $sheet->getId()]);
        }

        return $this->render('EventBundle:Schedule:addUnavailability.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request        $request
     * @param EventView      $eventView
     * @param Sheet          $sheet
     * @param Unavailability $unavailability
     *
     * @return RedirectResponse|Response
     */
    public function updateUnavailabilityAction(Request $request, EventView $eventView, Sheet $sheet, Unavailability $unavailability)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($sheet->getEvent()->getId() !== $eventView->getId()
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
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', 'flash.event.schedule.unavailability.update.success');

            return $this->redirectToRoute('event_sheet_schedule', ['sheet' => $sheet->getId()]);
        }

        return $this->render('EventBundle:Schedule:updateUnavailability.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param EventView      $eventView
     * @param Sheet          $sheet
     * @param Unavailability $unavailability
     *
     * @return RedirectResponse|Response
     */
    public function removeUnavailabilityAction(EventView $eventView, Sheet $sheet, Unavailability $unavailability)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($sheet->getEvent()->getId() !== $eventView->getId()
            || null === $sheet->getUserParticipant($this->getUser())
        ) {
            throw $this->createNotFoundException('The current User is not allowed to remove this unavailibity');
        }

        $command = new Remove($unavailability);
        $this->get('tactician.commandbus')->handle($command);
        $this->addFlash('success', 'flash.event.schedule.unavailability.remove.success');

        return $this->redirectToRoute('event_sheet_schedule', ['sheet' => $sheet->getId()]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param Happening $happening
     *
     * @return RedirectResponse|Response
     */
    public function participateHappeningAction(Request $request, EventView $eventView, Sheet $sheet, Happening $happening)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($happening->getEvent()->getId() !== $eventView->getId()
            || $sheet->getEvent()->getId() !== $eventView->getId()
            || null === $sheet->getUserParticipant($this->getUser())
        ) {
            throw $this->createNotFoundException('Event not linked to this happening');
        }

        // Get user participant
        $participant = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getParticipantForUserAndSheet($this->getUser(), $sheet);

        // Command and form
        $command = new Participate($happening, [$participant]);
        $form    = $this->createForm(ParticipateHappeningType::class, $command, [
            'sheet'     => $sheet,
            'happening' => $happening,
            'locale'    => $request->getLocale(),
        ]);
        $form->add('submit', SubmitType::class);

        // Handle form
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);

            $this->addFlash('success', 'flash.event.schedule.happening.participate.success');

            return $this->redirectToRoute('event_sheet_schedule', ['sheet' => $sheet->getId()]);
        }

        return $this->render('EventBundle:Schedule:participateHappening.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'happening' => $happening,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param EventView   $eventView
     * @param Sheet       $sheet
     * @param Happening   $happening
     * @param Participant $participant
     *
     * @return RedirectResponse
     */
    public function unparticipateHappeningAction(EventView $eventView, Sheet $sheet, Happening $happening, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($happening->getEvent()->getId() !== $eventView->getId()
            || $sheet->getEvent()->getId() !== $eventView->getId()
            || null === $sheet->getUserParticipant($this->getUser())
        ) {
            throw $this->createNotFoundException('Event not linked to this happening');
        }

        $this->get('tactician.commandbus')->handle(new Unparticipate($happening, $participant));
        $this->addFlash('success', 'flash.event.schedule.happening.unparticipate.success');

        return $this->redirectToRoute('event_sheet_schedule', ['sheet' => $sheet->getId()]);
    }
}
