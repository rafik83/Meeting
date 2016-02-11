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
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\MeetingRequest\FilterMeetingRequestType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\MeetingRequest\PositionMeetingType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\View\Meeting\AdminShowDetailsView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MeetingRequestController extends Controller
{
    /**
     * @param string $type
     * @param string $data
     * @param array  $options
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, $options);
    }


    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $filter     = [];
        $filtered   = false;
        $filterForm = $this->createFilterForm(
            FilterMeetingRequestType::class,
            ['state' => $request->query->get('state')]
        );

        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filter   = $filterForm->getData();
            $filtered = true;
        }

        $meetingRequests = $this
            ->get('vimeet_infrastructure.repository.meeting.request_repository')
            ->findByEventAndFilterByState($event, $request->query->getInt('page', 1), 20, $filter);

        return $this->render('VimeetAppBundle:Admin/MeetingRequest:list.html.twig', [
            'event'            => $event,
            'meeting_requests' => $meetingRequests,
            'totalRequest'     => $meetingRequests->getTotalItemCount(),
            'filter_form'      => $filterForm->createView(),
            'filtered'         => $filtered,
        ]);
    }


    /**
     * @param Event          $event
     * @param MeetingRequest $meetingRequest
     *
     * @return Response
     */
    public function showDetailAction(Event $event, MeetingRequest $meetingRequest)
    {
        $messages = $this->get('vimeet_infrastructure.repository.meeting.message_repository')
            ->getMessagesByMeetingRequest($meetingRequest);

        $meetingRequestView = new AdminShowDetailsView(
            $meetingRequest->getId(),
            $meetingRequest->getFromSheet()->getId(),
            $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')->guessSheetInfo($meetingRequest->getFromSheet()),
            $meetingRequest->getToSheet()->getId(),
            $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')->guessSheetInfo($meetingRequest->getToSheet()),
            array_map(function (Participant $participant) {
                return $this->get('vimeet_infrastructure.application.components.sheet.participant_info_guesser')->guessParticipantInfo($participant);
            }, $meetingRequest->getFromParticipants()->toArray()),
            array_map(function (Participant $participant) {
                return $this->get('vimeet_infrastructure.application.components.sheet.participant_info_guesser')->guessParticipantInfo($participant);
            }, $meetingRequest->getToParticipants()->toArray()),
            $messages,
            $meetingRequest->getState(),
            $meetingRequest->getCreatedAt(),
            $meetingRequest->getStateUpdatedAt()
        );

        return $this->render('VimeetAppBundle:Admin/MeetingRequest:details.html.twig', [
            'event'              => $event,
            'meetingRequestView' => $meetingRequestView,
        ]);
    }

    /**
     * @param Request        $request
     * @param Event          $event
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function positionAction(Request $request, Event $event, MeetingRequest $meetingRequest)
    {
        if ($meetingRequest->getState() !== MeetingRequest::STATE_APPROVED) {
            throw new NotFoundHttpException();
        }

        $command = new PositionMeeting($meetingRequest, new \DateTime);
        $form    = $this->createForm(PositionMeetingType::class, $command, [
            'event'           => $event,
            'meeting_request' => $meetingRequest,
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.meeting_request.position_meeting_handler')
                ->handle($command);
            $this->addFlash('success', 'flash.admin.meeting_request.position.success');

            return $this->redirectToRoute('admin_meeting_request_list', ['event' => $event->getId()]);
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
