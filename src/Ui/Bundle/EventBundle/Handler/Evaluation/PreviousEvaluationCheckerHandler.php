<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Evaluation;

use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\PreviousHappeningEvaluationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\PreviousHappeningEvaluationCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio\PreviousMeetingEvaluationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio\PreviousMeetingEvaluationCheckerHandler;

class PreviousEvaluationCheckerHandler
{
    private PreviousHappeningEvaluationCheckerHandler $previousHappeningEvaluationCheckerHandler;

    private PreviousMeetingEvaluationCheckerHandler $previousMeetingEvaluationCheckerHandler;

    public function __construct(
        PreviousHappeningEvaluationCheckerHandler $previousHappeningEvaluationCheckerHandler,
        PreviousMeetingEvaluationCheckerHandler $previousMeetingEvaluationCheckerHandler
    ) {
        $this->previousHappeningEvaluationCheckerHandler = $previousHappeningEvaluationCheckerHandler;
        $this->previousMeetingEvaluationCheckerHandler = $previousMeetingEvaluationCheckerHandler;
    }

    public function __invoke(PreviousEvaluationChecker $previousEvaluationChecker)
    {
        $resultPreviousHappeningEvaluationChecker = ($this->previousHappeningEvaluationCheckerHandler)(
            new PreviousHappeningEvaluationChecker(
                $previousEvaluationChecker->event,
                $previousEvaluationChecker->sheet,
                $previousEvaluationChecker->user,
                $previousEvaluationChecker->timeRange,
                $previousEvaluationChecker->origin
            )
        );

        if ($resultPreviousHappeningEvaluationChecker !== null) {
            return $resultPreviousHappeningEvaluationChecker;
        }

        return ($this->previousMeetingEvaluationCheckerHandler)(
            new PreviousMeetingEvaluationChecker(
                $previousEvaluationChecker->origin,
                $previousEvaluationChecker->event,
                $previousEvaluationChecker->sheet,
                $previousEvaluationChecker->user,
                $previousEvaluationChecker->timeRange
            )
        );
    }
}
