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
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\ParticipateHappeningType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\AddUnavailabilityType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\UpdateUnavailabilityType;
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

        $participantSchedules = $this
            ->get('proximum.vimeet.application.components.schedule.schedule_builder')
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

        $command = new Add();
        $form    = $this->createForm(AddUnavailabilityType::class, $command, [
            'action' => $this->generateUrl('event_sheet_schedule_add_unavailability', ['sheet' => $sheet->getId()]),
            'method' => 'POST',
            'sheet'  => $sheet,
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
     * @param Request        $request
     * @param EventView      $eventView
     * @param Sheet          $sheet
     * @param Unavailability $unavailability
     *
     * @return RedirectResponse|Response
     */
    public function removeUnavailabilityAction(Request $request, EventView $eventView, Sheet $sheet, Unavailability $unavailability)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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
        // Get user participant
        $participant = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getParticipantForUserAndSheet($this->getUser(), $sheet);

        // Command and form
        $command = new Participate($happening, [$participant]);
        $form    = $this->createForm(ParticipateHappeningType::class, $command, [
            'sheet'     => $sheet,
            'happening' => $happening,
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
     * @param Request     $request
     * @param EventView   $eventView
     * @param Sheet       $sheet
     * @param Happening   $happening
     * @param Participant $participant
     *
     * @return RedirectResponse
     */
    public function unparticipateHappeningAction(Request $request, EventView $eventView, Sheet $sheet, Happening $happening, Participant $participant)
    {
        $this->get('tactician.commandbus')->handle(new Unparticipate($happening, $participant));
        $this->addFlash('success', 'flash.event.schedule.happening.unparticipate.success');

        return $this->redirectToRoute('event_sheet_schedule', ['sheet' => $sheet->getId()]);
    }
}
