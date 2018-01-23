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
use Proximum\Vimeet\Application\Command\VideoConference\RequestTestAccess;
use Proximum\Vimeet\Application\Query\Agenda\MeetingViewQuery;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\MeetingAccessVoter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\VideoMeetingAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class VideoConferenceController extends Controller
{
    /**
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param Participant   $participant
     * @param Meeting       $meeting
     * @param UserInterface $user
     *
     * @return Response
     */
    public function videoMeetingAction(
        Request $request,
        UserInterface $user,
        EventDomain $eventDomain,
        Sheet $sheet,
        Participant $participant,
        Meeting $meeting
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(MeetingAccessVoter::PERMISSION, $meeting);
        $this->denyAccessUnlessGranted(VideoMeetingAccessVoter::PERMISSION, $meeting);

        if (!$meeting->getSpot()->isVisio()) {
            throw $this->createAccessDeniedException('Meeting is not visio');
        }

        $event = $eventDomain->getEvent();

        /** @var MeetingView $meetingView */
        $meetingView = $this->get('tactician.commandbus')->handle(
            new MeetingViewQuery($meeting, $sheet, false, $user, $event, $request->getLocale())
        );

        /** @var VideoConferenceView $videoConferenceView */
        $videoConferenceView = $this->get('tactician.commandbus')->handle(
            new RequestAccess($meeting, $this->getUser())
        );

        return $this->render(
            'EventBundle:VideoConference:videoConference.html.twig',
            [
                'event' => $event,
                'videoConferenceView' => $videoConferenceView,
                'meetingView' => $meetingView,
            ]
        );
    }

    /**
     * Opened page to create a session to test the Video Conference feature
     *
     * @param EventDomain $eventDomain
     *
     * @return RedirectResponse
     */
    public function createSessionVideoTestAction(EventDomain $eventDomain): RedirectResponse
    {
        $sessionId = $this->get('adapter.video_conference_adapter')->createSession();

        return $this->redirectToRoute('event_video_conference_access_session_test', ['sessionId' => $sessionId]);
    }

    /**
     * Opened page to test the Video Conference feature with a sessionId
     *
     * @param EventDomain $eventDomain
     * @param string      $sessionId
     *
     * @return Response
     */
    public function accessSessionVideoTestAction(EventDomain $eventDomain, string $sessionId): Response
    {
        /** @var VideoConferenceView $videoConferenceView */
        $videoConferenceView = $this->get('tactician.commandbus')->handle(
            new RequestTestAccess($sessionId)
        );

        return $this->render('EventBundle:VideoConference:videoConference.html.twig', [
            'event' => $eventDomain->getEvent(),
            'videoConferenceView' => $videoConferenceView,
        ]);
    }
}
