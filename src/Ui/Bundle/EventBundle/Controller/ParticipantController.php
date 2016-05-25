<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Participant\Delete;
use Proximum\Vimeet\Application\Command\Participant\Update;
use Proximum\Vimeet\Application\Command\Participant\UpdateProfile;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Application\Exception\Participant\DeleteNotAllowedException;
use Proximum\Vimeet\Application\Exception\Participant\EmailCanNotBeNullException;
use Proximum\Vimeet\Application\Exception\Participant\UpdateNotAllowedException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AddParticipantType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\ParticipantUpdateType;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ParticipantController extends Controller
{
    /**
     * @param EventView   $eventView
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return Response
     */
    public function seeAction(EventView $eventView, Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user               = $this->getUser();
        $participantManager = $this->get('components.participant.participant_manager');

        if (!$participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        return $this->render('EventBundle:Participant:see.html.twig', [
            'eventView'   => $eventView,
            'participant' => $participant
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
    public function updateProfileAction(Request $request, EventView $eventView, Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user               = $this->getUser();
        $locale             = $request->getLocale();
        $participantManager = $this->get('components.participant.participant_manager');

        if (!$participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        $profileTemplate = $this->get('template.template_data_factory')->createProfileTemplate($participant, $locale);
        $event = $this->get('vimeet_infrastructure.repository.event_repository')->getById($eventView->id);

        $form = $this->createForm(ProfileType::class, $profileTemplate, [
            'event'    => $event,
            'locale'   => $locale,
            'template' => $profileTemplate,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data = array_filter($profileTemplate->getData(), function ($value) {
                return null !== $value;
            });

            $updateProfile = new UpdateProfile($profileTemplate, $participant, $locale, $data, $user);
            $this->get('tactician.commandbus')->handle($updateProfile);

            return $this->redirectToRoute('event_sheet');
        }

        return $this->render('EventBundle:Participant:updateProfile.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView()
        ]);
    }

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

        $participantManager = $this->get('components.participant.participant_manager');

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
                $this->get('tactician.commandbus')->handle($add);
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

        return $this->render('EventBundle:Participant:add.html.twig', [
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

        if (!$this->get('components.participant.participant_manager')->isUserAllowedToEditParticipant($sheet, $participant, $this->getUser())) {
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
                $this->get('tactician.commandbus')->handle($updateParticipant);
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

        return $this->render('EventBundle:Participant:update.html.twig', [
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

        if (!$this->get('components.participant.participant_manager')->isUserAllowedToDeleteParticipant($sheet, $participant, $this->getUser())) {
            throw $this->createAccessDeniedException('You are not allowed to delete this participant');
        }

        $delete = new Delete($sheet, $this->getUser(), $participant);

        try {
            $this->get('tactician.commandbus')->handle($delete);
            $this->addFlash('success', 'flash.sheet.delete_participant.success');
        } catch (DeleteNotAllowedException $exception) {
            $this->addFlash('error', 'flash.sheet.delete_participant.access_denied');
        }

        // Go to the sheet
        return $this->redirectToRoute('event_sheet', ['sheet' => $sheet->getId()]);
    }
}
