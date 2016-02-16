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
use Proximum\Vimeet\Application\Command\Sheet\UpdateBlock;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Application\Exception\Participant\EmailCanNotBeNullException;
use Proximum\Vimeet\Application\Exception\Participant\IsNotLinkedToSheetException;
use Proximum\Vimeet\Application\Exception\Participant\IsNotOwnerException;
use Proximum\Vimeet\Application\Exception\Participant\OwnerCanNotBeDeletedException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\AddParticipantType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\DeleteParticipantType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\ParticipantUpdateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\UpdateBlockType;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SheetController extends Controller
{
    /**
     * Sheet.
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param string    $locale
     *
     * @return RedirectResponse|Response
     */
    public function sheetAction(Request $request, EventView $eventView, Sheet $sheet, $locale = null)
    {
        $locale = $locale ? : $request->getLocale();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createAccessDeniedException('No participant for this user attached on this sheet');
        }

        if (!$eventView->hasLocale($locale)) {
            throw $this->createNotFoundException('Locale not available for this event.');
        }

        return $this->render('VimeetAppBundle:Event/Sheet:index.html.twig', [
            'sheet'         => $sheet,
            'eventView'     => $eventView,
            'preview'       => $this->get('sheet.sheet_preview_factory')->createFromSheet($sheet, $this->getUser(), $locale),
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return RedirectResponse|Response
     */
    public function addParticipantAction(Request $request, EventView $eventView, Sheet $sheet)
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
        ]);
        $form->add('submit', SubmitType::class);

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

        return $this->render('VimeetAppBundle:Event/Sheet:addParticipant.html.twig', [
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
    public function updateParticipantAction(Request $request, EventView $eventView, Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->getUser()->getId() !== $participant->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not update other participant');
        }

        $updateParticipant = new Update($participant->getId(), $participant->getData());
        $form              = $this->createForm(ParticipantUpdateType::class, $updateParticipant, [
            'template' => $sheet->getType()->getParticipantTemplate(),
            'locale'   => $request->getLocale(),
            'submit'   => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this
                    ->get('vimeet_infrastructure.vimeet.application.command.participant.update_handler')
                    ->handle($updateParticipant);

                $this->addFlash('success', 'flash.sheet.update_participant.success');

                // Go to the sheet
                return $this->redirectToRoute('event_sheet', ['sheet' => $sheet->getId()]);
            } catch (RequiredDataEmptyException $exception) {
                foreach ($exception->getKeys() as $key) {
                    $form->get($key)->addError(new FormError('validators.field.required'));
                }
            }
        }

        return $this->render('VimeetAppBundle:Event/Sheet:updateParticipant.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param string    $locale
     * @param int       $block
     *
     * @return Response
     */
    public function updateBlockAction(Request $request, EventView $eventView, Sheet $sheet, $locale, $block)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$eventView->hasLocale($locale)) {
            throw $this->createNotFoundException('This locale is not available.');
        }

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createAccessDeniedException('You can not update this data');
        }

        $sheetTemplate = $sheet->getType()->getSheetTemplate();

        if (!isset($sheetTemplate[$block])) {
            throw new \InvalidArgumentException();
        }

        $updateBlock = new UpdateBlock($sheet, $block);
        $form        = $this->createForm(UpdateBlockType::class, $updateBlock, [
            'template' => $sheetTemplate[$block]['template'],
            'locale'   => $request->getLocale(),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('vimeet_infrastructure.vimeet.application.command.sheet.update_block_handler')->handle($updateBlock);
                $this->addFlash('success', 'flash.sheet.update_block.success');

                // Go to the sheet
                return $this->redirectToRoute('event_sheet', ['sheet' => $sheet->getId()]);
            } catch (RequiredDataEmptyException $exception) {
                foreach ($exception->getKeys() as $key) {
                    $form->get($key)->addError(new FormError('validators.field.required'));
                }
            }
        }

        return $this->render('VimeetAppBundle:Event/Sheet:updateBlock.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request     $request
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return RedirectResponse
     */
    public function deleteParticipantAction(Request $request, Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $delete = new Delete($sheet, $this->getUser(), $participant->getId());
        $form   = $this->createForm(DeleteParticipantType::class, $delete);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('vimeet_infrastructure.vimeet.application.command.participant.delete_handler')->handle($delete);
                $this->addFlash('success', 'flash.sheet.delete_participant.success');
            } catch (IsNotLinkedToSheetException $exception) {
                $this->addFlash('error', 'flash.sheet.delete_participant.access_denied');
            } catch (OwnerCanNotBeDeletedException $exception) {
                $this->addFlash('error', 'flash.sheet.delete_participant.access_denied');
            } catch (IsNotOwnerException $exception) {
                $this->addFlash('error', 'flash.sheet.delete_participant.access_denied');
            }
        }

        // Go to the sheet
        return $this->redirectToRoute('event_sheet', ['sheet' => $sheet->getId()]);
    }
}
