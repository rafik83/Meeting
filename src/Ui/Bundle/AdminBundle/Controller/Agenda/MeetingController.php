<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSpot;
use Proximum\Vimeet\Application\Exception\Meeting\BlockedSpotNotAvailableForThisMeetingAndSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSpotException;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SlotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SpotNotAvailableForThisMeetingException;
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
     * @param Request $request
     * @param Event   $event
     * @param Meeting $meeting
     *
     * @return JsonResponse
     */
    public function updateSpotAction(Request $request, Event $event, Meeting $meeting)
    {
        $this->checkAccess($event, $meeting);

        $meetingUpdateSpotView = $this->get('query.agenda.admin.meeting_update_spot_view_query_handler')->handle(
            new MeetingUpdateSpotViewQuery($meeting)
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

        $updateSpot = new UpdateSpot(
            $meeting,
            $spot,
            $data->blockedSlot,
            $data->blockedSpot
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
     * @param Request $request
     * @param Event   $event
     * @param Meeting $meeting
     *
     * @return JsonResponse
     */
    public function updateSlotAction(Request $request, Event $event, Meeting $meeting)
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

        $updateSlot = new UpdateSlot($meeting, $slot);

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

        $requestSlotView = $this->get('query.agenda.admin.request_slot_view_query_handler')->handle(
            new RequestSlotViewQuery($meetingRequest)
        );

        return new JsonResponse($requestSlotView);
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
}
