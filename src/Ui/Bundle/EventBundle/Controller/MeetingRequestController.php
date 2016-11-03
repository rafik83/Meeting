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
use Proximum\Vimeet\Application\Command\Meeting\UnApproveMeetingRequest;
use Proximum\Vimeet\Application\Command\Meeting\UnRefuseMeetingRequest;
use Proximum\Vimeet\Application\Command\MeetingRequest\UpdateRequest;
use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestListViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\Message\DiscussionMeetingRequestViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\StateListViewQuery;
use Proximum\Vimeet\Application\Query\Type\MeetingTypeViewQuery;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestListView;
use Proximum\Vimeet\Application\View\Meeting\Message\DiscussionMeetingRequestView;
use Proximum\Vimeet\Application\View\Meeting\StateListsView;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestApproveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestCancelType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestCreateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestRefuseType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestUpdateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\SearchType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\UnApproveMeetingRequestType;
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
     * List all meeting request of a sheet (sent and received)
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
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

        if (!$this->get('meeting.request_permission_manager')->isAllowedToCreate(
            $this->getUser(),
            $from,
            $sheet
        )) {
            throw $this->createNotFoundException('The viewer is not allowed to create a meeting request with this sheet');
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

            return new JsonResponse($this->createJsonResponseData(
                true,
                true,
                $this->renderView('EventBundle:MeetingRequest\Button:pendingRequestButton.html.twig', [
                    'meetingRequest' => $result->meetingRequest,
                ])
            ));
        } elseif ($isSubmitted && !$form->isValid()) {

            return new JsonResponse($this->createJsonResponseData(
                false,
                false,
                $this->renderView('EventBundle:MeetingRequest:createRequest.html.twig', [
                    'form' => $form->createView(),
                ])
            ));
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

        $approveRequest = new ApproveRequest($this->getUser(), $meetingRequest, $sheet);
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

            return new JsonResponse($this->createJsonResponseData(
                true,
                true,
                $this->renderView('EventBundle:MeetingRequest\Button:approvedProposition.html.twig', [
                    'meetingRequest' => $meetingRequest
                ])
            ));
        } elseif ($isSubmitted && !$form->isValid()) {
            return new JsonResponse($this->createJsonResponseData(
                false,
                false,
                $this->renderView('EventBundle:MeetingRequest:approvedRequest.html.twig', [
                    'discussion' => $discussion,
                    'form'       => $form->createView(),
                ])
            ));
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

            return new JsonResponse($this->createJsonResponseData(
                true,
                true,
                $this->renderView('EventBundle:MeetingRequest\Button:refusedProposition.html.twig', [
                    'meetingRequest' => $meetingRequest,
                ])
            ));
        } elseif ($isSubmitted && !$form->isValid()) {
            return new JsonResponse($this->createJsonResponseData(
                false,
                false,
                $this->renderView('EventBundle:MeetingRequest:refusedRequest.html.twig', [
                    'discussion' => $discussion,
                    'form'       => $form->createView(),
                ])
            ));
        }

        return $this->render('EventBundle:MeetingRequest:refusedRequest.html.twig', [
            'discussion' => $discussion,
            'form'       => $form->createView(),
        ]);
    }

    /**
     * @param Request        $request
     * @param EventDomain    $eventDomain
     * @param MeetingRequest $meetingRequest
     *
     * @return Response
     */
    public function showConversationOfRefuseRequestAction(
        Request $request,
        EventDomain $eventDomain,
        MeetingRequest $meetingRequest
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $permissionManager = $this->get('meeting.request_permission_manager');

        if (!$permissionManager->isAllowedToSeeConversationOfRefuseMeetingRequest($this->getUser(), $meetingRequest)) {
            throw $this->createNotFoundException('You are not allowed to see this meeting request.');
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not allowed method');
        }

        $sheet = $this->get('sheet.sheet_guesser')->getUserSheet(
            $this->getUser(),
            $eventDomain->getEvent(),
            $request->getLocale()
        );

        /** @var DiscussionMeetingRequestView $discussion */
        $discussion = $this
            ->get('tactician.commandbus.query')
            ->handle(new DiscussionMeetingRequestViewQuery($meetingRequest, $request->getLocale()));

        $form = null;

        if ($permissionManager->isAllowedToUnRefuse($this->getUser(), $meetingRequest, $sheet)) {
            $unRefuse = new UnRefuseMeetingRequest($this->getUser(), $meetingRequest, $sheet);
            $form     = $this->createForm(UnRefuseMeetingRequestType::class, $unRefuse, [
                'action' => $this->generateUrl('event_meeting_request_show_conversation_refuse', [
                    'meetingRequest' => $meetingRequest->getId()
                ]),
            ]);

            $isSubmitted = $form->handleRequest($request)->isSubmitted();

            if ($isSubmitted && $form->isValid()) {
                $this->get('tactician.commandbus')->handle($unRefuse);

                return new JsonResponse($this->createJsonResponseData(
                    true,
                    true,
                    $this->renderView('EventBundle:MeetingRequest\Button:approveRefuseRequestButton.html.twig', [
                        'meetingRequest' => $meetingRequest,
                        'sheet'          => $meetingRequest->getToSheet(),
                    ])
                ));
            } elseif ($isSubmitted && !$form->isValid()) {
                return new JsonResponse($this->createJsonResponseData(
                    false,
                    false,
                    $this->renderView('EventBundle:MeetingRequest:showRefusedRequest.html.twig', [
                        'discussion'  => $discussion,
                        'isItRequest' => $meetingRequest->isSender($sheet),
                        'form'        => $form->createView(),
                    ])
                ));
            }
        }

        return $this->render('EventBundle:MeetingRequest:showRefusedRequest.html.twig', [
            'discussion'  => $discussion,
            'isItRequest' => $meetingRequest->isSender($sheet),
            'form'        => $form !== null ? $form->createView() : null,
        ]);
    }

    /**
     * @param bool   $ok
     * @param bool   $close
     * @param string $html
     *
     * @return array
     */
    private function createJsonResponseData($ok, $close, $html)
    {
        return [
            'status' => $ok === true ? 'ok' : 'error',
            'close'  => $close,
            'html'   => $html,
        ];
    }

    /**
     * @param Request                  $request
     * @param MeetingRequest           $meetingRequest
     * @param Sheet                    $sheet
     * @param RequestPermissionManager $permissionManager
     * @param null                     $cancelForm
     *
     * @return null|JsonResponse
     */
    private function cancelFormOnEdit(
        Request &$request,
        MeetingRequest &$meetingRequest,
        Sheet &$sheet,
        RequestPermissionManager &$permissionManager,
        &$cancelForm
    ) {
        if ($permissionManager->isAllowedToCancel($this->getUser(), $meetingRequest, $sheet)) {
            $cancelRequest = new CancelRequest($meetingRequest, $this->getUser(), $sheet);
            $cancelForm    = $this->createForm(MeetingRequestCancelType::class, $cancelRequest, [
                'action' => $this->generateUrl('event_meeting_request_edit', [
                    'meetingRequest' => $meetingRequest->getId()
                ]),
            ]);

            if ($cancelForm->handleRequest($request)->isSubmitted() && $cancelForm->isValid()) {
                $this->get('tactician.commandbus')->handle($cancelRequest);

                return new JsonResponse($this->createJsonResponseData(
                    true,
                    true,
                    $this->renderView('EventBundle:MeetingRequest/Button:createRequest.html.twig', [
                        'sheet' => $meetingRequest->getToSheet(),
                    ])
                ));
            }
        }

        return null;
    }

    /**
     * @param Request                  $request
     * @param MeetingRequest           $meetingRequest
     * @param Sheet                    $sheet
     * @param RequestPermissionManager $permissionManager
     * @param null                     $unApprovedForm
     *
     * @return null|JsonResponse
     */
    private function unApproveFormOnEdit(
        Request &$request,
        MeetingRequest &$meetingRequest,
        Sheet &$sheet,
        RequestPermissionManager &$permissionManager,
        &$unApprovedForm = null
    ) {
        if ($permissionManager->isAllowedToUnApprove($this->getUser(), $meetingRequest, $sheet)) {
            $unApprovedRequest = new UnApproveMeetingRequest($this->getUser(), $meetingRequest, $sheet);
            $unApprovedForm    = $this->createForm(UnApproveMeetingRequestType::class, $unApprovedRequest, [
                'action' => $this->generateUrl('event_meeting_request_edit', [
                    'meetingRequest' => $meetingRequest->getId()
                ]),
            ]);

            if ($unApprovedForm->handleRequest($request)->isSubmitted() && $unApprovedForm->isValid()) {
                $this->get('tactician.commandbus')->handle($unApprovedRequest);

                return new JsonResponse($this->createJsonResponseData(
                    true,
                    true,
                    $this->renderView('EventBundle:MeetingRequest/Button:approveRefuseRequestButton.html.twig', [
                        'meetingRequest' => $meetingRequest,
                        'sheet'          => $sheet
                    ])
                ));
            }
        }

        return null;
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

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not allowed method');
        }

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

        $isProposition      = $meetingRequest->isReceiver($sheet);
        $form               = null;
        $cancelForm         = null;
        $unApprovedForm     = null;
        $cancelResponse     = $this->cancelFormOnEdit(
            $request,
            $meetingRequest,
            $sheet,
            $permissionManager,
            $cancelForm
        );
        $unapprovedResponse = $this->unApproveFormOnEdit(
            $request,
            $meetingRequest,
            $sheet,
            $permissionManager,
            $unApprovedForm
        );

        if ($cancelResponse !== null) {
            return $cancelResponse;
        }

        if ($unapprovedResponse !== null) {
            return $unapprovedResponse;
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

                return new JsonResponse($this->createJsonResponseData(
                    true,
                    false,
                    $this->renderView('EventBundle:MeetingRequest:editRequestSuccess.html.twig', [
                        'isProposition'  => $isProposition,
                        'meetingRequest' => $meetingRequest,
                    ])
                ));
            } elseif ($isSubmitted && !$form->isValid()) {
                return new JsonResponse($this->createJsonResponseData(
                    false,
                    false,
                    $this->renderView('EventBundle:MeetingRequest:editRequest.html.twig', [
                        'discussion'     => $discussion,
                        'form'           => $form->createView(),
                        'cancelForm'     => $cancelForm !== null ? $cancelForm->createView() : null,
                        'unApprovedForm' => $unApprovedForm !== null ? $unApprovedForm->createView() : null,
                        'isProposition'  => $isProposition,
                        'meetingRequest' => $meetingRequest,
                    ])
                ));
            }
        }

        return $this->render('EventBundle:MeetingRequest:editRequest.html.twig', [
            'discussion'     => $discussion,
            'form'           => $form !== null ? $form->createView() : $form,
            'cancelForm'     => $cancelForm !== null ? $cancelForm->createView() : null,
            'unApprovedForm' => $unApprovedForm !== null ? $unApprovedForm->createView() : null,
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
