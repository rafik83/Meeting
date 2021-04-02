<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\VideoConference\RequestAccess;
use Proximum\Vimeet\Application\Command\VideoConference\RequestTestAccess;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;
use Proximum\Vimeet\Application\Query\Agenda\MeetingViewQuery;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\VideoMeetingAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Evaluation\PreviousEvaluationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Evaluation\PreviousEvaluationCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio\EndVisioRedirect;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio\EndVisioRedirectHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoConferenceController extends Controller
{
    public function videoMeetingAction(
        Request $request,
        UserDomain $userDomain,
        EventDomain $eventDomain,
        Sheet $sheet,
        Participant $participant,
        Meeting $meeting
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(VideoMeetingAccessVoter::PERMISSION, $meeting);

        if (!$meeting->getSpot()->isVisio()) {
            throw $this->createAccessDeniedException('Meeting is not visio');
        }

        $user = $userDomain->getUser();

        if (false === $sheet->hasParticipant($participant)
            || false === $sheet->hasUser($user)
            || ($meeting->getToSheet() !== $sheet && $sheet !== $meeting->getFromSheet())
        ) {
            throw $this->createAccessDeniedException('Meeting is not accessible');
        }

        $event = $eventDomain->getEvent();

        $redirectResponse = ($this->get(PreviousEvaluationCheckerHandler::class))(
            new PreviousEvaluationChecker(
                $event,
                $sheet,
                $user,
                $meeting,
                $request->getRequestUri(),
            ));

        if ($redirectResponse instanceof RedirectResponse) {
            return $redirectResponse;
        }

        /** @var VideoConferenceView $videoConferenceView */
        $videoConferenceView = $this->get('tactician.commandbus')->handle(
            new RequestAccess(
                $meeting,
                $participant,
                $event->getAvailableLocale($request->getLocale())
            )
        );

        /** @var MeetingView $meetingView */
        $meetingView = $this->get('tactician.commandbus')->handle(
            new MeetingViewQuery($meeting, $sheet, false, $userDomain->getUser(), $event, $request->getLocale())
        );

        $endRedirectLink = ($this->get(EndVisioRedirectHandler::class))(new EndVisioRedirect(
            $sheet,
            $participant,
            $meeting
        ));

        return $this->render(
            'EventBundle:VideoConference:videoConference.html.twig',
            [
                'event' => $event,
                'sheet' => $sheet,
                'participant' => $participant,
                'userCompleteName' => $user->getAccount()->getCompleteName(),
                'currentUserId' => $user->getId(),
                'meeting' => $meeting,
                'videoConferenceView' => $videoConferenceView,
                'meetingView' => $meetingView,
                'currentTime' => $this->get('datetime')->getTimestamp(),
                'endRedirectLink' => $endRedirectLink,
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
     */
    public function accessSessionVideoTestAction(
        Request $request,
        EventDomain $eventDomain,
        ?UserDomain $userDomain,
        string $sessionId
    ): Response {
        if ('' === $sessionId) {
            return $this->redirectToRoute('event_video_conference_create_session_test');
        }

        $event = $eventDomain->getEvent();
        $userCompleteName = null !== $userDomain ? $userDomain->getUser()->getAccount()->getCompleteName() : 'N/A';

        try {
            /** @var VideoConferenceView $videoConferenceView */
            $videoConferenceView = $this->get('tactician.commandbus')->handle(
                new RequestTestAccess(
                    $event,
                    $sessionId,
                    $event->getAvailableLocale($request->getLocale())
                )
            );
        } catch (InvalidTokenGeneratorArgumentsException $exception) {
            throw $this->createNotFoundException('The sessionId is not valid');
        }

        return $this->render('EventBundle:VideoConference:videoConference.html.twig', [
            'event' => $event,
            'userCompleteName' => $userCompleteName,
            'videoConferenceView' => $videoConferenceView,
        ]);
    }
}
