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
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\MeetingRequest\PositionMeetingType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            'view_timezone'   => $this->getParameter('timezone_default'),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.meeting_request.position_meeting_handler')
                ->handle($command);
            $this->addFlash('success', 'flash.admin.meeting_request.position.success');

            return $this->redirectToRoute('admin_meeting_request_list', ['id' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/MeetingRequest:position.html.twig', [
            'event'           => $event,
            'meeting_request' => $meetingRequest,
            'form'            => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function slotsAction(Request $request)
    {
        $participants = $request->query->get('participants', []);

        $slots = $this
            ->get('vimeet_infrastructure.repository.meeting_slot_repository')
            ->findAvailableSlotIdByParticipantsIds($participants);

        return new JsonResponse($slots);
    }
}
