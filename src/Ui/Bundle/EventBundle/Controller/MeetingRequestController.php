<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Meeting\ApproveRequest;
use Proximum\Vimeet\Application\Command\Meeting\CancelRequest;
use Proximum\Vimeet\Application\Command\Meeting\CreateRequest;
use Proximum\Vimeet\Application\Command\Meeting\RefuseRequest;
use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequestFrom;
use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequestTo;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestListViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\StateListViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\StatusListViewQuery;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestListView;
use Proximum\Vimeet\Application\View\Meeting\StateListsView;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\Meeting\ShowDetailsView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestApproveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestCancelType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestCreateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestRefuseType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestUpdateFromType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestUpdateToType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\SearchType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MeetingRequestController extends Controller
{
    /**
     * List meeting requests the sheet sent
     *
     * @param Request   $request
     * @param EventDomain $eventDomain
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function listRequestAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createAccessDeniedException('You can not update this data');
        }

        $filters    = [];
        $searchForm = $this->createForm(SearchType::class, $filters, [
            'label' => null,
            'action'=> $this->generateUrl('event_meeting_list_request', [
                'sheet' => $sheet->getId()
            ]),
        ]);

        $searchForm->handleRequest($request);

        if ($searchForm->isValid()) {
            $filters = $searchForm->getData();
        }

        $query       = new MeetingRequestListViewQuery($sheet, $request->getLocale(), $filters);
        $statusQuery = new StateListViewQuery($sheet);

        /** @var MeetingRequestListView $meetingRequestListView */
        $meetingRequestListView = $this->get('tactician.commandbus.query')->handle($query);

        /** @var StateListsView $stateListsView */
        $stateListsView         = $this->get('tactician.commandbus.query')->handle($statusQuery);

        $template = 'EventBundle:MeetingRequest:listRequest.html.twig';

        if ($request->isXmlHttpRequest()) {
            $template = 'EventBundle:MeetingRequest/Partials:catalog.html.twig';
        }

        return $this->render($template, [
            'event'              => $eventDomain->getEvent(),
            'sheet'              => $sheet,
            'meetingRequestView' => $meetingRequestListView,
            'stateListsView'     => $stateListsView,
            'searchForm'         => $searchForm->createView(),
            'isCatalog'          => true, // set menu link visible,
            'isMeeting'          => true,
            'resultsCount'       => count($meetingRequestListView->getMeetingRequestsView()),
        ]);
    }

    /**
     * List meeting requests the sheet received
     *
     * @param Request   $request
     * @param EventDomain $eventDomain
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function listPropositionAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createAccessDeniedException('You can not update this data');
        }

        $meetingProposition = $this->get('vimeet_infrastructure.repository.meeting.request_repository')
            ->getPropositionReceivedBySheet($sheet);

        $propositionViews   = $this->get('vimeet_infrastructure.application.components.meeting.request_views_builder')
            ->generate($meetingProposition, $this->getUser(), $sheet, $request->getLocale());

        return $this->render('EventBundle:MeetingRequest:listProposition.html.twig', [
            'event'             => $eventDomain->getEvent(),
            'sheet'             => $sheet,
            'proposition_views' => $propositionViews,
        ]);
    }

    /**
     * Create a meeting request between two sheet
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return RedirectResponse|Response
     */
    public function createRequestAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $event = $eventDomain->getEvent();

        if (!$sheet->isInCatalog()) {
            throw $this->createAccessDeniedException('Sheet not in catalog');
        }

        if (!$this->get('domain.key_dates.checker.catalog_access_checker')->allowedToAccess($event)) {
            throw $this->createNotFoundException();
        }

        $from = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $request->getLocale());

        if (!$from->isInCatalog()) {
            throw $this->createAccessDeniedException('Sheet not in catalog');
        }

        if (!$this->get('meeting.request_permission_manager')->isAllowedToCreate($this->getUser(), $from, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to create this meeting request.');
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        $createRequest = new CreateRequest($from, $sheet, new \DateTime(), $this->getUser());
        $form          = $this->createForm(MeetingRequestCreateType::class, $createRequest, [
            'sheet'  => $from,
            'locale' => $request->getLocale(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($createRequest);
            $this->addFlash('success', 'flash.meeting_request.create.success');

            return $this->redirectToRoute('event_catalog_complete_sheet', ['sheet' => $sheet->getId()]);
        }

        return $this->render('EventBundle:MeetingRequest:createRequest.html.twig', [
            'event'    => $eventDomain->getEvent(),
            'fromName' => $sheetInfoGuesser->guessSheetName($from, $request->getLocale()),
            'toName'   => $sheetInfoGuesser->guessSheetName($sheet, $request->getLocale()),
            'form'     => $form->createView(),
        ]);
    }

    /**
     * Approve a meeting request
     *
     * @param Request        $request
     * @param EventDomain      $eventDomain
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function approveRequestAction(Request $request, EventDomain $eventDomain, Sheet $sheet, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToApprove($this->getUser(), $meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to approve this meeting request.');
        }

        $approveRequest = new ApproveRequest($meetingRequest, new \DateTime());
        $form           = $this->createForm(MeetingRequestApproveType::class, $approveRequest, [
            'sheet'  => $sheet,
            'locale' => $request->getLocale(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($approveRequest);
            $this->addFlash('success', 'flash.meeting_request.approved.success');

            return $this->redirectToRoute('event_meeting_list_proposition', ['sheet' => $sheet->getId()]);
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        return $this->render('EventBundle:MeetingRequest:approvedRequest.html.twig', [
            'event'    => $eventDomain->getEvent(),
            'sheet'    => $sheet,
            'fromName' => $sheetInfoGuesser->guessSheetName($meetingRequest->getFromSheet(), $request->getLocale()),
            'toName'   => $sheetInfoGuesser->guessSheetName($meetingRequest->getToSheet(), $request->getLocale()),
            'form'     => $form->createView(),
        ]);
    }

    /**
     * Refuse a meeting request
     *
     * @param Request        $request
     * @param EventDomain      $eventDomain
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function refuseRequestAction(Request $request, EventDomain $eventDomain, Sheet $sheet, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToRefuse($this->getUser(), $meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to refuse this meeting request.');
        }

        $refuseRequest = new RefuseRequest($meetingRequest, $this->getUser(), new \DateTime());
        $form          = $this->createForm(MeetingRequestRefuseType::class, $refuseRequest);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($refuseRequest);
            $this->addFlash('success', 'flash.meeting_request.refused.success');

            return $this->redirectToRoute('event_meeting_list_proposition', ['sheet' => $sheet->getId()]);
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        return $this->render('EventBundle:MeetingRequest:refusedRequest.html.twig', [
            'event'    => $eventDomain->getEvent(),
            'sheet'    => $sheet,
            'fromName' => $sheetInfoGuesser->guessSheetName($meetingRequest->getFromSheet(), $request->getLocale()),
            'toName'   => $sheetInfoGuesser->guessSheetName($meetingRequest->getToSheet(), $request->getLocale()),
            'form'     => $form->createView(),
        ]);
    }

    /**
     * Display a meeting request
     *
     * @param Request        $request
     * @param EventDomain      $eventDomain
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return Response
     */
    public function showRequestAction(Request $request, EventDomain $eventDomain, Sheet $sheet, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $locale = $request->getLocale();

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
            $sheetInfoGuesser->guessSheetName($meetingRequest->getToSheet(), $request->getLocale()),
            $meetingRequest->getFromSheet()->getId(),
            $sheetInfoGuesser->guessSheetName($meetingRequest->getFromSheet(), $request->getLocale()),
            array_map(
                function (Participant $participant) use ($locale) {
                    return $this
                        ->get('template.participant_info_guesser')
                        ->guessParticipantCompleteName($participant, $locale);
                },
                $participants
            ),
            $messageRepository->getMessagesByMeetingRequest($meetingRequest),
            $meetingRequest->getState()
        );

        return $this->render('EventBundle:MeetingRequest:showRequest.html.twig', [
            'event'              => $eventDomain->getEvent(),
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
     * @param EventDomain      $eventDomain
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function cancelRequestAction(Request $request, EventDomain $eventDomain, Sheet $sheet, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToCancel($this->getUser(), $meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to cancel this meeting request.');
        }

        $cancelRequest = new CancelRequest($meetingRequest, $this->getUser(), new \DateTime(), $sheet);
        $form          = $this->createForm(MeetingRequestCancelType::class, $cancelRequest);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($cancelRequest);
            $this->addFlash('success', 'flash.meeting_request.cancelled.success');

            return $this->redirectToRoute('event_meeting_list_request', ['sheet' => $sheet->getId()]);
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        return $this->render('EventBundle:MeetingRequest:cancelRequest.html.twig', [
            'event'    => $eventDomain->getEvent(),
            'sheet'    => $sheet,
            'fromName' => $sheetInfoGuesser->guessSheetName($meetingRequest->getFromSheet(), $request->getLocale()),
            'toName'   => $sheetInfoGuesser->guessSheetName($meetingRequest->getToSheet(), $request->getLocale()),
            'form'     => $form->createView(),
        ]);
    }

    /**
     * Edit a meeeting request
     *
     * @param Request        $request
     * @param EventDomain    $eventDomain
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function editRequestAction(Request $request, EventDomain $eventDomain, Sheet $sheet, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToEdit($this->getUser(), $meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to edit this meeting request.');
        }

        if ($meetingRequest->getFromSheet() === $sheet) {
            $command = new UpdateRequestFrom($meetingRequest, new \DateTime(), $this->getUser());
            $form    = $this->createForm(MeetingRequestUpdateFromType::class, $command, [
                'sheet'  => $sheet,
                'locale' => $request->getLocale()
            ]);
        } elseif ($meetingRequest->getToSheet() === $sheet) {
            $command = new UpdateRequestTo($meetingRequest, new \DateTime(), $this->getUser());
            $form    = $this->createForm(MeetingRequestUpdateToType::class, $command, [
                'sheet'  => $sheet,
                'locale' => $request->getLocale()
            ]);
        } else {
            throw $this->createAccessDeniedException('You are not allowed to edit this meeting request.');
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', 'flash.meeting_request.edit.success');

            return $this->redirectToRoute('event_meeting_list_request', ['sheet' => $sheet->getId()]);
        }

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        return $this->render('EventBundle:MeetingRequest:editRequest.html.twig', [
            'event'    => $eventDomain->getEvent(),
            'fromName' => $sheetInfoGuesser->guessSheetName($meetingRequest->getFromSheet(), $request->getLocale()),
            'toName'   => $sheetInfoGuesser->guessSheetName($meetingRequest->getToSheet(), $request->getLocale()),
            'form'     => $form->createView(),
            'sheet'    => $sheet,
        ]);
    }
}
