<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Application\Command\Meeting\CreateRequest;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\CategoryView;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @return Response
     */
    public function createRequestAction(Request $request, EventView $eventView, CategoryView $categoryView, Sheet $to, Sheet $from)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($from->getParticipants());
        $this->get('vimeet_infrastructure.application.components.sheet.manager')->isAllowedToRequestMeeting($to, $from);

        $sheetInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        $createRequest = new CreateRequest($from, $to);
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
}
