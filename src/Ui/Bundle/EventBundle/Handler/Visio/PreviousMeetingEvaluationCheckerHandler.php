<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;

class PreviousMeetingEvaluationCheckerHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var ContactRepositoryInterface */
    private $contactRepository;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        RouterInterface $router,
        FlashBagInterface $flashBag,
        MeetingRepositoryInterface $meetingRepository,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->contactRepository = $contactRepository;
        $this->flashBag = $flashBag;
        $this->router = $router;
    }

    public function __invoke(PreviousMeetingEvaluationChecker $previousMeetingEvaluationChecker): ?RedirectResponse
    {
        $event = $previousMeetingEvaluationChecker->event;
        $user = $previousMeetingEvaluationChecker->user;
        $sheet = $previousMeetingEvaluationChecker->sheet;
        $type = $sheet->getType();

        if (!$type->mustEvaluateMeeting()) {
            return null;
        }

        $participant = $sheet->getUserParticipant($user) ?? $sheet->getFirstParticipant();

        $begin = $previousMeetingEvaluationChecker->meeting->getSlot()->getBegin();
        $meeting = $this->meetingRepository->getPreviousVisioMeeting(
            $event,
            $sheet,
            $participant,
            $begin
        );

        if (!$meeting instanceof Meeting) {
            return null;
        }

        $metParticipants = $meeting->getMetParticipants($sheet);

        foreach ($metParticipants as $metParticipant) {
            $hasEvaluateParticipant = $this->contactRepository->hasEvaluateContactByEventAndUser(
                $event,
                $user,
                $metParticipant->getUser()
            );

            if ($hasEvaluateParticipant) {
                continue;
            }

            $this->flashBag->add('warning', 'flash.meeting.evaluation.previous_meeting_not_evaluate.warning');

            return new RedirectResponse(
                $this->router->generate(
                    'event_meeting_evaluation',
                    [
                        'sheet' => $sheet->getId(),
                        'meeting' => $meeting->getId(),
                        'redirectTo' => $previousMeetingEvaluationChecker->origin
                    ]
                )
            );
        }

        return null;
    }
}
