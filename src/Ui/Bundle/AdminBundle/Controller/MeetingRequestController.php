<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\MeetingRequest\RequestsToMeetings;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\MeetingRequest\FilterMeetingRequestType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\View\Meeting\AdminShowDetailsView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MeetingRequestController extends Controller
{
    /**
     * @param string $type
     * @param array  $data
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
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $filters    = [];
        $filtered   = false;
        $filterForm = $this->createFilterForm(
            FilterMeetingRequestType::class,
            ['state' => $request->query->get('state')]
        );

        // The form is not valid because of the parameters page sent
        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filters  = $filterForm->getData();
            $filtered = true;
        }

        $meetingRequests = $this
            ->get('vimeet_infrastructure.repository.meeting.request_repository')
            ->findByEventAndFilterByState($event, $request->query->getInt('page', 1), 20, $locale, $filters);

        $filterFormView = $filterForm->createView();

        return $this->render('AdminBundle:MeetingRequest:list.html.twig', [
            'event'            => $event,
            'meeting_requests' => $meetingRequests,
            'filter_form'      => $filterFormView,
            'filters_summary'  => $this->get('filter_summary')->getFilters($filterFormView, $filters, $locale),
            'filtered'         => $filtered,
        ]);
    }

    /**
     * @param Request        $request
     * @param Event          $event
     * @param MeetingRequest $meetingRequest
     *
     * @return Response
     */
    public function showDetailAction(Request $request, Event $event, MeetingRequest $meetingRequest)
    {
        $locale = $event->getAvailableLocale($request->getLocale());

        $messages = $this->get('vimeet_infrastructure.repository.meeting.message_repository')
            ->getMessagesByMeetingRequest($meetingRequest);

        $meetingRequestView = new AdminShowDetailsView(
            $meetingRequest->getId(),
            $meetingRequest->getFromSheet()->getId(),
            $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
                ->guessSheetTitle($meetingRequest->getFromSheet(), $locale),
            $meetingRequest->getToSheet()->getId(),
            $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
                ->guessSheetTitle($meetingRequest->getToSheet(), $locale),
            array_map(
                function (Participant $participant) use ($locale) {
                    return $this->get('template.participant_info_guesser')
                        ->guessParticipantCompleteName($participant, $locale);
                },
                $meetingRequest->getFromParticipants()->toArray()
            ),
            array_map(
                function (Participant $participant) use ($locale) {
                    return $this->get('template.participant_info_guesser')
                        ->guessParticipantCompleteName($participant, $locale);
                },
                $meetingRequest->getToParticipants()->toArray()
            ),
            $messages,
            $meetingRequest->getState(),
            $meetingRequest->getCreatedAt(),
            $meetingRequest->getStateUpdatedAt()
        );

        return $this->render('AdminBundle:MeetingRequest:details.html.twig', [
            'event'              => $event,
            'meetingRequestView' => $meetingRequestView,
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

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function sheetListAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $sheetMeetingsListViews = $this
            ->get('sheet.sheet_meetings_list_view_factory')
            ->findAll($event, $locale);

        $meetingsMetrics = $this
            ->get('sheet.meetings_metrics_view_factory')
            ->getFromSheets($sheetMeetingsListViews);

        return $this->render('AdminBundle:MeetingRequest:sheet_list.html.twig', [
            'event'            => $event,
            'sheets'           => $sheetMeetingsListViews,
            'meetings_metrics' => $meetingsMetrics,
        ]);
    }

    /**
     * @param Event $event
     *
     * @return RedirectResponse
     */
    public function transformRequestsToMeetingsAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $requestsToMeetings = new RequestsToMeetings($event, new \DateTime());
        $this->get('tactician.commandbus')->handle($requestsToMeetings);
        $this->addFlash('success', 'flash.admin.meeting_request.position.success');

        return $this->redirectToRoute('admin_meeting_request_sheets_list', ['event' => $event->getId()]);
    }
}
