<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Poll;

class PollView
{
    public int $id;
    public string $title;
    /** @var PollChoiceView[] $choices */
    public array $choices;
    public bool $multipleChoice;
    public string $status;
    public int $totalVotes;
    public bool $canVote;
    public ?string $resultsSubscriptionKey;
    /**
     * Unlike to total votes count and poll results, "has votes" is known by anyone
     */
    public bool $hasVotes;

    public function __construct(
        int $id,
        string $title,
        array $choices,
        bool $multipleChoice,
        string $status,
        int $totalVotes,
        bool $canVote,
        ?string $resultsSubscriptionKey,
        bool $hasVotes
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->choices = $choices;
        $this->multipleChoice = $multipleChoice;
        $this->status = $status;
        $this->totalVotes = $totalVotes;
        $this->canVote = $canVote;
        $this->resultsSubscriptionKey = $resultsSubscriptionKey;
        $this->hasVotes = $hasVotes;
    }
}
