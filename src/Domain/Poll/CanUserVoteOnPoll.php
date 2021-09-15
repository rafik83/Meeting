<?php

namespace Proximum\Vimeet\Domain\Poll;

use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\PollVoteRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class CanUserVoteOnPoll
{
    private HappeningParticipationRepositoryInterface $happeningParticipationRepository;
    private PollVoteRepositoryInterface $pollVoteRepository;

    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        PollVoteRepositoryInterface $pollVoteRepository
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->pollVoteRepository = $pollVoteRepository;
    }

    public function isSatisfiedBy(Poll $poll, User $user): bool
    {
        if ($poll->getStatus() !== Poll::STATUS_PUBLISHED) {
            return false;
        }

        $happeningParticipation = $this->happeningParticipationRepository
            ->findByHappeningAndUser($poll->getHappening(), $user);

        if (null === $happeningParticipation) {
            return false;
        }

        if ($happeningParticipation->isDisabled()) {
            return false;
        }

        if ($poll->getHappening()->hasSpeaker($user)) {
            return false;
        }

        if ($this->pollVoteRepository->hasUserVoted($poll, $user)) {
            return false;
        }

        return true;
    }
}
