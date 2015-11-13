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
use Proximum\Vimeet\Application\Command\Sheet\BuyParticipant;
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
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\BuyParticipantType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\UpdateBlockType;
use Proximum\Vimeet\Domain\Model\EventView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Specification\Sheet\CanAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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

        $sheetDataView = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet')
            ->getSheetDataView($sheet, $request->getLocale());

        $participantManager = $this->get('vimeet_app.service.participant_manager');

        $buttonParticipant['canBuyParticipant'] = $participantManager->canBuyParticipant($sheet);
        $buttonParticipant['addParticipant']    = $participantManager->availableAddParticipant($sheet);

        $participantDeleteForms = $this->addDeleteParticipantForm(
            $request,
            $sheet,
            $userParticipant,
            $participantViews
        );

        return $this->render('VimeetAppBundle:Event/Sheet:index.html.twig', [
            'eventView'                => $eventView,
            'typeView'                 => $typeView,
            'sheetDataView'            => $sheetDataView,
            'participantViews'         => $participantViews,
            'participant_delete_forms' => $participantDeleteForms,
            'buttonPartipant'          => $buttonParticipant,
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

        $participantManager = $this->get('vimeet_app.service.participant_manager');

        if ($participantManager->availableAddParticipant($sheet) <= 0) {
            throw new AccessDeniedException('You can not add a new participant');
        }

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
            } catch (EmailCanNotBeNullException $exception) {
                $this->addGivenErrorOnGivenField(
                    $this->get('translator')->trans('validators.field.required', [], 'validators'),
                    $form->get('email')
                );
            } catch (ParticipantAlreadyExistException $exception) {
                $this->addGivenErrorOnGivenField(
                    $this->get('translator')->trans('event.sheet.participant.already_exists'),
                    $form->get('email')
                );
            } catch (RequiredDataEmptyException $exception) {
                $form = $this->addRequiredErrorOnForm(
                    $form,
                    $sheet->getType()->getParticipantTemplate(),
                    $add->data,
                    $form->get('data')
                );
            }
        }

        return $this->render('VimeetAppBundle:Event/Sheet:addParticipant.html.twig', [
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
                    'id'        => $sheet->getId(),
                ]);
            } catch (RequiredDataEmptyException $exception) {
                $form = $this->addRequiredErrorOnForm(
                    $form,
                    $sheet->getType()->getParticipantTemplate(),
                    $updateParticipant->data,
                    $form->get('data')
                );
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
     * @param int       $block
     *
     * @return Response
     */
    public function updateBlockAction(Request $request, EventView $eventView, Sheet $sheet, $block)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

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
                    'id'        => $sheet->getId(),
                ]);
            } catch (RequiredDataEmptyException $exception) {
                $form = $this->addRequiredErrorOnForm(
                    $form,
                    $sheet->getType()->getSheetTemplate()[$block]['template'],
                    $updateBlock->data,
                    $form->get('data')
                );
            }
        }

        return $this->render('VimeetAppBundle:Event/Sheet:updateBlock.html.twig', [
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

        $delete = new Delete($sheet, $this->getUser(), $participant->getId());
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
     * @param Request $request
     * @param Sheet   $sheet
     * @param $userParticipant
     * @param $participantViews
     *
     * @return array
     */
    private function addDeleteParticipantForm(Request$request, Sheet $sheet, $userParticipant, $participantViews)
    {
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
                            ),
                        ]
                    )->createView();
                }
            }
        }

        return $participantDeleteForms;
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return RedirectResponse|Response
     */
    public function buyParticipantAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($sheet->getType()->getMaxParticipant() <= count($sheet->getParticipants())) {
            throw new AccessDeniedException('You can not buy a new participant');
        }

        $participantManager = $this->get('vimeet_app.service.participant_manager');
        $participantPrice   = $participantManager->getParticipantPrice($sheet);
        $planningPrice      = $participantManager->getPlanningPrice($sheet);

        $buyParticipant = new BuyParticipant($sheet, $request->getLocale());
        $form           = $this->createForm(new BuyParticipantType(), $buyParticipant, [
            'template' => $sheet->getType()->getParticipantTemplate(),
            'locale'   => $request->getLocale(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this
                    ->get('vimeet_infrastructure.vimeet.application.command.sheet.buy_participant_handler')
                    ->handle($buyParticipant);

                $this->addFlash('success', 'flash.sheet.add_participant.success');

                // Go to the sheet
                return $this->redirectToRoute('event_sheet', [
                    'subdomain' => $request->attributes->get('subdomain'),
                    'id'        => $sheet->getId(),
                ]);
            } catch (EmailCanNotBeNullException $exception) {
                $this->addGivenErrorOnGivenField(
                    $this->get('translator')->trans('validators.field.required', [], 'validators'),
                    $form->get('participantData')->get('email')
                );
            } catch (ParticipantAlreadyExistException $exception) {
                $this->addGivenErrorOnGivenField(
                    $this->get('translator')->trans('event.sheet.participant.already_exists'),
                    $form->get('participantData')->get('email')
                );
            } catch (RequiredDataEmptyException $exception) {
                $form = $this->addRequiredErrorOnForm(
                    $form,
                    $sheet->getType()->getParticipantTemplate(),
                    $buyParticipant->participantData['data'],
                    $form->get('participantData')->get('data')
                );
            }
        }

        return $this->render('VimeetAppBundle:Event/Sheet:buyParticipant.html.twig', [
            'eventView'        => $eventView,
            'sheet'            => $sheet,
            'form'             => $form->createView(),
            'participantPrice' => $participantPrice,
            'planningPrice'    => $planningPrice,
        ]);
    }
}
