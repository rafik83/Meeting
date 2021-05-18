<?php

namespace Proximum\Vimeet\Application\Event\Meeting;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * When a user has submitted an evaluation, he can change it as much as he wants.
 * We consider after a certain amount of time without update that the evaluation is complete.
 * This is when this event occurs.
 */
class MeetingEvaluationUpdateExpiredEvent extends SymfonyEvent
{
    private Meeting $meeting;
    private User $user;
    private Sheet $evaluatingSheet;
    private int $evaluation;
    private ?string $locale;

    public function __construct(
        Meeting $meeting,
        User $user,
        Sheet $evaluatingSheet,
        int $evaluation,
        ?string $locale = null
    ) {
        $this->meeting = $meeting;
        $this->user = $user;
        $this->evaluatingSheet = $evaluatingSheet;
        $this->evaluation = $evaluation;
        $this->locale = $locale;
    }

    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }

    public function getEvent(): Event
    {
        return $this->meeting->getEvent();
    }

    /**
     * User from the sheet that has been evaluated
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * The sheet that made an evaluation
     */
    public function getEvaluatingSheet(): Sheet
    {
        return $this->evaluatingSheet;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getEvaluation(): int
    {
        return $this->evaluation;
    }
}
