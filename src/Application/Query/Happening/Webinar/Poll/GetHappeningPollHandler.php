<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Poll;

use Doctrine\Common\Collections\Collection;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Domain\Model\Happening\PollChoice;
use Proximum\Vimeet\Domain\Repository\Happening\PollVoteRepositoryInterface;

class GetHappeningPollHandler
{
    private GetHappeningPollResultsHandler $getHappeningPollResultsHandler;
    private NotificationSubscriberInterface $notificationSubscriber;
    private PollVoteRepositoryInterface $pollVoteRepository;

    public function __construct(
        GetHappeningPollResultsHandler $getHappeningPollResultsHandler,
        NotificationSubscriberInterface $notificationSubscriber,
        PollVoteRepositoryInterface $pollVoteRepository
    ) {
        $this->getHappeningPollResultsHandler = $getHappeningPollResultsHandler;
        $this->notificationSubscriber = $notificationSubscriber;
        $this->pollVoteRepository = $pollVoteRepository;
    }

    public function handle(GetHappeningPoll $query): PollView
    {
        $poll = $query->poll;

        $pollResults = null;
        $totalVotes = 0;

        $canVote = !$query->addResults;
        $resultsSubscriptionKey = null;

        if ($query->addResults) {
            $pollResults = $this->getHappeningPollResultsHandler->handle(new GetHappeningPollResults($poll));

            $totalVotes = $this->pollVoteRepository->countVotingUsers($poll);

            $resultsSubscriptionKey = $this->notificationSubscriber->getPollResultsSubscriberKey($poll);
        }

        return new PollView(
            $poll->getId(),
            $poll->getTitle(),
            $this->createChoiceViews($poll->getPollChoices(), $totalVotes, $pollResults->choiceResults ?? null),
            $poll->isMultipleChoice(),
            $poll->getStatus(),
            $totalVotes,
            $canVote,
            $resultsSubscriptionKey,
            $this->pollVoteRepository->hasVotes($poll)
        );
    }

    /**
     * @param PollChoiceResult[]|null $choiceResults
     */
    private function createChoiceViews(Collection $choices, int $totalVotes, ?array $choiceResults): array
    {
        if ($choiceResults !== null && $totalVotes > 0) {
            $indexedChoiceResults = array_reduce(
                $choiceResults,
                static function ($carry, $choiceResult) use ($totalVotes) {
                    $carry[$choiceResult->id] = (int)round($choiceResult->count / $totalVotes * 100);

                    return $carry;
                },
                []
            );
        }

        return $choices->map(
            fn(PollChoice $choice) => new PollChoiceView(
                $choice->getId(),
                $choice->getContent(),
                $indexedChoiceResults[$choice->getId()] ?? null
            )
        )->toArray()
            ;
    }
}
