<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\MeetingRequest\PositionMeeting;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\MeetingRequest\PositionMeetingType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class MeetingRequestController extends Controller
{
    public function listAction(Request $request, Event $event)
    {
        $meetingRequests = $this
            ->get('vimeet_infrastructure.repository.meeting.request_repository')
            ->getPendingByEvent($event, $request->query->getInt('page', 1), 20);

        return $this->render('VimeetAppBundle:Admin/MeetingRequest:list.html.twig', [
            'event'            => $event,
            'meeting_requests' => $meetingRequests,
        ]);
    }

    /**
     * @ParamConverter(
     *   "meetingRequest",
     *   class="Proximum\Vimeet\Domain\Model\Meeting\Request",
     *   options={"id" = "request_id"}
     * )
     *
     * @param Request        $request
     * @param Event          $event
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function positionAction(Request $request, Event $event, MeetingRequest $meetingRequest)
    {
        $command = new PositionMeeting($meetingRequest);
        $form    = $this->createForm(PositionMeetingType::class, $command, [
            'event'           => $event,
            'meeting_request' => $meetingRequest,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {

            $this
                ->get('vimeet_infrastructure.vimeet.application.command.meeting_request.position_meeting_handler')
                ->handle($command);


            return $this->redirectToRoute('admin_meeting_request_list', ['id' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/MeetingRequest:position.html.twig', [
            'event'           => $event,
            'meeting_request' => $meetingRequest,
            'form'            => $form->createView(),
        ]);
    }
}
