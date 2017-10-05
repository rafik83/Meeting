<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\VideoConference\RequestAccess;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\HappeningAccessVoter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\MeetingAccessVoter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\VideoMeetingAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class VideoConferenceController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     * @param Meeting     $meeting
     *
     * @return JsonResponse
     */
    public function requestAccessAction(EventDomain $eventDomain, Meeting $meeting): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(HappeningAccessVoter::PERMISSION, $eventDomain->getEvent());
        $this->denyAccessUnlessGranted(MeetingAccessVoter::PERMISSION, $meeting);
        $this->denyAccessUnlessGranted(VideoMeetingAccessVoter::PERMISSION, $meeting);

        if (!$meeting->getSpot()->isVisio()) {
            return new JsonResponse('Meeting is not visio', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $videoConferenceView = $this->get('tactician.commandbus')->handle(
                new RequestAccess($meeting, $this->getUser())
            );
        } catch (InvalidTokenGeneratorArgumentsException $exception) {
            return new JsonResponse('Invalid token generator arguments', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse($videoConferenceView);
    }

    /**
     * Opened page to test the Video Conference feature
     *
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function testAction(EventDomain $eventDomain): Response
    {
        return $this->render('EventBundle:VideoConference:test.html.twig', [
            'event' => $eventDomain->getEvent(),
        ]);
    }
}
