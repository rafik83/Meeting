<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Command\Meeting\Admin\RemoveMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Admin\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSpot;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\UpdateParticipants;
use Proximum\Vimeet\Application\Command\Unavailability\MassAssignment\Update;
use Proximum\Vimeet\Application\Exception\Meeting\BlockedSpotNotAvailableForThisMeetingAndSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSpotException;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SlotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SpotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\InvalidParticipantException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSlotAvailableException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSpotAvailableException;
use Proximum\Vimeet\Application\Exception\Slot\LockedException;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOnMeetingException;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOutOfMassSlotException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSpotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Request\MeetingRequestListViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Request\RequestSheetsViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQuery;
use Proximum\Vimeet\Application\Query\MassAssignment\MassAssignmentViewQuery;
use Proximum\Vimeet\Domain\Meeting\VisioGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\MassAssignment\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MeetingController extends Controller
{
    /**
     * @param Event   $event
     * @param Meeting $meeting
     *
     * @return JsonResponse
     */
    public function updateSpotAction(Event $event, Meeting $meeting)
    {
        $this->checkAccess($event, $meeting);

        $isVisio = $this->get('domain.meeting.visio_guesser')->hasMeetingParticipantVisio($meeting);

        $meetingUpdateSpotView = $this->get('query.agenda.admin.meeting_update_spot_view_query_handler')->handle(
            new MeetingUpdateSpotViewQuery($meeting, $isVisio)
        );

        return new JsonResponse($meetingUpdateSpotView);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Meeting $meeting
     *
     * @return JsonResponse
     */
    public function handleUpdateSpotAction(Request $request, Event $event, Meeting $meeting)
    {
        $this->checkAccess($event, $meeting);

        $data = json_decode($request->getContent());

        if (!isset($data->spotId) || !isset($data->blockedSlot) || !isset($data->blockedSpot)) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSpot.error');
        }

        $isVisio = $this->get('domain.meeting.visio_guesser')->hasMeetingParticipantVisio($meeting);
        $spot = $this->get('vimeet_infrastructure.repository.spot_repository')->find(
            $event,
            (int) $data->spotId,
            $isVisio // find visio spot if meeting visio
        );

        if (null === $spot) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSpot.selectedSpotNotExists');
        }

        $updateSpot = new UpdateSpot(
            $meeting,
            $spot,
            $data->blockedSlot,
            $data->blockedSpot,
            $isVisio
        );

        try {
            $this->get('tactician.commandbus')->handle($updateSpot);
        } catch (SpotNotAvailableForThisMeetingException $exception) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSpot.spotNotAvailableForThisMeeting');
        } catch (MeetingIsBlockedSpotException $exception) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSpot.isBlockedSpot');
        } catch (\Exception $exception) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSlot.error');
        }

        return new JsonResponse();
    }

    /**
     * @param Event   $event
     * @param Meeting $meeting
     *
     * @return JsonResponse
     */
    public function updateSlotAction(Event $event, Meeting $meeting)
    {
        $this->checkAccess($event, $meeting);

        $meetingUpdateSlotView = $this->get('query.agenda.admin.meeting_update_slot_view_query_handler')->handle(
            new MeetingUpdateSlotViewQuery(
                $meeting,
                $this->checkVisio($meeting->getRequest())
            )
        );

        return new JsonResponse($meetingUpdateSlotView);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Meeting $meeting
     *
     * @return JsonResponse
     */
    public function handleUpdateSlotAction(Request $request, Event $event, Meeting $meeting)
    {
        $this->checkAccess($event, $meeting);

        $data = json_decode($request->getContent());

        if (!isset($data->slotId)) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSlot.error');
        }

        $slot = $this->get('vimeet_infrastructure.repository.meeting_slot_repository')->find(
            $event,
            (int) $data->slotId
        );

        if (null === $slot) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSlot.selectedSlotNotExists');
        }

        $visio = $this->get('domain.meeting.visio_guesser')->hasMeetingParticipantVisio($meeting);

        $updateSlot = new UpdateSlot($meeting, $slot, $visio);

        try {
            $this->get('tactician.commandbus')->handle($updateSlot);

            $meeting->getRequest()->setUpdateOrDeleteReasonMessage(null);
            $this->get('vimeet_infrastructure.repository.meeting.request_repository')
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
    public function transformRequestIntoMeetingAction(Event $event, Meeting\Request $meetingRequest)
    {
        $this->checkMeetingRequestAccess($event, $meetingRequest);

        $isVisio = $this->checkVisio($meetingRequest);

        try {
            $requestSlotView = $this->get('query.agenda.admin.request_slot_view_query_handler')->handle(
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
     *
     * @param Request         $request
     * @param Event           $event
     * @param Meeting\Request $meetingRequest
     *
     * @return JsonResponse
     */
    public function getParticipantsOfRequestAction(Request $request, Event $event, Meeting\Request $meetingRequest)
    {
        $this->checkMeetingRequestAccess($event, $meetingRequest);

        $requestSheetsView = $this->get('tactician.commandbus.query')->handle(
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
    public function updateParticipantsOfRequestAction(Request $request, Event $event, Meeting\Request $meetingRequest)
    {
        $this->checkMeetingRequestAccess($event, $meetingRequest);

        $data = json_decode($request->getContent(), true);

        if (!isset($data['fromParticipants']) || !isset($data['toParticipants'])) {
            return $this->createErrorJsonResponse('admin.agenda.request.updateParticipant.invalidArguments');
        }

        try {
            $this->get('tactician.commandbus')->handle(
                new UpdateParticipants($meetingRequest, $data['fromParticipants'], $data['toParticipants'])
            );
        } catch (InvalidParticipantException $exception) {
            return $this->createErrorJsonResponse('admin.agenda.request.updateParticipant.invalidArguments');
        }

        $meetingRequestListFrom = $this->get('tactician.commandbus.query')->handle(new MeetingRequestListViewQuery($meetingRequest->getFromSheet(), $event->getAvailableLocale($request->getLocale())));
        $meetingRequestListTo   = $this->get('tactician.commandbus.query')->handle(new MeetingRequestListViewQuery($meetingRequest->getToSheet(), $event->getAvailableLocale($request->getLocale())));

        return new JsonResponse([
            $meetingRequestListFrom,
            $meetingRequestListTo,
        ]);
    }

    /**
     * @param Request         $request
     * @param Event           $event
     * @param Meeting\Request $meetingRequest
     *
     * @return JsonResponse
     */
    public function handleTransformRequestIntoMeetingAction(
        Request $request,
        Event $event,
        Meeting\Request $meetingRequest
    ) {
        $this->checkMeetingRequestAccess($event, $meetingRequest);

        $data = json_decode($request->getContent());

        if (!isset($data->slotId)) {
            return $this->createErrorJsonResponse('admin.agenda.request.transformIntoMeeting.error');
        }

        $slot = $this->get('vimeet_infrastructure.repository.meeting_slot_repository')->find(
            $event,
            (int) $data->slotId
        );

        if (null === $slot) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.transformIntoMeeting.selectedSpotNotExists');
        }

        $isVisio = $this->checkVisio($meetingRequest);

        $transformRequestIntoMeeting = new TransformRequestIntoMeeting($meetingRequest, $slot, $isVisio);

        try {
            $this->get('tactician.commandbus')->handle($transformRequestIntoMeeting);
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

    /**
     * @param Event   $event
     * @param Meeting $meeting
     */
    private function checkAccess(Event $event, Meeting $meeting)
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
    public function removeAction(Event $event, Meeting $meeting)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($meeting->getFromSheet()->getEvent() !== $event) {
            return new JsonResponse('Meeting are not on this event', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $response = new JsonResponse();

        try {
            $this->get('tactician.commandbus.query')->handle(new RemoveMeeting($meeting, $this->getUser()));
        } catch (LockedException $lockedException) {
            $response->setData($lockedException->getMessage());
            $response->setStatusCode(Response::HTTP_LOCKED);
        }

        return $response;
    }

    /**
     * @param Request        $request
     * @param Event          $event
     * @param MassAssignment $massAssignment
     *
     * @return JsonResponse
     */
    public function massAssignmentDetailAction(Request $request, Event $event, MassAssignment $massAssignment)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $massAssignmentView = $this->get('tactician.commandbus.query')->handle(
            new MassAssignmentViewQuery(
                $massAssignment,
                $event,
                $event->getAvailableLocale($request->getLocale())
            )
        );

        return new JsonResponse($massAssignmentView);
    }

    /**
     * @param Request        $request
     * @param Event          $event
     * @param MassAssignment $massAssignment
     *
     * @return JsonResponse
     */
    public function updateMassAssignmentAction(Request $request, Event $event, MassAssignment $massAssignment)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if (!$request->request->has('begin') || !$request->request->has('end') || !$request->request->has('enabled')) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateMassAssignment.missingData');
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
            $this->get('tactician.commandbus')->handle($update);
        } catch (MassAssignmentOnMeetingException $exception) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateMassAssignment.meetingOrHappeningOnSlot');
        } catch (MassAssignmentOutOfMassSlotException $exception) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateMassAssignment.outOfMassSlot');
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Event           $event
     * @param Meeting\Request $meetingRequest
     */
    private function checkMeetingRequestAccess(Event $event, Meeting\Request $meetingRequest)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($meetingRequest->getFromSheet()->getEvent() !== $event) {
            throw new \InvalidArgumentException('Meeting are not on this event');
        }
    }

    /**
     * @param string $key
     *
     * @return JsonResponse
     */
    private function createErrorJsonResponse($key)
    {
        return new JsonResponse($this->get('translator')->trans($key), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Check if meeting or meeting request has some participant in visio
     *
     * @param Meeting\Request $meetingRequest
     *
     * @return bool
     *
     * @see VisioGuesser
     */
    private function checkVisio(Meeting\Request $meetingRequest)
    {
        $visioGuesser = $this->get('domain.meeting.visio_guesser');

        if ($meetingRequest->hasMeeting()) {
            return $visioGuesser->hasMeetingParticipantVisio($meetingRequest->getMeeting());
        }

        return $visioGuesser->hasMeetingRequestParticipantVisio($meetingRequest);
    }
}
