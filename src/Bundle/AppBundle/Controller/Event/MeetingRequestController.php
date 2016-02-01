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
use Proximum\Vimeet\Application\Command\MeetingRequest\EditRequest;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\MeetingRequestApproveType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\MeetingRequestCreateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\MeetingRequestEditType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\MeetingRequestRefuseType;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\CategoryView;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Domain\View\Meeting\ShowDetailsView;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\MeetingRequestCancelType;

class MeetingRequestController extends BaseController
{
    /**
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function listRequestAction(EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        $meetingRequestRepository = $this->get('vimeet_infrastructure.repository.meeting.request_repository');
        $meetingRequest           = $meetingRequestRepository->getRequestSentBySheet($sheet);

        $requestViews = $this
            ->get('vimeet_infrastructure.application.components.meeting.request_views_builder')
            ->generate($meetingRequest);

        return $this->render('VimeetAppBundle:Event/MeetingRequest:listRequest.html.twig', [
            'eventView'     => $eventView,
            'sheet'         => $sheet,
            'request_views' => $requestViews,
        ]);
    }

    /**
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function listPropositionAction(EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        $meetingRequestRepository = $this->get('vimeet_infrastructure.repository.meeting.request_repository');
        $meetingProposition       = $meetingRequestRepository->getPropositionReceivedBySheet($sheet);

        $propositionViews = $this
            ->get('vimeet_infrastructure.application.components.meeting.request_views_builder')
            ->generate($meetingProposition);

        return $this->render('VimeetAppBundle:Event/MeetingRequest:listProposition.html.twig', [
            'eventView'         => $eventView,
            'sheet'             => $sheet,
            'proposition_views' => $propositionViews,
        ]);
    }

    /**
     * @param Request      $request
     * @param EventView    $eventView
     * @param CategoryView $categoryView
     * @param Sheet        $to
     * @param Sheet        $from
     *
     * @return RedirectResponse|Response
     */
    public function createRequestAction(
        Request $request,
        EventView $eventView,
        CategoryView $categoryView,
        Sheet $to,
        Sheet $from
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToCreate($this->getUser(), $from, $to)) {
            throw $this->createAccessDeniedException('You are not allowed to create this meeting request.');
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        $createRequest = new CreateRequest($from, $to, new \DateTime(), $this->getUser());
        $form          = $this->createForm(MeetingRequestCreateType::class, $createRequest, ['sheet' => $from]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.meeting.create_request_handler')
                ->handle($createRequest);

            $this->addFlash('success', 'flash.meeting_request.create.success');

            return $this->redirectToRoute('event_catalog_category', ['categoryView' => $categoryView->id]);
        }
        $fromName = $sheetInfoGuesser->guessSheetInfo($from);
        $toName   = $sheetInfoGuesser->guessSheetInfo($to);

        return $this->render('VimeetAppBundle:Event/MeetingRequest:createRequest.html.twig', [
            'eventView' => $eventView,
            'fromName'  => $fromName,
            'toName'    => $toName,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request        $request
     * @param EventView      $eventView
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function approveRequestAction(Request $request, EventView $eventView, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToApprove($this->getUser(), $meetingRequest)) {
            throw $this->createAccessDeniedException('You are not allowed to approve this meeting request.');
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        $approveRequest = new ApproveRequest($meetingRequest);
        $form           = $this->createForm(MeetingRequestApproveType::class, $approveRequest, [
            'sheet' => $meetingRequest->getUserSheet($this->getUser()),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.meeting.approve_request_handler')
                ->handle($approveRequest);

            $this->addFlash('success', 'flash.meeting_request.approved.success');

            return $this->redirectToRoute('event_meeting_list_proposition', [
                'sheet' => $meetingRequest->getUserSheet($this->getUser())
            ]);
        }

        return $this->render('VimeetAppBundle:Event/MeetingRequest:approvedRequest.html.twig', [
            'eventView' => $eventView,
            'fromName'  => $sheetInfoGuesser->guessSheetInfo($meetingRequest->getFromSheet()),
            'toName'    => $sheetInfoGuesser->guessSheetInfo($meetingRequest->getToSheet()),
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request        $request
     * @param EventView      $eventView
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function refuseRequestAction(Request $request, EventView $eventView, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToRefuse($this->getUser(), $meetingRequest)) {
            throw $this->createAccessDeniedException('You are not allowed to refuse this meeting request.');
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        $refuseRequest = new RefuseRequest($meetingRequest, $this->getUser());
        $form          = $this->createForm(MeetingRequestRefuseType::class, $refuseRequest);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.meeting.refuse_request_handler')
                ->handle($refuseRequest);

            $this->addFlash('success', 'flash.meeting_request.refused.success');

            return $this->redirectToRoute('event_meeting_list_proposition', [
                'sheet' => $meetingRequest->getUserSheet($this->getUser())
            ]);
        }
        $fromName = $sheetInfoGuesser->guessSheetInfo($meetingRequest->getFromSheet());
        $toName   = $sheetInfoGuesser->guessSheetInfo($meetingRequest->getToSheet());

        return $this->render('VimeetAppBundle:Event/MeetingRequest:refusedRequest.html.twig', [
            'eventView' => $eventView,
            'fromName'  => $fromName,
            'toName'    => $toName,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param EventView      $eventView
     * @param MeetingRequest $meetingRequest
     *
     * @return Response
     */
    public function showRequestAction(EventView $eventView, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToSee($this->getUser(), $meetingRequest)) {
            throw $this->createAccessDeniedException('You are not allowed to see this meeting request.');
        }

        $canEdit    = $this->get('meeting.request_permission_manager')->isAllowedToEdit($this->getUser(), $meetingRequest);
        $canCancel  = $this->get('meeting.request_permission_manager')->isAllowedToCancel($this->getUser(), $meetingRequest);
        $canRefuse  = $this->get('meeting.request_permission_manager')->isAllowedToRefuse($this->getUser(), $meetingRequest);
        $canApprove = $this->get('meeting.request_permission_manager')->isAllowedToApprove($this->getUser(), $meetingRequest);

        $participants = $meetingRequest->getUserParticpants($this->getUser());

        // Get messages
        $messages = $this
            ->get('vimeet_infrastructure.repository.meeting.message_repository')
            ->getMessagesByMeetingRequest($meetingRequest);

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        $meetingRequestView = new ShowDetailsView(
            $meetingRequest->getId(),
            $meetingRequest->getToSheet()->getId(),
            $sheetInfoGuesser->guessSheetInfo($meetingRequest->getToSheet()),
            $meetingRequest->getFromSheet()->getId(),
            $sheetInfoGuesser->guessSheetInfo($meetingRequest->getFromSheet()),
            array_map(function (Participant $participant) {
                return $this->get('vimeet_infrastructure.application.components.sheet.participant_info_guesser')->guessParticipantInfo($participant);
            }, $participants),
            $messages,
            $meetingRequest->getState()
        );

        return $this->render('VimeetAppBundle:Event/MeetingRequest:showRequest.html.twig', [
            'eventView'          => $eventView,
            'meetingRequestView' => $meetingRequestView,
            'canEdit'            => $canEdit,
            'canCancel'          => $canCancel,
            'canRefuse'          => $canRefuse,
            'canApprove'         => $canApprove,
        ]);
    }

    /**
     * @param Request $request
     * @param EventView $eventView
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function cancelRequestAction(Request $request, EventView $eventView, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToCancel($this->getUser(), $meetingRequest)) {
            throw $this->createAccessDeniedException('You are not allowed to cancel this meeting request.');
        }

        $cancelRequest = new CancelRequest($meetingRequest, $this->getUser());
        $form          = $this->createForm(MeetingRequestCancelType::class, $cancelRequest);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.meeting.cancel_request_handler')
                ->handle($cancelRequest);

            $this->addFlash('success', 'flash.meeting_request.cancelled.success');

            return $this->redirectToRoute('event_meeting_list_request', [
                'sheet' => $meetingRequest->getUserSheet($this->getUser())
            ]);
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');
        $fromName = $sheetInfoGuesser->guessSheetInfo($meetingRequest->getFromSheet());
        $toName   = $sheetInfoGuesser->guessSheetInfo($meetingRequest->getToSheet());

        return $this->render('VimeetAppBundle:Event/MeetingRequest:cancelRequest.html.twig', [
            'eventView' => $eventView,
            'fromName'  => $fromName,
            'toName'    => $toName,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request        $request
     * @param EventView      $eventView
     * @param MeetingRequest $meetingRequest
     * @param Sheet          $from
     *
     * @return RedirectResponse|Response
     */
    public function editRequestAction(
        Request $request,
        EventView $eventView,
        MeetingRequest $meetingRequest,
        Sheet $from
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToEdit($this->getUser(), $meetingRequest)) {
            throw $this->createAccessDeniedException('You are not allowed to edit this meeting request.');
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        $editRequest = new EditRequest($meetingRequest, new \DateTime(), $this->getUser());
        $form        = $this->createForm(MeetingRequestEditType::class, $editRequest, [
            'sheet' => $from,
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.meeting.edit_request_handler')->handle($editRequest);

            $this->addFlash('success', 'flash.meeting_request.edit.success');

            return $this->redirectToRoute('event_meeting_list_request', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $from->getId()
            ]);
        }
        $fromName = $sheetInfoGuesser->guessSheetInfo($from);
        $toName   = $sheetInfoGuesser->guessSheetInfo($meetingRequest->getToSheet());

        return $this->render('VimeetAppBundle:Event/MeetingRequest:editRequest.html.twig', [
            'eventView' => $eventView,
            'fromName'  => $fromName,
            'toName'    => $toName,
            'form'      => $form->createView(),
            'sheet'     => $form
        ]);
    }
}
