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
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSpotException;
use Proximum\Vimeet\Application\Exception\Meeting\SpotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSpotViewQuery;
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

        if (Request::METHOD_POST === $request->getMethod()) {
            return $this->handleUpdateSpotAction($request, $event, $meeting);
        }

        $meetingUpdateSpotView = $this->get('query.agenda.admin.meeting_update_slot_view_query_handler')->handle(
            new MeetingUpdateSlotViewQuery($meeting)
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

        $updateSlot = new UpdateSlot($meeting, $slot);
        dump($updateSlot);

        try {
            $this->get('tactician.commandbus')->handle($updateSlot);
        } catch (\Exception $exception) {
            return $this->createErrorJsonResponse($exception->getMessage().' - '. $exception->getFile().' - '.$exception->getLine());
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
     * @param string $key
     *
     * @return JsonResponse
     */
    private function createErrorJsonResponse($key)
    {
        return new JsonResponse($this->get('translator')->trans($key), Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
