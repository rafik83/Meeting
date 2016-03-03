<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Participant\Delete;
use Proximum\Vimeet\Application\Command\Participant\Update;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Application\Exception\Participant\DeleteNotAllowedException;
use Proximum\Vimeet\Application\Exception\Participant\EmailCanNotBeNullException;
use Proximum\Vimeet\Application\Exception\Participant\UpdateNotAllowedException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\AddParticipantType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\ParticipantUpdateType;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ParticipantController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return RedirectResponse|Response
     */
    public function addAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $participantManager = $this->get('vimeet_infrastructure.application.components.participant.participant_manager');

        if ($participantManager->canAddParticipant($sheet) <= 0) {
            throw $this->createAccessDeniedException('You can not add a new participant');
        }

        $add  = new Add($sheet, $request->getLocale());
        $form = $this->createForm(AddParticipantType::class, $add, [
            'template' => $sheet->getType()->getParticipantTemplate(),
            'locale'   => $request->getLocale(),
            'submit'   => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('vimeet_infrastructure.vimeet.application.command.participant.add_handler')->handle($add);
                $this->addFlash('success', 'flash.sheet.add_participant.success');

                // Go to the sheet
                return $this->redirectToRoute('event_sheet', ['sheet' => $sheet->getId()]);
            } catch (EmailCanNotBeNullException $exception) {
                $form->get('email')->addError(new FormError('validators.field.required'));
            } catch (ParticipantAlreadyExistException $exception) {
                $form->get('email')->addError(new FormError('event.sheet.participant.already_exists'));
            } catch (RequiredDataEmptyException $exception) {
                foreach ($exception->getKeys() as $key) {
                    $form->get($key)->addError(new FormError('validators.field.required'));
                }
            }
        }

        return $this->render('VimeetAppBundle:Event/Participant:add.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request     $request
     * @param EventView   $eventView
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return Response
     */
    public function updateAction(Request $request, EventView $eventView, Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('vimeet_infrastructure.application.components.participant.participant_manager')->isUserAllowedToEditParticipant($sheet, $participant, $this->getUser())) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        $updateParticipant = new Update($sheet, $this->getUser(), $participant);
        $form              = $this->createForm(ParticipantUpdateType::class, $updateParticipant, [
            'template' => $sheet->getType()->getParticipantTemplate(),
            'locale'   => $request->getLocale(),
            'submit'   => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('vimeet_infrastructure.vimeet.application.command.participant.update_handler')->handle($updateParticipant);
                $this->addFlash('success', 'flash.sheet.update_participant.success');

                // Go to the sheet
                return $this->redirectToRoute('event_sheet', ['sheet' => $sheet->getId()]);
            } catch (UpdateNotAllowedException $exception) {
                $this->addFlash('error', 'flash.sheet.update_participant.access_denied');
            } catch (RequiredDataEmptyException $exception) {
                foreach ($exception->getKeys() as $key) {
                    $form->get($key)->addError(new FormError('validators.field.required'));
                }
            }
        }

        return $this->render('VimeetAppBundle:Event/Participant:update.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return RedirectResponse
     */
    public function deleteAction(Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('vimeet_infrastructure.application.components.participant.participant_manager')->isUserAllowedToEditParticipant($sheet, $participant, $this->getUser())) {
            throw $this->createAccessDeniedException('You are not allowed to delete this participant');
        }

        $delete = new Delete($sheet, $this->getUser(), $participant);

        try {
            $this->get('vimeet_infrastructure.vimeet.application.command.participant.delete_handler')->handle($delete);
            $this->addFlash('success', 'flash.sheet.delete_participant.success');
        } catch (DeleteNotAllowedException $exception) {
            $this->addFlash('error', 'flash.sheet.delete_participant.access_denied');
        }

        // Go to the sheet
        return $this->redirectToRoute('event_sheet', ['sheet' => $sheet->getId()]);
    }
}
