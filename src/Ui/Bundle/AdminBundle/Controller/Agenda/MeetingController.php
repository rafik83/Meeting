<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\UpdateParticipants;
use Proximum\Vimeet\Application\Command\Meeting\Admin\RemoveMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Admin\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSpot;
use Proximum\Vimeet\Application\Command\Unavailability\MassAssignment\Update;
use Proximum\Vimeet\Application\Exception\MeetingRequest\InvalidParticipantException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSlotAvailableException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSpotAvailableException;
use Proximum\Vimeet\Application\Exception\Meeting\BlockedSpotNotAvailableForThisMeetingAndSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSpotException;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SlotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SpotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Exception\Slot\LockedException;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOnMeetingException;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOutOfMassSlotException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSpotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSpotViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Request\MeetingRequestListViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Request\RequestSheetsViewQuery;
use Proximum\Vimeet\Application\Query\MassAssignment\MassAssignmentViewQuery;
use Proximum\Vimeet\Domain\Meeting\VisioGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\MassAssignment\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MeetingController extends AbstractController
{
    private VisioGuesser $meetingVisioGuesser;
    private MeetingUpdateSpotViewQueryHandler $meetingUpdateSpotViewQueryHandler;
    private SpotRepositoryInterface $spotRepository;
    private MeetingUpdateSlotViewQueryHandler $meetingUpdateSlotViewQueryHandler;
    private MeetingSlotRepositoryInterface $meetingSlotRepository;
    private RequestRepositoryInterface $meetingRequestRepository;
    private SheetRepositoryInterface $sheetRepository;
    private ParticipantRepositoryInterface $participantRepository;
    private RequestSlotViewQueryHandler $requestSlotViewQueryHandler;
    private TranslatorInterface $translator;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        VisioGuesser $meetingVisioGuesser,
        MeetingUpdateSpotViewQueryHandler $meetingUpdateSpotViewQueryHandler,
        SpotRepositoryInterface $spotRepository,
        MeetingUpdateSlotViewQueryHandler $meetingUpdateSlotViewQueryHandler,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        RequestRepositoryInterface $meetingRequestRepository,
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        RequestSlotViewQueryHandler $requestSlotViewQueryHandler,
        TranslatorInterface $translator,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->meetingVisioGuesser = $meetingVisioGuesser;
        $this->meetingUpdateSpotViewQueryHandler = $meetingUpdateSpotViewQueryHandler;
        $this->spotRepository = $spotRepository;
        $this->meetingUpdateSlotViewQueryHandler = $meetingUpdateSlotViewQueryHandler;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->meetingRequestRepository = $meetingRequestRepository;
        $this->sheetRepository = $sheetRepository;
        $this->participantRepository = $participantRepository;
        $this->requestSlotViewQueryHandler = $requestSlotViewQueryHandler;
        $this->translator = $translator;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function updateSpotAction(Request $request, Event $event, Meeting $meeting, Sheet $sheet): JsonResponse
    {
        $this->checkAccess($event, $meeting);

        $isVisio = $this->meetingVisioGuesser->hasMeetingParticipantVisio($meeting);

        $meetingUpdateSpotView = $this->meetingUpdateSpotViewQueryHandler->handle(
            new MeetingUpdateSpotViewQuery($meeting, $sheet, $isVisio, $event->getAvailableLocale($request->getLocale()))
        );

        return new JsonResponse($meetingUpdateSpotView);
    }

    public function handleUpdateSpotAction(Request $request, Event $event, Meeting $meeting, Sheet $sheet): JsonResponse
    {
        $this->checkAccess($event, $meeting);

        if (!$meeting->hasSheet($sheet)) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent());

        if (!isset($data->spotId)
            || !isset($data->slotId)
            || !isset($data->blockedSlot)
            || !isset($data->blockedSpot)
            || !isset($data->meetingParticipants)
            || !is_array($data->meetingParticipants)
            || count($data->meetingParticipants) === 0
        ) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSpot.error');
        }

        $isVisio = $this->meetingVisioGuesser->hasMeetingParticipantVisio($meeting);
        $spot = $this->spotRepository->find(
            $event,
            (int) $data->spotId,
            $isVisio // find visio spot if meeting visio
        );

        $slot = $this->meetingSlotRepository->find(
            $event,
            (int) $data->slotId
        );

        if (null === $spot) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSpot.selectedSpotNotExists');
        }

        if (null === $slot) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSpot.selectedSlotNotExists');
        }

        if ($slot->getEvent() !== $event) {
            throw new AccessDeniedException();
        }

        $participants = $this->participantRepository->findByIds($data->meetingParticipants);
        foreach ($participants as $participant) {
            if (!$sheet->hasParticipant($participant)) {
                throw new AccessDeniedException();
            }
        }

        $updateSpot = new UpdateSpot(
            $meeting,
            $spot,
            $data->blockedSlot,
            $data->blockedSpot,
            $isVisio,
            $sheet,
            $slot,
            $participants
        );

        try {
            $this->commandBus->handle($updateSpot);
        } catch (SpotNotAvailableForThisMeetingException $exception) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSpot.spotNotAvailableForThisMeeting');
        } catch (MeetingIsBlockedSpotException $exception) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSpot.isBlockedSpot');
        } catch (\Exception $exception) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSlot.error');
        }

        return new JsonResponse();
    }

    public function updateSlotAction(Event $event, Meeting $meeting): JsonResponse
    {
        $this->checkAccess($event, $meeting);

        $meetingUpdateSlotView = $this->meetingUpdateSlotViewQueryHandler->handle(
            new MeetingUpdateSlotViewQuery(
                $meeting,
                $this->checkVisio($meeting->getRequest())
            )
        );

        return new JsonResponse($meetingUpdateSlotView);
    }

    public function handleUpdateSlotAction(Request $request, Event $event, Meeting $meeting): JsonResponse
    {
        $this->checkAccess($event, $meeting);

        $data = json_decode($request->getContent());

        if (!isset($data->slotId)) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSlot.error');
        }

        $slot = $this->meetingSlotRepository->find(
            $event,
            (int) $data->slotId
        );

        if (null === $slot) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSlot.selectedSlotNotExists');
        }

        $visio = $this->meetingVisioGuesser->hasMeetingParticipantVisio($meeting);

        $updateSlot = new UpdateSlot($meeting, $slot, $visio);

        try {
            $this->commandBus->handle($updateSlot);

            $meeting->getRequest()->setUpdateOrDeleteReasonMessage(null);
            $this->meetingRequestRepository
                ->set($meeting->getRequest());
        } catch (BlockedSpotNotAvailableForThisMeetingAndSlotException $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.meeting.updateSlot.blockedSpotNotAvailableForThisMeetingAndSlot'
            );
        } catch (MeetingIsBlockedSlotException $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.meeting.updateSlot.meetingIsBlockedSlot'
            );
        } catch (NoSpotsAvailableForThisSlotAndMeetingException $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.meeting.updateSlot.noSpotsAvailableForThisSlotAndMeeting'
            );
        } catch (SlotNotAvailableForThisMeetingException $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.meeting.updateSlot.slotNotAvailableForThisMeeting'
            );
        } catch (\Exception $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.meeting.updateSlot.error'
            );
        }

        return new JsonResponse();
    }

    /**
     * @param Event           $event
     * @param Meeting\Request $meetingRequest
     *
     * @return JsonResponse
     */
    public function transformRequestIntoMeetingAction(Event $event, Meeting\Request $meetingRequest): JsonResponse
    {
        $this->checkMeetingRequestAccess($event, $meetingRequest);

        $isVisio = $this->checkVisio($meetingRequest);

        try {
            $requestSlotView = $this->requestSlotViewQueryHandler->handle(
                new RequestSlotViewQuery($meetingRequest, $isVisio)
            );
        } catch (NoSlotAvailableException $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.request.transformIntoMeeting.noSlotAvailable'
            );
        } catch (NoSpotAvailableException $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.request.transformIntoMeeting.noSpotAvailable'
            );
        } catch (\Exception $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.request.transformIntoMeeting.error'
            );
        }

        return new JsonResponse($requestSlotView);
    }

    /**
     * This method returns the participants and sheet concerned by the given meeting request
     */
    public function getParticipantsOfRequestAction(Request $request, Event $event, Meeting\Request $meetingRequest): JsonResponse
    {
        $this->checkMeetingRequestAccess($event, $meetingRequest);

        $requestSheetsView = $this->queryBus->handle(
            new RequestSheetsViewQuery($meetingRequest, $event->getAvailableLocale($request->getLocale()))
        );

        return new JsonResponse($requestSheetsView);
    }

    /**
     * @param Request         $request
     * @param Event           $event
     * @param Meeting\Request $meetingRequest
     *
     * @return JsonResponse
     */
    public function updateParticipantsOfRequestAction(Request $request, Event $event, Meeting\Request $meetingRequest): JsonResponse
    {
        $this->checkMeetingRequestAccess($event, $meetingRequest);

        $data = json_decode($request->getContent(), true);

        if (!isset($data['fromParticipants']) || !isset($data['toParticipants'])) {
            return $this->createErrorJsonResponse('admin.agenda.request.updateParticipant.invalidArguments');
        }

        try {
            $this->commandBus->handle(
                new UpdateParticipants($meetingRequest, $data['fromParticipants'], $data['toParticipants'])
            );
        } catch (InvalidParticipantException $exception) {
            return $this->createErrorJsonResponse('admin.agenda.request.updateParticipant.invalidArguments');
        }

        $meetingRequestListFrom = $this->queryBus->handle(new MeetingRequestListViewQuery($meetingRequest->getFromSheet(), $event->getAvailableLocale($request->getLocale())));
        $meetingRequestListTo   = $this->queryBus->handle(new MeetingRequestListViewQuery($meetingRequest->getToSheet(), $event->getAvailableLocale($request->getLocale())));

        return new JsonResponse([
            $meetingRequestListFrom,
            $meetingRequestListTo,
        ]);
    }

    public function handleTransformRequestIntoMeetingAction(
        Request $request,
        Event $event,
        Meeting\Request $meetingRequest
    ): JsonResponse {
        $this->checkMeetingRequestAccess($event, $meetingRequest);

        $data = json_decode($request->getContent());

        if (!isset($data->slotId)) {
            return $this->createErrorJsonResponse('admin.agenda.request.transformIntoMeeting.error');
        }

        $slot = $this->meetingSlotRepository->find(
            $event,
            (int) $data->slotId
        );

        if (null === $slot) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.transformIntoMeeting.selectedSpotNotExists');
        }

        $isVisio = $this->checkVisio($meetingRequest);

        $transformRequestIntoMeeting = new TransformRequestIntoMeeting($meetingRequest, $slot, $isVisio);

        try {
            $this->commandBus->handle($transformRequestIntoMeeting);
        } catch (NoSlotAvailableException $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.request.transformIntoMeeting.noSlotAvailable'
            );
        } catch (NoSpotsAvailableForThisSlotAndMeetingException $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.meeting.updateSlot.noSpotsAvailableForThisSlotAndMeeting'
            );
        } catch (SlotNotAvailableForThisMeetingException $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.meeting.updateSlot.slotNotAvailableForThisMeeting'
            );
        } catch (\Exception $exception) {
            return $this->createErrorJsonResponse(
                'admin.agenda.request.transformIntoMeeting.error'
            );
        }

        return new JsonResponse();
    }

    private function checkAccess(Event $event, Meeting $meeting): void
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($meeting->getFromSheet()->getEvent() !== $event) {
            throw new \InvalidArgumentException('Meeting are not on this event');
        }
    }

    /**
     * @param Event   $event
     * @param Meeting $meeting
     *
     * @return JsonResponse
     */
    public function removeAction(Event $event, Meeting $meeting): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($meeting->getFromSheet()->getEvent() !== $event) {
            return new JsonResponse('Meeting are not on this event', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $response = new JsonResponse();

        try {
            $this->commandBus->handle(new RemoveMeeting($meeting, $this->getUser()));
        } catch (LockedException $lockedException) {
            $response->setData($lockedException->getMessage());
            $response->setStatusCode(Response::HTTP_LOCKED);
        }

        return $response;
    }

    public function massAssignmentDetailAction(Request $request, Event $event, MassAssignment $massAssignment): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $userSheetsUserCount = $this->sheetRepository
            ->countSheetsByUserAndEvent($massAssignment->getUser(), $event)
        ;

        if ($userSheetsUserCount === 0) {
            throw $this->createNotFoundException('Event inconsistency');
        }

        $massAssignmentView = $this->queryBus->handle(
            new MassAssignmentViewQuery(
                $massAssignment,
                $event,
                $event->getAvailableLocale($request->getLocale())
            )
        );

        return new JsonResponse($massAssignmentView);
    }

    public function updateMassAssignmentAction(Request $request, Event $event, MassAssignment $massAssignment): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if (!$request->request->has('begin') || !$request->request->has('end') || !$request->request->has('enabled')) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateMassAssignment.missingData');
        }

        $userSheetsUserCount = $this->sheetRepository
            ->countSheetsByUserAndEvent($massAssignment->getUser(), $event)
        ;

        if ($userSheetsUserCount === 0) {
            throw $this->createNotFoundException('Event inconsistency');
        }

        $update = new Update($massAssignment);

        $form = $this->createForm(UpdateType::class, $update, [
            'method' => 'POST',
            'event'  => $event,
        ]);

        $form->handleRequest($request)->submit([
            'begin'   => $request->request->get('begin'),
            'end'     => $request->request->get('end'),
            'enabled' => 'true' === $request->request->get('enabled'),
        ]);

        try {
            $this->commandBus->handle($update);
        } catch (MassAssignmentOnMeetingException $exception) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateMassAssignment.meetingOrHappeningOnSlot');
        } catch (MassAssignmentOutOfMassSlotException $exception) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateMassAssignment.outOfMassSlot');
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function checkMeetingRequestAccess(Event $event, Meeting\Request $meetingRequest): void
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($meetingRequest->getFromSheet()->getEvent() !== $event) {
            throw new \InvalidArgumentException('Meeting are not on this event');
        }
    }

    private function createErrorJsonResponse(string $key): JsonResponse
    {
        return new JsonResponse($this->translator->trans($key), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Check if meeting or meeting request has some participant in visio
     *
     * @see VisioGuesser
     */
    private function checkVisio(Meeting\Request $meetingRequest): bool
    {
        if ($meetingRequest->hasMeeting()) {
            return $this->meetingVisioGuesser->hasMeetingParticipantVisio($meetingRequest->getMeeting());
        }

        return $this->meetingVisioGuesser->hasMeetingRequestParticipantVisio($meetingRequest);
    }
}
