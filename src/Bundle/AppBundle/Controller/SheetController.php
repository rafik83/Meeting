<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

use Doctrine\ORM\PersistentCollection;
use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Participant\Update;
use Proximum\Vimeet\Application\Command\Participant\Delete;
use Proximum\Vimeet\Application\Command\Sheet\UpdateBlock;
use Proximum\Vimeet\Application\Exception\Participant\IsNotLinkedToSheetException;
use Proximum\Vimeet\Application\Exception\Participant\IsNotOwnerException;
use Proximum\Vimeet\Application\Exception\Participant\OwnerCanNotBeDeletedException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\AddParticipantType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\DeleteParticipantType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\ParticipantUpdateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\UpdateBlockType;
use Proximum\Vimeet\Domain\Model\EventView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Specification\Sheet\CanAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SheetController extends BaseController
{
    /**
     * Sheet
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return RedirectResponse|Response
     */
    public function sheetAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $sheetSpecification = new CanAccess($this->getUser());

        if (!$sheetSpecification->isSatisfiedBy($sheet)) {
            throw new AccessDeniedException('No participant for this user attached on this sheet');
        }

        $typeView = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getTypeViewById($sheet->getType()->getId(), $request->getLocale());

        $participantViews = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getParticipantViewsBySheet($sheet->getId());

        $userParticipant = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getParticipantForUserAndSheet($this->getUser(), $sheet);

        $participantDeleteForms = [];

        if ($userParticipant->isOwner()) {
            foreach ($participantViews as $participantView) {
                if (!$participantView->owner) {
                    $delete = new Delete($sheet, $this->getUser(), $participantView->id);

                    $participantDeleteForms[$participantView->id] = $this->createForm(
                        new DeleteParticipantType(),
                        $delete,
                        [
                            'action' => $this->generateUrl(
                                'event_sheet_delete_participant',
                                [
                                    'subdomain'      => $request->attributes->get('subdomain'),
                                    'id'             => $sheet->getId(),
                                    'participant_id' => $participantView->id,
                                ]
                            )
                        ]
                    )->createView();
                }
            }
        }

        return $this->render('VimeetAppBundle:Event:sheet.html.twig', [
            'eventView'                => $eventView,
            'typeView'                 => $typeView,
            'sheet'                    => $sheet,
            'participantViews'         => $participantViews,
            'participant_delete_forms' => $participantDeleteForms,
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

        $add  = new Add($sheet, $request->getLocale());
        $form = $this->createForm(new AddParticipantType(), $add, [
            'template' => $sheet->getType()->getParticipantTemplate(),
            'locale'   => $request->getLocale(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('vimeet_infrastructure.vimeet.application.command.participant.add_handler')->handle($add);
                $this->addFlash('success', 'flash.sheet.add_participant.success');

                // Go to the sheet
                return $this->redirectToRoute('event_sheet', [
                    'subdomain' => $request->attributes->get('subdomain'),
                    'id'        => $sheet->getId(),
                ]);

            } catch (ParticipantAlreadyExistException $exception) {
                $error = new FormError($this->get('translator')->trans('event.sheet.participant.already_exists'));
                $form->get('email')->addError($error);

            } catch (RequiredDataEmptyException $exception) {
                $form = $this->addRequiredErrorOnForm(
                    $form,
                    $sheet->getType()->getParticipantTemplate(),
                    $add->data
                );
            }
        }

        return $this->render('VimeetAppBundle:Event:addParticipant.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @ParamConverter(
     *   "participant",
     *   class="Proximum\Vimeet\Domain\Model\Participant",
     *   options={"id" = "participant_id"}
     * )
     *
     * @param Request     $request
     * @param EventView   $eventView
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return Response
     */
    public function updateParticipantAction(
        Request $request,
        EventView $eventView,
        Sheet $sheet,
        Participant $participant
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->getUser()->getId() !== $participant->getUser()->getId()) {
            throw new AccessDeniedException('You can not update other participant');
        }

        $updateParticipant = new Update($participant->getId(), $participant->getData());
        $form              = $this->createForm(new ParticipantUpdateType(), $updateParticipant, [
            'template' => $sheet->getType()->getParticipantTemplate(),
            'locale'   => $request->getLocale(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this
                    ->get('vimeet_infrastructure.vimeet.application.command.participant.update_handler')
                    ->handle($updateParticipant);

                $this->addFlash('success', 'flash.sheet.update_participant.success');

                // Go to the sheet
                return $this->redirectToRoute('event_sheet', [
                    'subdomain' => $request->attributes->get('subdomain'),
                    'id' => $sheet->getId(),
                ]);
            } catch (RequiredDataEmptyException $exception) {
                $form = $this->addRequiredErrorOnForm(
                    $form,
                    $sheet->getType()->getParticipantTemplate(),
                    $updateParticipant->data
                );
            }
        }

        return $this->render('VimeetAppBundle:Event:updateParticipant.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param integer   $block
     *
     * @return Response
     */
    public function updateBlockAction(Request $request, EventView $eventView, Sheet $sheet, $block)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->isParticipant($sheet->getParticipants());

        $sheetTemplate = $sheet->getType()->getSheetTemplate();

        if (!isset($sheetTemplate[$block])) {
            throw new \InvalidArgumentException();
        }

        $updateBlock = new UpdateBlock($sheet, $block);
        $form        = $this->createForm(new UpdateBlockType(), $updateBlock, [
            'template' => $sheetTemplate[$block]['template'],
            'locale'   => $request->getLocale(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this
                    ->get('vimeet_infrastructure.vimeet.application.command.sheet.update_block_handler')
                    ->handle($updateBlock);

                $this->addFlash('success', 'flash.sheet.update_block.success');

                // Go to the sheet
                return $this->redirectToRoute('event_sheet', [
                    'subdomain' => $request->attributes->get('subdomain'),
                    'id' => $sheet->getId(),
                ]);
            } catch (RequiredDataEmptyException $exception) {
                $form = $this->addRequiredErrorOnForm(
                    $form,
                    $sheet->getType()->getSheetTemplate()[$block]['template'],
                    $updateBlock->data
                );
            }
        }

        return $this->render('VimeetAppBundle:Event:updateBlock.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @ParamConverter(
     *   "participant",
     *   class="Proximum\Vimeet\Domain\Model\Participant",
     *   options={"id" = "participant_id"}
     * )
     *
     * @param Request     $request
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return RedirectResponse
     */
    public function deleteParticipantAction(Request $request, Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $delete = new Delete($sheet, $this->getUser(), $participant);
        $form   = $this->createForm(new DeleteParticipantType(), $delete);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this
                    ->get('vimeet_infrastructure.vimeet.application.command.participant.delete_handler')
                    ->handle($delete);
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
        return $this->redirectToRoute('event_sheet', [
            'subdomain' => $request->attributes->get('subdomain'),
            'id'        => $sheet->getId(),
        ]);
    }

    /**
     * @param PersistentCollection $participants
     *
     * @throws AccessDeniedException
     */
    private function isParticipant(PersistentCollection $participants)
    {
        $isUserParticipant = false;

        foreach ($participants as $participant) {
            if ($this->getUser() === $participant->getUser()) {
                $isUserParticipant = true;
            }
        }

        if (!$isUserParticipant) {
            throw new AccessDeniedException('You can not update this data');
        }
    }
}
