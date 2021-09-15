<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Poll;

use Proximum\Vimeet\Domain\Model\Happening\PollChoice;

class PollChoiceResult
{
    private PollChoice $pollChoice;
    private int $voteCount;

    public function __construct(PollChoice $pollChoice, int $voteCount)
    {
        $this->pollChoice = $pollChoice;
        $this->voteCount = $voteCount;
    }

    public function getPollChoice(): PollChoice
    {
        return $this->pollChoice;
    }

    public function getVoteCount(): int
    {
        return $this->voteCount;
    }
}
