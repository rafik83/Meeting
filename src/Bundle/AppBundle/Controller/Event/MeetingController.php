<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
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
}
