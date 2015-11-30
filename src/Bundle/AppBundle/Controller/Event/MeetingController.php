<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use DateTime;
use Proximum\Vimeet\Application\Command\Meeting\ApprovedRequest;
use Proximum\Vimeet\Application\Command\Meeting\CreateRequest;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\CategoryView;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MeetingController extends BaseController
{
    /**
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function listRequestAction(EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        $meetingRequestRepository = $this->get('vimeet_infrastructure.repository.meeting.request_repository');
        $meetingRequest           = $meetingRequestRepository->getRequestSentBySheet($sheet);

        $requestViews = $this
            ->get('vimeet_infrastructure.application.components.meeting.request_views_builder')
            ->generate($meetingRequest);

        return $this->render('VimeetAppBundle:Event/Meeting:listRequest.html.twig', [
            'eventView'     => $eventView,
            'sheet'         => $sheet,
            'request_views' => $requestViews,
        ]);
    }

    /**
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function listPropositionAction(EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        $meetingRequestRepository = $this->get('vimeet_infrastructure.repository.meeting.request_repository');
        $meetingProposition       = $meetingRequestRepository->getPropositionReceivedBySheet($sheet);

        $propositionViews = $this
            ->get('vimeet_infrastructure.application.components.meeting.request_views_builder')
            ->generate($meetingProposition);

        return $this->render('VimeetAppBundle:Event/Meeting:listProposition.html.twig', [
            'eventView'         => $eventView,
            'sheet'             => $sheet,
            'proposition_views' => $propositionViews,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView    $eventView
     * @param CategoryView $categoryView
     * @param Sheet        $to
     * @param Sheet        $from
     *
     * @return RedirectResponse|Response
     */
    public function createRequestAction(
        Request $request,
        EventView $eventView,
        CategoryView $categoryView,
        Sheet $to,
        Sheet $from
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($from->getParticipants());
        $this->get('vimeet_infrastructure.application.components.sheet.manager')->isAllowedToRequestMeeting($to, $from);

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        $createRequest = new CreateRequest($from, $to, new DateTime);
        $form          = $this->createForm('meeting_request_create', $createRequest, [
            'sheet' => $from
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.meeting.create_request_handler')
                ->handle($createRequest);

            $this->addFlash('success', 'flash.meeting_request.create.success');

            return $this->redirectToRoute('event_catalog_category', [
                'subdomain'    => $request->attributes->get('subdomain'),
                'categoryView' => $categoryView->id,
            ]);
        }
        $fromName = $sheetInfoGuesser->guessSheetInfo($from);
        $toName   = $sheetInfoGuesser->guessSheetInfo($to);

        return $this->render('VimeetAppBundle:Event/Meeting:createRequest.html.twig', [
            'eventView' => $eventView,
            'fromName'  => $fromName,
            'toName'    => $toName,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param EventView $eventView
     * @param Sheet $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return RedirectResponse|Response
     */
    public function approvedRequestAction(
        Request $request,
        EventView $eventView,
        Sheet $sheet,
        MeetingRequest $meetingRequest
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($meetingRequest->getTo()->getParticipants());
        $this->isAllowedToUpdateMeetingRequest($meetingRequest);

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        $approvedRequest = new ApprovedRequest($meetingRequest);
        $form          = $this->createForm('meeting_request_approved', $approvedRequest, [
            'sheet' => $meetingRequest->getTo()
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.meeting.approved_request_handler')
                ->handle($approvedRequest);

            $this->addFlash('success', 'flash.meeting_request.approved.success');

            return $this->redirectToRoute('event_meeting_list_proposition', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
            ]);
        }
        $fromName = $sheetInfoGuesser->guessSheetInfo($meetingRequest->getFrom());
        $toName   = $sheetInfoGuesser->guessSheetInfo($meetingRequest->getTo());

        return $this->render('VimeetAppBundle:Event/Meeting:approvedRequest.html.twig', [
            'eventView' => $eventView,
            'fromName'  => $fromName,
            'toName'    => $toName,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param MeetingRequest $meetingRequest
     *
     * @throws AccessDeniedException
     */
    private function isAllowedToUpdateMeetingRequest(MeetingRequest $meetingRequest)
    {
        if ($meetingRequest->getState() !== MeetingRequest::STATE_SENT) {
            throw new AccessDeniedException('You can not access this meeting request');
        }
    }
}
