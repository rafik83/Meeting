<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\VideoConference\ConnectSession;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoConferenceController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Meeting     $meeting
     *
     * @return JsonResponse
     */
    public function requestAccessAction(Request $request, EventDomain $eventDomain, Meeting $meeting)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        if (!$meeting->getSpot()->isVisio()) {
            return new JsonResponse('Meeting is not visio', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$request->isXmlHttpRequest()) {
            throw new AccessDeniedException();
        }

        $videoConferenceView = $this->get('tactician.commandbus')->handle(
            new ConnectSession($meeting, $this->getUser())
        );

        return new JsonResponse($videoConferenceView);
    }
}
