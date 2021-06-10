<?php

namespace Proximum\Vimeet\Domain\Repository\Happening;

use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollChoiceResult;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\Happening\PollChoice;
use Proximum\Vimeet\Domain\Model\Happening\PollVote;
use Proximum\Vimeet\Domain\Model\User;

interface PollVoteRepositoryInterface
{
    /**
     * @param Poll $poll
     *
     * @return PollChoiceResult[]
     */
    public function getResults(Poll $poll): array;

    public function countVoteByPollChoice(PollChoice $pollChoice): int;

    public function add(PollVote $pollVote): void;

    public function hasUserVoted(Poll $poll, User $user): bool;

    public function hasVotes(Poll $poll): bool;

    public function countVotingUsers(Poll $poll): int;
}
