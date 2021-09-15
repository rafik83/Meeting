<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Exception\Happening\PollNotFoundException;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPoll;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollHandler;
use Proximum\Vimeet\Domain\Model\Happening\PollChoice;
use Proximum\Vimeet\Domain\Model\Happening\PollVote;
use Proximum\Vimeet\Domain\Poll\CanUserVoteOnPoll;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Happening\PollVoteRepositoryInterface;

class VoteHandler
{
    private PollVoteRepositoryInterface $pollVoteRepository;
    private CanUserVoteOnPoll $canUserVoteOnPoll;
    private NotificationPublisherInterface $notificationPublisher;
    private PollRepositoryInterface $pollRepository;
    private GetHappeningPollHandler $getHappeningPollHandler;

    public function __construct(
        PollVoteRepositoryInterface $pollVoteRepository,
        CanUserVoteOnPoll $canUserVoteOnPoll,
        NotificationPublisherInterface $notificationPublisher,
        PollRepositoryInterface $pollRepository,
        GetHappeningPollHandler $getHappeningPollHandler
    ) {
        $this->pollVoteRepository = $pollVoteRepository;
        $this->canUserVoteOnPoll = $canUserVoteOnPoll;
        $this->notificationPublisher = $notificationPublisher;
        $this->pollRepository = $pollRepository;
        $this->getHappeningPollHandler = $getHappeningPollHandler;
    }

    public function handle(Vote $command): VoteResultView
    {
        $poll = $this->pollRepository->findById($command->pollId);
        $user = $command->user;

        if ($poll === null) {
            throw new PollNotFoundException(sprintf('No poll with id %d', $command->pollId));
        }

        if ($command->happening->getId() !== $poll->getHappening()->getId()) {
            throw new NotAllowedVoteException($poll, $user);
        }

        if (!$this->canUserVoteOnPoll->isSatisfiedBy($poll, $user)) {
            throw new NotAllowedVoteException($poll, $user);
        }

        $votedPollChoices = array_filter(
            $poll->getPollChoicesArray(),
            static fn(PollChoice $pollChoice) => in_array($pollChoice->getId(), $command->choicesId, true)
        );

        $voteCount = count($votedPollChoices);

        $choicesId = $command->choicesId;

        if ($voteCount > 1 && !$poll->isMultipleChoice()) {
            throw new MultipleChoiceVoteNotAllowedException($poll, $user, $choicesId);
        }

        if ($voteCount !== count($choicesId)) {
            throw new PollChoicesNotRecognizedException($poll, $user, $choicesId);
        }

        if ($voteCount === 0) {
            throw new NoVoteException($poll, $user, $choicesId);
        }

        foreach ($votedPollChoices as $pollChoice) {
            $this->pollVoteRepository->add(new PollVote($pollChoice, $user));
        }

        $this->notificationPublisher->publishDelayedPollVoteNotification($poll);

        return new VoteResultView($this->getHappeningPollHandler->handle(new GetHappeningPoll($poll, true)));
    }
}
