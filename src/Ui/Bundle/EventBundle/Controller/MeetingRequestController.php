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
use Proximum\Vimeet\Application\Command\Meeting\UnRefuseMeetingRequest;
use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequest;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestListViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\Message\DiscussionMeetingRequestViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\StateListViewQuery;
use Proximum\Vimeet\Application\Query\Type\MeetingTypeViewQuery;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestListView;
use Proximum\Vimeet\Application\View\Meeting\Message\DiscussionMeetingRequestView;
use Proximum\Vimeet\Application\View\Meeting\StateListsView;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\Meeting\ShowDetailsView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestApproveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestCancelType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestCreateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestRefuseType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestUpdateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\SearchType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\UnRefuseMeetingRequestType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        $typeViews = $this->get('tactician.commandbus.query')->handle(new MeetingTypeViewQuery(
            $sheet, $request->getLocale()
        ));

        $defaults   = SearchType::getDefaultFilters($typeViews);
        $searchForm = $this->createSearchForm($sheet, $defaults, SearchType::transformTypeViews($typeViews));

        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()) {
            $filters = array_merge($defaults, array_filter(
                $searchForm->getData(), function ($data) {
                    return !empty($data);
                })
            );

            $searchForm = $this->createSearchForm($sheet, $filters, SearchType::transformTypeViews($typeViews));
        } else {
            $filters = $defaults;
        }

        $query       = new MeetingRequestListViewQuery($sheet, $request->getLocale(), $filters);
        $statusQuery = new StateListViewQuery($sheet, $filters);

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
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param null        $from
     *
     * @throws \Exception
     */
    private function authorizeToCreateRequest(Request $request, EventDomain $eventDomain, Sheet $sheet, &$from)
    {
        $event = $eventDomain->getEvent();

        if (!$sheet->isInCatalog()) {
            throw $this->createNotFoundException('Sheet not in catalog');
        }

        if (!$this->get('domain.key_dates.checker.catalog_access_checker')->allowedToAccess($event)) {
            throw $this->createNotFoundException();
        }

        $from = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $request->getLocale());

        if (!$from->isInCatalog()) {
            throw $this->createNotFoundException('Viewer Sheet not in catalog');
        }

        $visibleTypes = $this->get('catalog.visible_participation_types')->getAllowedTypesList($from);

        if (!in_array($sheet->getType(), $visibleTypes)) {
            throw $this->createNotFoundException('The viewer is not allowed to create a meeting request with this sheet');
        }

        if ($from === $sheet) {
            throw $this->createNotFoundException('You can not request a meeting with yourself');
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not allowed method');
        }

        if (null !== $this
                ->get('vimeet_infrastructure.repository.meeting.request_repository')
                ->getRequestBetweenSheets($sheet, $from)
        ) {
            throw $this->createNotFoundException('You can not request a meeting as there is already one');
        }
    }

    /**
     * Create a meeting request between two sheet
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response|JsonResponse
     */
    public function createRequestAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $from = null;
        $this->authorizeToCreateRequest($request, $eventDomain, $sheet, $from);

        $createRequest = new CreateRequest($from, $sheet, $this->getUser());
        $form          = $this->createForm(MeetingRequestCreateType::class, $createRequest, [
            'action' => $this->generateUrl('event_catalog_sheet_meeting_request', ['sheet' => $sheet->getId()]),
            'sheet'  => $from,
            'locale' => $request->getLocale(),
        ]);

        $isSubmitted = $form->handleRequest($request)->isSubmitted();
        if ($isSubmitted && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($createRequest);

            return $this->createJsonResponse(
                true,
                true,
                $this->renderView('EventBundle:MeetingRequest\Button:pendingRequestButton.html.twig', [
                    'meetingRequest' => $result->meetingRequest,
                ])
            );
        } elseif ($isSubmitted && !$form->isValid()) {

            return $this->createJsonResponse(
                false,
                false,
                $this->renderView('EventBundle:MeetingRequest:createRequest.html.twig', [
                    'form' => $form->createView(),
                ])
            );
        }

        return $this->render('EventBundle:MeetingRequest:createRequest.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Approve a meeting request
     *
     * @param Request        $request
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function approveRequestAction(
        Request $request,
        Sheet $sheet,
        MeetingRequest $meetingRequest
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToApprove($this->getUser(), $meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to approve this meeting request.');
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not allowed method');
        }

        /** @var DiscussionMeetingRequestView $discussion */
        $discussion = $this
            ->get('tactician.commandbus.query')
            ->handle(new DiscussionMeetingRequestViewQuery($meetingRequest, $request->getLocale()));

        $approveRequest = new ApproveRequest($meetingRequest);
        $form           = $this->createForm(MeetingRequestApproveType::class, $approveRequest, [
            'action' => $this->generateUrl('event_meeting_request_approve', [
                'sheet'          => $sheet->getId(),
                'meetingRequest' => $meetingRequest->getId()
            ]),
            'locale' => $request->getLocale(),
            'sheet'  => $sheet,
        ]);

        $isSubmitted = $form->handleRequest($request)->isSubmitted();

        if ($isSubmitted && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($approveRequest);

            return $this->createJsonResponse(
                true,
                true,
                $this->renderView('EventBundle:MeetingRequest\Button:approvedProposition.html.twig', [
                    'meetingRequest' => $meetingRequest
                ])
            );
        } elseif ($isSubmitted && !$form->isValid()) {
            return $this->createJsonResponse(
                false,
                false,
                $this->renderView('EventBundle:MeetingRequest:approvedRequest.html.twig', [
                    'discussion' => $discussion,
                    'form'       => $form->createView(),
                ])
            );
        }

        return $this->render('EventBundle:MeetingRequest:approvedRequest.html.twig', [
            'discussion' => $discussion,
            'form'       => $form->createView(),
        ]);
    }

    /**
     * Refuse a meeting request
     *
     * @param Request        $request
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return Response|JsonResponse
     */
    public function refuseRequestAction(
        Request $request,
        Sheet $sheet,
        MeetingRequest $meetingRequest
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$this->get('meeting.request_permission_manager')->isAllowedToRefuse($this->getUser(), $meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to refuse this meeting request.');
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not allowed method');
        }

        /** @var DiscussionMeetingRequestView $discussion */
        $discussion = $this
            ->get('tactician.commandbus.query')
            ->handle(new DiscussionMeetingRequestViewQuery($meetingRequest, $request->getLocale()));

        $refuseRequest = new RefuseRequest($meetingRequest, $this->getUser());
        $form          = $this->createForm(MeetingRequestRefuseType::class, $refuseRequest, [
            'action' => $this->generateUrl('event_meeting_request_refuse', [
                'sheet'          => $sheet->getId(),
                'meetingRequest' => $meetingRequest->getId()
            ]),
        ]);

        $isSubmitted = $form->handleRequest($request)->isSubmitted();

        if ($isSubmitted && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($refuseRequest);

            return $this->createJsonResponse(
                true,
                true,
                $this->renderView('EventBundle:MeetingRequest\Button:refusedProposition.html.twig', [
                    'meetingRequest' => $meetingRequest,
                ])
            );
        } elseif ($isSubmitted && !$form->isValid()) {
            return $this->createJsonResponse(
                false,
                false,
                $this->renderView('EventBundle:MeetingRequest:refusedRequest.html.twig', [
                    'discussion' => $discussion,
                    'form'       => $form->createView(),
                ])
            );
        }

        return $this->render('EventBundle:MeetingRequest:refusedRequest.html.twig', [
            'discussion' => $discussion,
            'form'       => $form->createView(),
        ]);
    }

    /**
     * @param Request        $request
     * @param MeetingRequest $meetingRequest
     *
     * @return Response
     */
    public function showConversationOfRefuseRequestAction(Request $request, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!$meetingRequest->getFromSheet()->hasUser($this->getUser())
            && !$meetingRequest->getToSheet()->hasUser($this->getUser())
        ) {
            throw $this->createNotFoundException('You are not allowed to see this meeting request.');
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not allowed method');
        }

        $isItRequest = $meetingRequest->getFromSheet()->hasUser($this->getUser());

        /** @var DiscussionMeetingRequestView $discussion */
        $discussion = $this
            ->get('tactician.commandbus.query')
            ->handle(new DiscussionMeetingRequestViewQuery($meetingRequest, $request->getLocale()));

        $form = null;

        if (!$isItRequest) {
            $unRefuse = new UnRefuseMeetingRequest($meetingRequest);
            $form     = $this->createForm(UnRefuseMeetingRequestType::class, $unRefuse, [
                'action' => $this->generateUrl('event_meeting_request_show_conversation_refuse', [
                    'meetingRequest' => $meetingRequest->getId()
                ]),
            ]);

            $isSubmitted = $form->handleRequest($request)->isSubmitted();

            if ($isSubmitted && $form->isValid()) {
                $this->get('tactician.commandbus')->handle($unRefuse);

                return $this->createJsonResponse(
                    true,
                    true,
                    $this->renderView('EventBundle:MeetingRequest\Button:approveRefuseRequestButton.html.twig', [
                        'meetingRequest' => $meetingRequest,
                        'sheet'          => $meetingRequest->getToSheet(),
                    ])
                );
            } elseif ($isSubmitted && !$form->isValid()) {
                return $this->createJsonResponse(
                    false,
                    false,
                    $this->renderView('EventBundle:MeetingRequest:showRefusedRequest.html.twig', [
                        'discussion'  => $discussion,
                        'isItRequest' => $isItRequest,
                        'form'        => $form->createView(),
                    ])
                );
            }
        }

        return $this->render('EventBundle:MeetingRequest:showRefusedRequest.html.twig', [
            'discussion'  => $discussion,
            'isItRequest' => $isItRequest,
            'form'        => $form !== null ? $form->createView() : null,
        ]);
    }

    /**
     * @param bool   $ok
     * @param bool   $close
     * @param string $html
     *
     * @return JsonResponse
     */
    private function createJsonResponse($ok, $close, $html)
    {
        $response = new JsonResponse();
        $response->setData([
            'status' => $ok === true ? 'ok' : 'error',
            'close'  => $close,
            'html'   => $html,
        ]);

        return $response;
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
     * Edit a meeting request
     *
     * @param Request        $request
     * @param EventDomain    $eventDomain
     * @param MeetingRequest $meetingRequest
     *
     * @return JsonResponse|Response
     */
    public function editRequestAction(Request $request, EventDomain $eventDomain, MeetingRequest $meetingRequest)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->get('sheet.sheet_guesser')->getUserSheet(
            $this->getUser(),
            $eventDomain->getEvent(),
            $request->getLocale()
        );
        $permissionManager = $this->get('meeting.request_permission_manager');

        if (($meetingRequest->isApproved()
            && !$permissionManager->isAllowedToEditApproved($this->getUser(), $meetingRequest, $sheet)
        ) || ($meetingRequest->isSent()
                && !$permissionManager->isAllowedToEdit($this->getUser(), $meetingRequest, $sheet)
            )
        ) {
            throw $this->createNotFoundException('You are not allowed to edit this meeting request.');
        }

        /** @var DiscussionMeetingRequestView $discussion */
        $discussion = $this
            ->get('tactician.commandbus.query')
            ->handle(new DiscussionMeetingRequestViewQuery($meetingRequest, $request->getLocale()));
        $form = null;

        $isProposition = $meetingRequest->getFromSheet() !== $sheet;

        $cancelForm = null;
        if ($permissionManager->isAllowedToCancel($this->getUser(), $meetingRequest, $sheet)) {
            $cancelRequest = new CancelRequest($meetingRequest, $this->getUser(), $sheet);
            $cancelForm = $this->createForm(MeetingRequestCancelType::class, $cancelRequest, [
                'action' => $this->generateUrl('event_meeting_request_edit', [
                    'meetingRequest' => $meetingRequest->getId()
                ]),
            ]);

            if ($cancelForm->handleRequest($request)->isSubmitted() && $cancelForm->isValid()) {
                if ($isProposition) {
                    $sheetLooked = $meetingRequest->getFromSheet();
                } else {
                    $sheetLooked = $meetingRequest->getToSheet();
                }

                $this->get('tactician.commandbus')->handle($cancelRequest);

                return $this->createJsonResponse(
                    true,
                    true,
                    $this->renderView('EventBundle:MeetingRequest/Button:createRequest.html.twig', [
                        'sheet' => $sheetLooked,
                    ])
                );
            }
        }

        if (!$discussion->hasMessageOfSheet($sheet) || 1 < $sheet->countParticipant()) {
            $command = new UpdateRequest($meetingRequest, $sheet, $this->getUser());
            $form    = $this->createForm(MeetingRequestUpdateType::class, $command, [
                'sheet'            => $sheet,
                'locale'           => $request->getLocale(),
                'show_description' => !$discussion->hasMessageOfSheet($sheet),
                'action'           => $this->generateUrl('event_meeting_request_edit', [
                    'meetingRequest' => $meetingRequest->getId()
                ]),
            ]);

            $isSubmitted = $form->handleRequest($request)->isSubmitted();
            if ($isSubmitted && $form->isValid()) {
                $this->get('tactician.commandbus')->handle($command);
                if ($meetingRequest->isApproved()) {
                    if ($isProposition) {
                        $this->addFlash('success', 'flash.meeting_request.approved.proposition.edit.success');
                    } else {
                        $this->addFlash('success', 'flash.meeting_request.approved.request.edit.success');
                    }
                } else {
                    $this->addFlash('success', 'flash.meeting_request.pending.request.edit.success');
                }

                return $this->createJsonResponse(
                    true,
                    false,
                    $this->renderView('EventBundle:MeetingRequest:editRequestSuccess.html.twig', [
                        'isProposition'  => $isProposition,
                        'meetingRequest' => $meetingRequest,
                    ])
                );
            } elseif ($isSubmitted && !$form->isValid()) {
                return $this->createJsonResponse(
                    false,
                    false,
                    $this->renderView('EventBundle:MeetingRequest:editRequest.html.twig', [
                        'discussion'     => $discussion,
                        'form'           => $form->createView(),
                        'cancelForm'     => $cancelForm !== null ? $cancelForm->createView() : null,
                        'isProposition'  => $isProposition,
                        'meetingRequest' => $meetingRequest,
                    ])
                );
            }
        }

        return $this->render('EventBundle:MeetingRequest:editRequest.html.twig', [
            'discussion'     => $discussion,
            'form'           => $form !== null ? $form->createView() : $form,
            'cancelForm'     => $cancelForm !== null ? $cancelForm->createView() : null,
            'isProposition'  => $isProposition,
            'meetingRequest' => $meetingRequest,
        ]);
    }

    /**
     * @param Sheet $sheet
     * @param array $filters
     * @param array $typeViews
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createSearchForm(Sheet $sheet, array $filters, array $typeViews)
    {
        return $this->get('form.factory')->createNamed('', SearchType::class, $filters, [
            'label'     => null,
            'typeViews' => $typeViews,
            'action'    => $this->generateUrl('event_meeting_list_request', [
                'sheet' => $sheet->getId(),
            ]),
        ]);
    }
}
