<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoConferenceController extends AbstractController
{
    private PreviousEvaluationCheckerHandler $previousEvaluationCheckerHandler;
    private EndVisioRedirectHandler $endVisioRedirectHandler;
    private VideoConferenceAdapterInterface $videoConferenceAdapter;
    private DateTimeInterface $dateTime;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        PreviousEvaluationCheckerHandler $previousEvaluationCheckerHandler,
        EndVisioRedirectHandler $endVisioRedirectHandler,
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        DateTimeInterface $dateTime,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->previousEvaluationCheckerHandler = $previousEvaluationCheckerHandler;
        $this->endVisioRedirectHandler = $endVisioRedirectHandler;
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->dateTime = $dateTime;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

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

        $redirectResponse = ($this->previousEvaluationCheckerHandler)(
            new PreviousEvaluationChecker(
                $event,
                $sheet,
                $user,
                $meeting,
                $request->getUri(),
            ));

        if ($redirectResponse instanceof RedirectResponse) {
            return $redirectResponse;
        }

        /** @var VideoConferenceView $videoConferenceView */
        $videoConferenceView = $this->commandBus->handle(
            new RequestAccess(
                $meeting,
                $participant,
                $event->getAvailableLocale($request->getLocale())
            )
        );

        /** @var MeetingView $meetingView */
        $meetingView = $this->queryBus->handle(
            new MeetingViewQuery($meeting, $sheet, false, $userDomain->getUser(), $event, $request->getLocale())
        );

        $endRedirectLink = ($this->endVisioRedirectHandler)(new EndVisioRedirect(
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
                'currentTime' => $this->dateTime->getTimestamp(),
                'endRedirectLink' => $endRedirectLink,
            ]
        );
    }

    /**
     * Opened page to create a session to test the Video Conference feature
     */
    public function createSessionVideoTestAction(EventDomain $eventDomain): RedirectResponse
    {
        $sessionId = $this->videoConferenceAdapter->createSession();

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
            $videoConferenceView = $this->commandBus->handle(
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
