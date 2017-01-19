<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Command\Meeting\UpdateSpot;
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
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($meeting->getFromSheet()->getEvent() !== $event) {
            return new JsonResponse('Meeting are not on this event', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ('POST' === $request->getMethod()) {
            return $this->handleUpdateSpotAction($request, $event, $meeting);
        }

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
    private function handleUpdateSpotAction(Request $request, Event $event, Meeting $meeting)
    {
        $data = json_decode($request->getContent());

        if (!isset($data->spotId) || !isset($data->blockedSlot) || !isset($data->blockedSpot)) {
            throw $this->createNotFoundException();
        }

        $spot = $this->get('vimeet_infrastructure.repository.spot_repository')->find(
            $event,
            $data->spotId
        );

        $updateSpot = new UpdateSpot(
            $meeting,
            $spot,
            $data->blockedSlot,
            $data->blockedSpot
        );

        $this->get('tactician.commandbus')->handle($updateSpot);

        return new JsonResponse();
    }
}
