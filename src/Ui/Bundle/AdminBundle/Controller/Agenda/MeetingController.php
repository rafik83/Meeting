<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Command\Meeting\Admin\RemoveMeetingViewQuery;
use Proximum\Vimeet\Application\Command\Meeting\Admin\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSpot;
use Proximum\Vimeet\Application\Exception\Meeting\BlockedSpotNotAvailableForThisMeetingAndSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSpotException;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SlotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SpotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSlotAvailableException;
use Proximum\Vimeet\Application\Exception\Slot\LockedException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSpotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
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

        $isVisio = $this->checkVisio($meeting->getRequest());

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

        $spot = $this->get('vimeet_infrastructure.repository.spot_repository')->find(
            $event,
            (int) $data->spotId
        );

        if (null === $spot) {
            return $this->createErrorJsonResponse('admin.agenda.meeting.updateSpot.selectedSpotNotExists');
        }

        $visio = $this->checkVisio($meeting->getRequest());
        dump($visio);

        $updateSpot = new UpdateSpot(
            $meeting,
            $spot,
            $data->blockedSlot,
            $data->blockedSpot,
            $visio
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
            new MeetingUpdateSlotViewQuery($meeting)
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

        $visio = $this->checkVisio($meeting->getRequest());

        $updateSlot = new UpdateSlot($meeting, $slot, $visio);

        try {
            $this->get('tactician.commandbus')->handle($updateSlot);
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
     * @param Request $request
     * @param Event   $event
     * @param Meeting\Request $meetingRequest
     *
     * @return JsonResponse
     */
    public function transformRequestIntoMeetingAction(Request $request, Event $event, Meeting\Request $meetingRequest)
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
        }

        return new JsonResponse($requestSlotView);
    }

    /**
     * @param Request $request
     * @param Event   $event
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
                'admin.agenda.meeting.updateSlot.error'
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
            $this->get('tactician.commandbus.query')->handle(new RemoveMeetingViewQuery($meeting, $this->getUser()));
        } catch (LockedException $lockedException) {
            $response->setData($lockedException->getMessage());
            $response->setStatusCode(Response::HTTP_LOCKED);
        }

        return $response;
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
     * @param Meeting\Request $meetingRequest
     *
     * @return bool
     */
    private function checkVisio(Meeting\Request $meetingRequest)
    {
        $visioGuesser = $this->get('domain.meeting.visio_guesser');

        if ($meetingRequest->getMeeting() !== null) {
            return $visioGuesser->hasMeetingParticipantVisio($meetingRequest->getMeeting());
        }

        return $visioGuesser->hasMeetingRequestParticipantVisio($meetingRequest);
    }
}
