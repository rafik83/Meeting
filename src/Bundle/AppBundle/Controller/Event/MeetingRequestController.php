<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Application\Command\Meeting\ApproveRequest;
use Proximum\Vimeet\Application\Command\Meeting\CancelRequest;
use Proximum\Vimeet\Application\Command\Meeting\CreateRequest;
use Proximum\Vimeet\Application\Command\Meeting\RefuseRequest;
use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequestFrom;
use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequestTo;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\MeetingRequestApproveType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\MeetingRequestCancelType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\MeetingRequestCreateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\MeetingRequestRefuseType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\MeetingRequestUpdateFromType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\MeetingRequestUpdateToType;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\CategoryView;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Domain\View\Meeting\ShowDetailsView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MeetingRequestController extends BaseController
{
    /**
     * List meeting requests the sheet sent
     *
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function listRequestAction(EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        $meetingRequest = $this->get('vimeet_infrastructure.repository.meeting.request_repository')->getRequestSentBySheet($sheet);
        $requestViews   = $this->get('vimeet_infrastructure.application.components.meeting.request_views_builder')->generate($meetingRequest, $this->getUser(), $sheet);

        return $this->render('VimeetAppBundle:Event/MeetingRequest:listRequest.html.twig', [
            'eventView'     => $eventView,
            'sheet'         => $sheet,
            'request_views' => $requestViews,
        ]);
    }

    /**
     * List meeting requests the sheet received
     *
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function listPropositionAction(EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        $meetingProposition = $this->get('vimeet_infrastructure.repository.meeting.request_repository')->getPropositionReceivedBySheet($sheet);
        $propositionViews   = $this->get('vimeet_infrastructure.application.components.meeting.request_views_builder')->generate($meetingProposition, $this->getUser(), $sheet);

        return $this->render('VimeetAppBundle:Event/MeetingRequest:listProposition.html.twig', [
            'eventView'         => $eventView,
            'sheet'             => $sheet,
            'proposition_views' => $propositionViews,
        ]);
    }

    /**
     * Create a meeting request between two sheet
     *
     * @param Request      $request
     * @param EventView    $eventView
     * @param CategoryView $categoryView
     * @param Sheet        $to
     * @param Sheet        $from
     *
     * @return RedirectResponse|Response
     */
    public function createRequestAction(Request $request, EventView $eventView, CategoryView $categoryView, Sheet $to, Sheet $from)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToCreate($this->getUser(), $from, $to)) {
            throw $this->createAccessDeniedException('You are not allowed to create this meeting request.');
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        $createRequest = new CreateRequest($from, $to, new \DateTime(), $this->getUser());
        $form          = $this->createForm(MeetingRequestCreateType::class, $createRequest, ['sheet' => $from]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.meeting.create_request_handler')->handle($createRequest);
            $this->addFlash('success', 'flash.meeting_request.create.success');

            return $this->redirectToRoute('event_catalog_category', ['categoryView' => $categoryView->id]);
        }

        return $this->render('VimeetAppBundle:Event/MeetingRequest:createRequest.html.twig', [
            'eventView' => $eventView,
            'fromName'  => $sheetInfoGuesser->guessSheetInfo($from),
            'toName'    => $sheetInfoGuesser->guessSheetInfo($to),
            'form'      => $form->createView(),
        ]);
    }

    /**
     * Approve a meeting request
     *
     * @param Request        $request
     * @param EventView      $eventView
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function approveRequestAction(Request $request, EventView $eventView, Sheet $sheet, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToApprove($this->getUser(), $meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to approve this meeting request.');
        }

        $approveRequest = new ApproveRequest($meetingRequest, new \DateTime());
        $form           = $this->createForm(MeetingRequestApproveType::class, $approveRequest, ['sheet' => $sheet]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.meeting.approve_request_handler')->handle($approveRequest);
            $this->addFlash('success', 'flash.meeting_request.approved.success');

            return $this->redirectToRoute('event_meeting_list_proposition', ['sheet' => $sheet->getId()]);
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        return $this->render('VimeetAppBundle:Event/MeetingRequest:approvedRequest.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'fromName'  => $sheetInfoGuesser->guessSheetInfo($meetingRequest->getFromSheet()),
            'toName'    => $sheetInfoGuesser->guessSheetInfo($meetingRequest->getToSheet()),
            'form'      => $form->createView(),
        ]);
    }

    /**
     * Refuse a meeting request
     *
     * @param Request        $request
     * @param EventView      $eventView
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function refuseRequestAction(Request $request, EventView $eventView, Sheet $sheet, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToRefuse($this->getUser(), $meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to refuse this meeting request.');
        }

        $refuseRequest = new RefuseRequest($meetingRequest, $this->getUser(), new \DateTime());
        $form          = $this->createForm(MeetingRequestRefuseType::class, $refuseRequest);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.meeting.refuse_request_handler')->handle($refuseRequest);
            $this->addFlash('success', 'flash.meeting_request.refused.success');

            return $this->redirectToRoute('event_meeting_list_proposition', ['sheet' => $sheet->getId()]);
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        return $this->render('VimeetAppBundle:Event/MeetingRequest:refusedRequest.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'fromName'  => $sheetInfoGuesser->guessSheetInfo($meetingRequest->getFromSheet()),
            'toName'    => $sheetInfoGuesser->guessSheetInfo($meetingRequest->getToSheet()),
            'form'      => $form->createView(),
        ]);
    }

    /**
     * Display a meeting request
     *
     * @param EventView      $eventView
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return Response
     */
    public function showRequestAction(EventView $eventView, Sheet $sheet, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToSee($this->getUser(), $meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to see this meeting request.');
        }

        if ($meetingRequest->getFromSheet() === $sheet) {
            $participants = $meetingRequest->getFromParticipants()->toArray();
        } elseif ($meetingRequest->getToSheet() === $sheet) {
            $participants = $meetingRequest->getToParticipants()->toArray();
        } else {
            throw $this->createAccessDeniedException('You are not allowed to see this meeting request.');
        }

        $messageRepository = $this->get('vimeet_infrastructure.repository.meeting.message_repository');
        $permissionManager = $this->get('meeting.request_permission_manager');
        $sheetInfoGuesser  = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        $meetingRequestView = new ShowDetailsView(
            $meetingRequest->getId(),
            $meetingRequest->getToSheet()->getId(),
            $sheetInfoGuesser->guessSheetInfo($meetingRequest->getToSheet()),
            $meetingRequest->getFromSheet()->getId(),
            $sheetInfoGuesser->guessSheetInfo($meetingRequest->getFromSheet()),
            array_map(function (Participant $participant) {
                return $this
                    ->get('vimeet_infrastructure.application.components.sheet.participant_info_guesser')
                    ->guessParticipantInfo($participant);
            }, $participants),
            $messageRepository->getMessagesByMeetingRequest($meetingRequest),
            $meetingRequest->getState()
        );

        return $this->render('VimeetAppBundle:Event/MeetingRequest:showRequest.html.twig', [
            'eventView'          => $eventView,
            'sheet'              => $sheet,
            'meetingRequestView' => $meetingRequestView,
            'canEdit'            => $permissionManager->isAllowedToEdit($this->getUser(), $meetingRequest, $sheet),
            'canCancel'          => $permissionManager->isAllowedToCancel($this->getUser(), $meetingRequest, $sheet),
            'canRefuse'          => $permissionManager->isAllowedToRefuse($this->getUser(), $meetingRequest, $sheet),
            'canApprove'         => $permissionManager->isAllowedToApprove($this->getUser(), $meetingRequest, $sheet),
        ]);
    }

    /**
     * Cancel a meeting request
     *
     * @param Request        $request
     * @param EventView      $eventView
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function cancelRequestAction(Request $request, EventView $eventView, Sheet $sheet, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToCancel($this->getUser(), $meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to cancel this meeting request.');
        }

        $cancelRequest = new CancelRequest($meetingRequest, $this->getUser(), new \DateTime(), $sheet);
        $form          = $this->createForm(MeetingRequestCancelType::class, $cancelRequest);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.meeting.cancel_request_handler')->handle($cancelRequest);
            $this->addFlash('success', 'flash.meeting_request.cancelled.success');

            return $this->redirectToRoute('event_meeting_list_request', ['sheet' => $sheet->getId()]);
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        return $this->render('VimeetAppBundle:Event/MeetingRequest:cancelRequest.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'fromName'  => $sheetInfoGuesser->guessSheetInfo($meetingRequest->getFromSheet()),
            'toName'    => $sheetInfoGuesser->guessSheetInfo($meetingRequest->getToSheet()),
            'form'      => $form->createView(),
        ]);
    }

    /**
     * Edit a meeeting request
     *
     * @param Request        $request
     * @param EventView      $eventView
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function editRequestAction(Request $request, EventView $eventView, Sheet $sheet, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToEdit($this->getUser(), $meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to edit this meeting request.');
        }

        if ($meetingRequest->getFromSheet() === $sheet) {
            $command = new UpdateRequestFrom($meetingRequest, new \DateTime(), $this->getUser());
            $form    = $this->createForm(MeetingRequestUpdateFromType::class, $command, ['sheet' => $sheet]);
            $handler = $this->get('vimeet_infrastructure.vimeet.application.command.meeting.update_request_from_handler');
        } elseif ($meetingRequest->getToSheet() === $sheet) {
            $command = new UpdateRequestTo($meetingRequest, new \DateTime(), $this->getUser());
            $form    = $this->createForm(MeetingRequestUpdateToType::class, $command, ['sheet' => $sheet]);
            $handler = $this->get('vimeet_infrastructure.vimeet.application.command.meeting.update_request_to_handler');
        } else {
            throw $this->createAccessDeniedException('You are not allowed to edit this meeting request.');
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $handler->handle($command);
            $this->addFlash('success', 'flash.meeting_request.edit.success');

            return $this->redirectToRoute('event_meeting_list_request', ['sheet' => $sheet->getId()]);
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        return $this->render('VimeetAppBundle:Event/MeetingRequest:editRequest.html.twig', [
            'eventView' => $eventView,
            'fromName'  => $sheetInfoGuesser->guessSheetInfo($meetingRequest->getFromSheet()),
            'toName'    => $sheetInfoGuesser->guessSheetInfo($meetingRequest->getToSheet()),
            'form'      => $form->createView(),
            'sheet'     => $sheet,
        ]);
    }
}
