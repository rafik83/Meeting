<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Meeting\CreateRequest;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MeetingRequestController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $approvedRequests = $this
            ->get('vimeet_infrastructure.repository.meeting.request_repository')
            ->getRequestsByApprovedState();

        $approvedRequestViews = $this
            ->get('vimeet_infrastructure.application.components.meeting.request_views_builder')
            ->generate($approvedRequests);

        return $this->render('VimeetAppBundle:Admin/MeetingRequest:list.html.twig', [
            'event'        => $event,
            'requestViews' => $approvedRequestViews,
        ]);
    }

    /**
     * @ParamConverter(
     *   "meetingRequest",
     *   class="Proximum\Vimeet\Domain\Model\Meeting\Request",
     *   options={"id" = "meeting_request_id"}
     * )
     *
     * @param Request        $request
     * @param Event          $event
     * @param MeetingRequest $meetingRequest
     *
     * @return Response|RedirectResponse
     */
    public function addAction(Request $request, Event $event, MeetingRequest $meetingRequest)
    {
        $meetingRequestView = $this
            ->get('vimeet_infrastructure.application.components.meeting.request_view_builder')
            ->generate($meetingRequest);

        $create = new CreateRequest($meetingRequest, new \DateTime());
        $form   = $this->createForm('meeting_create', $create, [
            'method'         => 'POST',
            'action'         => $this->generateUrl('admin_meeting_add', [
                'id'                 => $event->getId(),
                'meeting_request_id' => $meetingRequest->getId()
            ]),
            'meetingRequest' => $meetingRequest,
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('admin_meeting_list', ['id' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/MeetingRequest:add.html.twig', [
            'event'       => $event,
            'requestView' => $meetingRequestView,
            'form'        => $form->createView(),
        ]);
    }
}
