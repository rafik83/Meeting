<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Poll;

class PollResultsView
{
    /** @var PollChoiceResultView[] */
    public array $choiceResults;

    public function __construct(array $choiceResults)
    {
        $this->choiceResults = $choiceResults;
    }
}
