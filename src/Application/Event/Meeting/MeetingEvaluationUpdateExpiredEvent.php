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
    private Sheet $evaluatingSheet;
    private User $evaluatingUser;
    private int $evaluation;
    private ?string $locale;

    public function __construct(
        Meeting $meeting,
        Sheet $evaluatingSheet,
        User $evaluatingUser,
        int $evaluation,
        ?string $locale = null
    ) {
        $this->meeting = $meeting;
        $this->evaluatingUser = $evaluatingUser;
        $this->evaluatingSheet = $evaluatingSheet;
        $this->evaluation = $evaluation;
        $this->locale = $locale ?? $meeting->getSheetMet($this->evaluatingSheet)->getOwnerLocale();
    }

    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }

    public function getEvent(): Event
    {
        return $this->meeting->getEvent();
    }

    public function getEvaluatingUser(): User
    {
        return $this->evaluatingUser;
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
