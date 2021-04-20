<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\ApproveRequest;
use Proximum\Vimeet\Application\Command\Meeting\CancelRequest;
use Proximum\Vimeet\Application\Command\Meeting\CreateRequest;
use Proximum\Vimeet\Application\Command\Meeting\RefuseRequest;
use Proximum\Vimeet\Application\Command\Meeting\UnApproveMeetingRequest;
use Proximum\Vimeet\Application\Command\Meeting\UnRefuseMeetingRequest;
use Proximum\Vimeet\Application\Command\Meeting\UpdateMeetingRequest;
use Proximum\Vimeet\Application\Command\MeetingRequest\Counter;
use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQuery;
use Proximum\Vimeet\Application\Query\Category\MeetingCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestListViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\Message\DiscussionMeetingRequestViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\StateListViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\Query\Type\MeetingTypeViewQuery;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Application\View\Meeting\ApproveRequestResult;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestListView;
use Proximum\Vimeet\Application\View\Meeting\Message\DiscussionMeetingRequestView;
use Proximum\Vimeet\Application\View\Meeting\StateListsView;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Meeting\Constant;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\CatalogAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestApproveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestCancelType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestCreateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestRefuseType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\MeetingRequestUpdateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\SearchType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\UnApproveMeetingRequestType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request\UnRefuseMeetingRequestType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\AvailabilityConfirmationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\AvailabilityConfirmationCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class MeetingRequestController extends AbstractController
{
    private AvailabilityConfirmationCheckerHandler $availabilityConfirmationCheckerHandler;
    private DDayGuesser $dDayGuesser;
    private MeetingSlotRepositoryInterface $meetingSlotRepository;
    private EventOpenAccessChecker $eventOpenAccessChecker;
    private MeetingRequestAccessChecker $meetingRequestAccessChecker;
    private CatalogAccessChecker $catalogAccessChecker;
    private VisibleParticipationTypes $catalogVisibleParticipationTypes;
    private RequestRepositoryInterface $requestRepository;
    private SheetRepositoryInterface $sheetRepository;
    private RequestPermissionManager $permissionManager;
    private Counter $meetingRequestCounter;
    private MeetingPublishedAccessChecker $meetingPublishedAccessChecker;
    private AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker;
    private FormFactoryInterface $formFactory;
    private ParticipantInfoGuesser $participantInfoGuesser;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        AvailabilityConfirmationCheckerHandler $availabilityConfirmationCheckerHandler,
        DDayGuesser $dDayGuesser,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        EventOpenAccessChecker $eventOpenAccessChecker,
        MeetingRequestAccessChecker $meetingRequestAccessChecker,
        CatalogAccessChecker $catalogAccessChecker,
        VisibleParticipationTypes $catalogVisibleParticipationTypes,
        RequestRepositoryInterface $requestRepository,
        SheetRepositoryInterface $sheetRepository,
        RequestPermissionManager $permissionManager,
        Counter $meetingRequestCounter,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker,
        FormFactoryInterface $formFactory,
        ParticipantInfoGuesser $participantInfoGuesser,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->availabilityConfirmationCheckerHandler = $availabilityConfirmationCheckerHandler;
        $this->dDayGuesser = $dDayGuesser;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
        $this->meetingRequestAccessChecker = $meetingRequestAccessChecker;
        $this->catalogAccessChecker = $catalogAccessChecker;
        $this->catalogVisibleParticipationTypes = $catalogVisibleParticipationTypes;
        $this->requestRepository = $requestRepository;
        $this->sheetRepository = $sheetRepository;
        $this->permissionManager = $permissionManager;
        $this->meetingRequestCounter = $meetingRequestCounter;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
        $this->answeringMeetingRequestAccessChecker = $answeringMeetingRequestAccessChecker;
        $this->formFactory = $formFactory;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }
    /**
     * List all meeting request of a sheet (sent and received)
     */
    public function listRequestAction(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $event = $eventDomain->getEvent();
        $this->denyAccessUnlessGranted(CatalogAccessVoter::VIEW, $event);

        $user = $userDomain->getUser();

        $availabilityConfirmation = $this->availabilityConfirmationCheckerHandler->handle(new AvailabilityConfirmationChecker(
                $event,
                $sheet,
                $user,
                AvailabilityConfirmationChecker::ORIGIN_MEETING_REQUEST_MANAGEMENT
            ))
        ;

        if (!$availabilityConfirmation->isAllowedToAccess()) {
            return $this->redirect($availabilityConfirmation->redirectRoute);
        }

        $locale = $request->getLocale();

        $typeViews = $this->queryBus->handle(new MeetingTypeViewQuery(
            $sheet, $request->getLocale()
        ));

        $categoryViews = $this->queryBus->handle(new MeetingCategoryViewQuery(
            $sheet,
            $request->getLocale()
        ));

        $dDay = $this->dDayGuesser->isItDDayAndFeatureEnabled($event);
        $isUserParticipant = $sheet->hasUserParticipant($user);
        $filterAvailableSlot = $dDay && $isUserParticipant;
        $availableSlots = [];
        $specificSlot = null;

        if (true === $filterAvailableSlot) {
            /** @var AvailableSlotView[] $availableSlots */
            $availableSlots = $this->queryBus->handle(
                new AvailableSlotsByParticipantQuery($event, $sheet->getUserParticipant($user))
            );

            $filterAvailableSlot = !empty($availableSlots);

            $slotId = $request->query->get('slot_id');

            if (null !== $slotId) {
                $slot = $this->meetingSlotRepository->findById((int) $slotId);

                if (null !== $slot) {
                    foreach ($availableSlots as $availableSlot) {
                        if ($availableSlot->id === $slot->getId()) {
                            $specificSlot = $slot;
                        }
                    }
                }
            }
        }

        $defaults = SearchType::getDefaultFilters($typeViews, $categoryViews);
        $searchForm = $this->createSearchForm(
            $event,
            $sheet,
            $locale,
            $defaults,
            SearchType::transformTypeViews($typeViews),
            SearchType::transformCategoryViews($categoryViews),
            $filterAvailableSlot,
            $specificSlot
        );

        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()) {
            $filters = array_merge($defaults,
                array_filter(
                    $searchForm->getData(), static function ($data) {
                        return !empty($data);
                    }
                )
            );

            $searchForm = $this->createSearchForm(
                $event,
                $sheet,
                $locale,
                $filters,
                SearchType::transformTypeViews($typeViews),
                SearchType::transformCategoryViews($categoryViews),
                $filterAvailableSlot,
                $specificSlot
            );
        } else {
            $filters = $defaults;
        }

        $event = $eventDomain->getEvent();
        $query = new MeetingRequestListViewQuery(
            $event,
            $sheet,
            $user,
            $locale,
            $filters,
            $this->getSpecificSlot($filters, $specificSlot, $availableSlots),
            \count($categoryViews) >= 1
        );
        $statusQuery = new StateListViewQuery(
            $sheet,
            $filters,
            $this->getSpecificSlot($filters, $specificSlot, $availableSlots)
        );

        /** @var MeetingRequestListView $meetingRequestListView */
        $meetingRequestListView = $this->queryBus->handle($query);

        /** @var StateListsView $stateListsView */
        $stateListsView = $this->queryBus->handle($statusQuery);

        $template = 'EventBundle:MeetingRequest:listRequest.html.twig';

        if ($request->isXmlHttpRequest()) {
            $template = 'EventBundle:MeetingRequest/Partials:catalog.html.twig';
        }

        $isEventOpen = $this->eventOpenAccessChecker->allowedToAccess($event);

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_MEETING_MANAGEMENT,
            $request->getLocale()
        );
        $tipTranslationViews = $this->queryBus->handle($tipTranslationViewQuery);

        return $this->render($template, [
            'event' => $event,
            'sheet' => $sheet,
            'meetingRequestView' => $meetingRequestListView,
            'stateListsView' => $stateListsView,
            'searchForm' => $searchForm->createView(),
            'isCatalog' => true, // set menu link visible,
            'isMeeting' => true,
            'isEventOpen' => $isEventOpen,
            'filterRequestProposition' => $this->isFilterRequestPropositionActive($searchForm->get('state')->getData()),
            'resultsCount' => \count($meetingRequestListView->getMeetingRequestsView()),
            'tipTranslationViews' => $tipTranslationViews,
            'participant' => $sheet->getUserParticipant($user),
        ]);
    }

    /**
     * @param array            $filters
     * @param MeetingSlot|null $specificSlot
     * @param array            $availableSlots
     *
     * @return array
     */
    private function getSpecificSlot(array $filters, MeetingSlot $specificSlot = null, array $availableSlots)
    {
        if (empty($filters['availableSlot'])) {
            return [];
        }

        if ($specificSlot instanceof MeetingSlot && Constant::FILTER_AVAILABLE_SLOT_IDS_SLOT === $filters['availableSlot']) {
            return [$specificSlot];
        }

        return $availableSlots;
    }

    /**
     * @param $state
     *
     * @return bool
     */
    private function isFilterRequestPropositionActive($state)
    {
        return Constant::FILTER_STATE_APPROVED === $state || Constant::FILTER_STATE_REFUSED === $state;
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Sheet   $fromSheet
     * @param Sheet   $toSheet
     *
     * @throws NotFoundHttpException
     */
    private function authorizeToCreateRequest(Request $request, Event $event, Sheet $fromSheet, Sheet $toSheet)
    {
        // if the meeting request are closed
        if (!$this->meetingRequestAccessChecker->allowedToAccess($event)) {
            throw $this->createNotFoundException('You can not request a meeting as the meeting request are closed');
        }

        if (!$toSheet->isInInternalCatalog()) {
            throw $this->createNotFoundException('ToSheet not in catalog');
        }

        // If the catalog is closed
        if (!$this->catalogAccessChecker->allowedToAccess($event)) {
            throw $this->createNotFoundException();
        }

        // If the sheet that request the meeting is not in catalog
        if (!$fromSheet->isInInternalCatalog()) {
            throw $this->createNotFoundException('Viewer Sheet not in catalog');
        }

        $visibleTypes = $this->catalogVisibleParticipationTypes->getAllowedTypesList($fromSheet);

        // If there are no rules between the two sheets
        if (!in_array($toSheet->getType(), $visibleTypes)) {
            throw $this->createNotFoundException('The viewer is not allowed to create a meeting request with this sheet');
        }

        // If the requester sheet is the sheet requested
        if ($fromSheet === $toSheet) {
            throw $this->createNotFoundException('You can not request a meeting with yourself');
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not allowed method');
        }

        // If there is already a meeting request between the two sheets
        if (null !== $this->requestRepository->getRequestBetweenSheets($fromSheet, $toSheet)) {
            throw $this->createNotFoundException('You can not request a meeting as there is already one');
        }
    }

    /**
     * Create a meeting request between two sheet
     * @param Sheet         $sheet       fromSheet
     */
    public function createRequestAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet, // fromSheet
        int $toSheet, // int to avoid collision with sheet param converter
        UserInterface $user
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User not found');
        }

        $priorityNumberAvailable = $this->meetingRequestCounter->getCountSheetPriorityAvailable($sheet);

        $toSheet = $this->sheetRepository->getSheetById($toSheet);

        if (null === $toSheet || $eventDomain->getEvent() !== $toSheet->getEvent()) {
            throw $this->createAccessDeniedException('ToSheet not found');
        }

        $this->authorizeToCreateRequest($request, $eventDomain->getEvent(), $sheet, $toSheet);

        $createRequest = new CreateRequest($eventDomain->getEvent(), $sheet, $toSheet, $user, $request->getLocale());
        $form = $this->createForm(MeetingRequestCreateType::class, $createRequest, [
            'action' => $this->generateUrl('event_catalog_sheet_meeting_request', [
                'sheet' => $sheet->getId(),
                'toSheet' => $toSheet->getId(),
            ]),
            'sheet' => $sheet,
            'locale' => $request->getLocale(),
            'priorityNumberAvailable' => $priorityNumberAvailable
        ]);

        $isSubmitted = $form->handleRequest($request)->isSubmitted();
        if ($isSubmitted && $form->isValid()) {
            $result = $this->commandBus->handle($createRequest);

            if ($result instanceof ApproveRequestResult) {
                $flashMessageView = $this->renderView('EventBundle::MeetingRequest\Message\requestTransformedIntoMeeting.html.twig', [
                    'meetingDdayView' => $result->meetingView,
                    'error' => $result->hasError,
                ]);

                return new JsonResponse($this->createJsonResponseData(
                    true,
                    null === $result || (null !== $result && null === $result->meetingView && !$result->hasError),
                    $this->renderView('EventBundle:MeetingRequest\Button:approvedRequest.html.twig', [
                        'sheet' => $sheet,
                        'meetingRequest' => $result->request,
                        'isMeetingPublished' => $this->meetingPublishedAccessChecker
                            ->allowedToAccess($eventDomain->getEvent()),
                        'isMeetingRequestUpdateLocked' => $eventDomain
                            ->getEvent()
                            ->getConfiguration()
                            ->isMeetingRequestUpdateLocked(),
                        'isPhoneValidationRequired' => false,
                    ]),
                    '',
                    $flashMessageView ?? null
                ));
            }

            return new JsonResponse($this->createJsonResponseData(
                true,
                true,
                $this->renderView('EventBundle:MeetingRequest\Button:pendingRequestButton.html.twig', [
                    'sheet' => $sheet,
                    'meetingRequest' => $result->meetingRequest,
                    'isPhoneValidationRequired' => false,
                ]),
                $this->getParticipantsHtml($createRequest->participants, $request->getLocale())
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
            'sheet' => $sheet,
            'priorityNumberAvailable' => $priorityNumberAvailable,
        ]);
    }

    /**
     * Approve a meeting request
     */
    public function approveRequestAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        MeetingRequest $meetingRequest
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$this->permissionManager->isAllowedToApprove($meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to approve this meeting request.');
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not allowed method');
        }

        $priorityNumberAvailable = $this->meetingRequestCounter->getCountSheetPriorityAvailable($sheet);

        /** @var DiscussionMeetingRequestView $discussion */
        $discussion = $this
            ->queryBus
            ->handle(new DiscussionMeetingRequestViewQuery($meetingRequest, $request->getLocale()));

        $approveRequest = new ApproveRequest($this->getUser(), $meetingRequest, $sheet, $request->getLocale());
        $form = $this->createForm(MeetingRequestApproveType::class, $approveRequest, [
            'action' => $this->generateUrl('event_meeting_request_approve', [
                'sheet' => $sheet->getId(),
                'meetingRequest' => $meetingRequest->getId(),
            ]),
            'locale' => $request->getLocale(),
            'sheet' => $sheet,
            'priorityNumberAvailable' => $priorityNumberAvailable
        ]);

        $isSubmitted = $form->handleRequest($request)->isSubmitted();

        if ($isSubmitted && $form->isValid()) {
            /** @var ApproveRequestResult $approveRequestResult */
            $approveRequestResult = $this->commandBus->handle($approveRequest);

            if ($approveRequestResult->hasError || $approveRequestResult->meetingView !== null) {
                $flashMessageView = $this->renderView('EventBundle::MeetingRequest\Message\requestTransformedIntoMeeting.html.twig', [
                    'meetingDdayView' => $approveRequestResult->meetingView,
                    'error' => $approveRequestResult->hasError,
                ]);
            }

            return new JsonResponse($this->createJsonResponseData(
                true,
                null === $approveRequestResult->meetingView && !$approveRequestResult->hasError,
                $this->renderView('EventBundle:MeetingRequest\Button:approvedProposition.html.twig', [
                    'sheet' => $sheet,
                    'meetingRequest' => $meetingRequest,
                    'isMeetingPublished' => $this->meetingPublishedAccessChecker
                        ->allowedToAccess($eventDomain->getEvent()),
                    'isMeetingRequestUpdateLocked' => $eventDomain
                        ->getEvent()
                        ->getConfiguration()
                        ->isMeetingRequestUpdateLocked(),
                    'isPhoneValidationRequired' => false,
                ]),
                $this->getParticipantsHtml($approveRequest->participants, $request->getLocale()),
                $flashMessageView ?? null
            ));
        } elseif ($isSubmitted && !$form->isValid()) {
            return new JsonResponse($this->createJsonResponseData(
                false,
                false,
                $this->renderView('EventBundle:MeetingRequest:approvedRequest.html.twig', [
                    'discussion' => $discussion,
                    'form' => $form->createView(),
                ])
            ));
        }

        return $this->render('EventBundle:MeetingRequest:approvedRequest.html.twig', [
            'discussion' => $discussion,
            'sheet' => $sheet,
            'priorityNumberAvailable' => $priorityNumberAvailable,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Refuse a meeting request
     */
    public function refuseRequestAction(
        Request $request,
        Sheet $sheet,
        MeetingRequest $meetingRequest
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$this->permissionManager->isAllowedToRefuse($meetingRequest, $sheet)) {
            throw $this->createAccessDeniedException('You are not allowed to refuse this meeting request.');
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not allowed method');
        }

        /** @var DiscussionMeetingRequestView $discussion */
        $discussion = $this
            ->queryBus
            ->handle(new DiscussionMeetingRequestViewQuery($meetingRequest, $request->getLocale()));

        $refuseRequest = new RefuseRequest($meetingRequest, $this->getUser());
        $form = $this->createForm(MeetingRequestRefuseType::class, $refuseRequest, [
            'action' => $this->generateUrl('event_meeting_request_refuse', [
                'sheet' => $sheet->getId(),
                'meetingRequest' => $meetingRequest->getId(),
            ]),
        ]);

        $isSubmitted = $form->handleRequest($request)->isSubmitted();

        if ($isSubmitted && $form->isValid()) {
            $this->commandBus->handle($refuseRequest);

            return new JsonResponse($this->createJsonResponseData(
                true,
                true,
                $this->renderView('EventBundle:MeetingRequest\Button:refusedProposition.html.twig', [
                    'sheet' => $sheet,
                    'meetingRequest' => $meetingRequest,
                    'isPhoneValidationRequired' => false,
                ])
            ));
        } elseif ($isSubmitted && !$form->isValid()) {
            return new JsonResponse($this->createJsonResponseData(
                false,
                false,
                $this->renderView('EventBundle:MeetingRequest:refusedRequest.html.twig', [
                    'discussion' => $discussion,
                    'form' => $form->createView(),
                ])
            ));
        }

        return $this->render('EventBundle:MeetingRequest:refusedRequest.html.twig', [
            'discussion' => $discussion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request        $request
     * @param EventDomain    $eventDomain
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return Response
     */
    public function showConversationOfRefuseRequestAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        MeetingRequest $meetingRequest
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        if (!$this->permissionManager->isAllowedToSeeConversationOfRefuseMeetingRequest($sheet, $meetingRequest)) {
            throw $this->createNotFoundException('You are not allowed to see this meeting request.');
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not allowed method');
        }

        /** @var DiscussionMeetingRequestView $discussion */
        $discussion = $this
            ->queryBus
            ->handle(new DiscussionMeetingRequestViewQuery($meetingRequest, $request->getLocale()));

        $form = null;

        if ($this->permissionManager->isAllowedToUnRefuse($meetingRequest, $sheet)) {
            $unRefuse = new UnRefuseMeetingRequest($this->getUser(), $meetingRequest, $sheet);
            $form = $this->createForm(UnRefuseMeetingRequestType::class, $unRefuse, [
                'action' => $this->generateUrl('event_meeting_request_show_conversation_refuse', [
                    'sheet' => $sheet->getId(),
                    'meetingRequest' => $meetingRequest->getId(),
                ]),
            ]);

            $isSubmitted = $form->handleRequest($request)->isSubmitted();

            if ($isSubmitted && $form->isValid()) {
                $this->commandBus->handle($unRefuse);

                // If the meeting request are still answerable
                if ($this->answeringMeetingRequestAccessChecker
                    ->allowedToAccess($eventDomain->getEvent())
                ) {
                    return new JsonResponse($this->createJsonResponseData(
                        true,
                        true,
                        $this->renderView('EventBundle:MeetingRequest\Button:approveRefuseRequestButton.html.twig', [
                            'meetingRequest' => $meetingRequest,
                            'sheet' => $meetingRequest->getToSheet(),
                            'isPhoneValidationRequired' => false,
                        ])
                    ));
                } else {
                    return $this->displayWarningClosedAnsweringMeetingRequest();
                }
            } elseif ($isSubmitted && !$form->isValid()) {
                return new JsonResponse($this->createJsonResponseData(
                    false,
                    false,
                    $this->renderView('EventBundle:MeetingRequest:showRefusedRequest.html.twig', [
                        'discussion' => $discussion,
                        'isItRequest' => $meetingRequest->isSender($sheet),
                        'form' => $form->createView(),
                    ])
                ));
            }
        }

        return $this->render('EventBundle:MeetingRequest:showRefusedRequest.html.twig', [
            'discussion' => $discussion,
            'isItRequest' => $meetingRequest->isSender($sheet),
            'form' => $form instanceof FormInterface ? $form->createView() : null,
        ]);
    }

    /**
     * @param bool        $ok
     * @param bool        $close
     * @param string      $html
     * @param string      $participantsHtml
     * @param null|string $flashMessage
     *
     * @return array
     */
    private function createJsonResponseData($ok, $close, $html, $participantsHtml = '', $flashMessage = null)
    {
        $response = [
            'status' => true === $ok ? 'ok' : 'error',
            'close' => $close,
            'html' => $html,
            'participantsHtml' => $participantsHtml,
        ];

        if (null !== $flashMessage) {
            $response['flashMessage'] = $flashMessage;
        }

        return $response;
    }

    /**
     * @return JsonResponse
     */
    private function displayWarningClosedMeetingRequest()
    {
        return new JsonResponse($this->createJsonResponseData(
            true,
            true,
            $this->renderView('EventBundle:MeetingRequest/Button:closedMeetingRequest.html.twig')
        ));
    }

    /**
     * @return JsonResponse
     */
    private function displayWarningClosedAnsweringMeetingRequest()
    {
        return new JsonResponse($this->createJsonResponseData(
            true,
            true,
            $this->renderView('EventBundle:MeetingRequest/Button:closedAnsweringMeetingRequest.html.twig')
        ));
    }

    /**
     * @param Request                  $request
     * @param MeetingRequest           $meetingRequest
     * @param Sheet                    $sheet
     * @param null                     $cancelForm
     *
     * @return null|JsonResponse
     */
    private function cancelFormOnEdit(
        Request &$request,
        MeetingRequest &$meetingRequest,
        Sheet &$sheet,
        &$cancelForm
    ) {
        if ($this->permissionManager->isAllowedToCancel($meetingRequest, $sheet)) {
            $toSheet = $meetingRequest->getToSheet();
            $cancelRequest = new CancelRequest($meetingRequest, $this->getUser(), $sheet);
            $cancelForm = $this->createForm(MeetingRequestCancelType::class, $cancelRequest, [
                'action' => $this->generateUrl('event_meeting_request_edit', [
                    'sheet' => $sheet->getId(),
                    'meetingRequest' => $meetingRequest->getId(),
                ]),
            ]);

            if ($cancelForm->handleRequest($request)->isSubmitted() && $cancelForm->isValid()) {
                $this->commandBus->handle($cancelRequest);

                // If you are still allowed to request someone in meeting
                if ($this->meetingRequestAccessChecker
                    ->allowedToAccess($sheet->getEvent())
                ) {
                    return new JsonResponse($this->createJsonResponseData(
                        true,
                        true,
                        $this->renderView('EventBundle:MeetingRequest/Button:createRequest.html.twig', [
                            'sheet' => $sheet,
                            'toSheet' => $toSheet,
                            'isPhoneValidationRequired' => false,
                        ])
                    ));
                } else {
                    // Otherwise, response with the message saying the meeting requests are closed
                    return $this->displayWarningClosedMeetingRequest();
                }
            }
        }

        return null;
    }

    /**
     * @param Request                  $request
     * @param MeetingRequest           $meetingRequest
     * @param Sheet                    $sheet
     * @param null                     $unApprovedForm
     *
     * @return null|JsonResponse
     */
    private function unApproveFormOnEdit(
        Request &$request,
        MeetingRequest &$meetingRequest,
        Sheet &$sheet,
        &$unApprovedForm = null
    ) {
        if ($this->permissionManager->isAllowedToUnApprove($meetingRequest, $sheet)) {
            $unApprovedRequest = new UnApproveMeetingRequest($this->getUser(), $meetingRequest, $sheet);
            $unApprovedForm = $this->createForm(UnApproveMeetingRequestType::class, $unApprovedRequest, [
                'action' => $this->generateUrl('event_meeting_request_edit', [
                    'sheet' => $sheet->getId(),
                    'meetingRequest' => $meetingRequest->getId(),
                ]),
            ]);

            if ($unApprovedForm->handleRequest($request)->isSubmitted() && $unApprovedForm->isValid()) {
                $this->commandBus->handle($unApprovedRequest);

                return new JsonResponse($this->createJsonResponseData(
                    true,
                    true,
                    $this->renderView('EventBundle:MeetingRequest/Button:approveRefuseRequestButton.html.twig', [
                        'meetingRequest' => $meetingRequest,
                        'sheet' => $sheet,
                        'isPhoneValidationRequired' => false,
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
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return JsonResponse|Response
     */
    public function editRequestAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        MeetingRequest $meetingRequest
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not allowed method');
        }

        if (!$this->permissionManager->isAllowedToEditSentOrApproved($meetingRequest, $sheet)) {
            throw $this->createNotFoundException('You are not allowed to edit this meeting request.');
        }

        $priorityNumberAvailable = $this->meetingRequestCounter->getCountSheetPriorityAvailable($sheet);

        /** @var DiscussionMeetingRequestView $discussion */
        $discussion = $this
            ->queryBus
            ->handle(new DiscussionMeetingRequestViewQuery($meetingRequest, $request->getLocale()));

        $isProposition = $meetingRequest->isReceiver($sheet);
        $form = null;
        $cancelForm = null;
        $unApprovedForm = null;
        $cancelResponse = $this->cancelFormOnEdit(
            $request,
            $meetingRequest,
            $sheet,
            $cancelForm
        );
        $unapprovedResponse = $this->unApproveFormOnEdit(
            $request,
            $meetingRequest,
            $sheet,
            $unApprovedForm
        );

        if (null !== $cancelResponse) {
            return $cancelResponse;
        }

        if (null !== $unapprovedResponse) {
            return $unapprovedResponse;
        }

        if ($this->displayEditForm($discussion, $sheet)) {
            $command = new UpdateMeetingRequest($meetingRequest, $sheet);
            $form = $this->createForm(MeetingRequestUpdateType::class, $command, [
                'sheet' => $sheet,
                'locale' => $request->getLocale(),
                'show_description' => !$discussion->hasMessageOfSheet($sheet),
                'action' => $this->generateUrl('event_meeting_request_edit', [
                    'sheet' => $sheet->getId(),
                    'meetingRequest' => $meetingRequest->getId(),
                ]),
                'meetingRequest' => $meetingRequest,
                'priorityNumberAvailable' => $priorityNumberAvailable,
            ]);

            $isSubmitted = $form->handleRequest($request)->isSubmitted();
            if ($isSubmitted && $form->isValid()) {
                $this->commandBus->handle($command);

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
                        'isProposition' => $isProposition,
                        'meetingRequest' => $meetingRequest,
                    ]),
                    $this->getParticipantsHtml($command->participants, $request->getLocale())
                ));
            } elseif ($isSubmitted && !$form->isValid()) {
                return new JsonResponse($this->createJsonResponseData(
                    false,
                    false,
                    $this->renderView('EventBundle:MeetingRequest:editRequest.html.twig', [
                        'discussion' => $discussion,
                        'form' => $form->createView(),
                        'cancelForm' => $cancelForm instanceof FormInterface ? $cancelForm->createView() : null,
                        'unApprovedForm' => $unApprovedForm instanceof FormInterface ? $unApprovedForm->createView() : null,
                        'isProposition' => $isProposition,
                        'meetingRequest' => $meetingRequest,
                        'sheet' => $sheet,
                        'priorityNumberAvailable' => $priorityNumberAvailable
                    ])
                ));
            }
        }

        return $this->render('EventBundle:MeetingRequest:editRequest.html.twig', [
            'discussion' => $discussion,
            'form' => $form instanceof FormInterface ? $form->createView() : $form,
            'cancelForm' => $cancelForm instanceof FormInterface ? $cancelForm->createView() : null,
            'unApprovedForm' => $unApprovedForm instanceof FormInterface ? $unApprovedForm->createView() : null,
            'isProposition' => $isProposition,
            'meetingRequest' => $meetingRequest,
            'sheet' => $sheet,
            'priorityNumberAvailable' => $priorityNumberAvailable
        ]);
    }

    /**
     * The form should not be initiated and display if the current sheet has
     * already sent a message and has only one participant
     *
     * @param DiscussionMeetingRequestView $discussion
     * @param Sheet                        $sheet
     *
     * @return bool
     */
    private function displayEditForm(DiscussionMeetingRequestView $discussion, Sheet $sheet)
    {
        return !$discussion->hasMessageOfSheet($sheet) || 1 < $sheet->countParticipants();
    }

    private function createSearchForm(
        Event $event,
        Sheet $sheet,
        string $locale,
        array $filters,
        array $typeViews,
        array $categoryViews,
        bool $filterAvailableSlot = false,
        MeetingSlot $specificSlot = null
    ): FormInterface {
        return $this->formFactory->createNamed('', SearchType::class, $filters, [
            'action' => $this->generateUrl('event_meeting_list_request', [
                'sheet' => $sheet->getId(),
            ]),
            'event' => $event,
            'filterAvailableSlot' => $filterAvailableSlot,
            'label' => null,
            'locale' => $locale,
            'specificSlot' => $specificSlot,
            'typeViews' => $typeViews,
            'categoryViews' => $categoryViews,
        ]);
    }

    private function getParticipantsHtml(array $participants, string $locale): string
    {
        $participants = array_map(function (Participant $participant) use ($locale) {
            return $this->participantInfoGuesser
                ->guessParticipantCompleteName($participant, $locale);
        },
            $participants
        );

        return $this->renderView('EventBundle:MeetingRequest:participantsList.html.twig', [
            'participants' => $participants,
        ]);
    }
}
