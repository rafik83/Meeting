<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Happening;

use Predis\Client;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollChoiceResult;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\Happening\PollChoice;
use Proximum\Vimeet\Domain\Model\Happening\PollVote;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\PollVoteRepositoryInterface;

class CachedPollVoteRepository implements PollVoteRepositoryInterface
{
    public const RESULT_COUNT_TTL = 2;

    private PollVoteRepository $pollVoteRepository;
    private Client $client;

    public function __construct(PollVoteRepository $pollVoteRepository, Client $client)
    {
        $this->pollVoteRepository = $pollVoteRepository;
        $this->client = $client;
    }

    public function getResults(Poll $poll): array
    {
        return array_map(
            fn(PollChoice $pollChoice) => new PollChoiceResult($pollChoice, $this->countVoteByPollChoice($pollChoice)),
            $poll->getPollChoices()->toArray()
        );
    }

    public function countVoteByPollChoice(PollChoice $pollChoice): int
    {
        $choiceCount = $this->client->get($this->getPollChoiceVoteKey($pollChoice));

        if ($choiceCount === null) {
            $choiceCount = $this->saveInRedisVoteCount($pollChoice);
        }

        return $choiceCount;
    }

    public function add(PollVote $pollVote): void
    {
        $this->pollVoteRepository->add($pollVote);

        $this->saveInRedisVoteCount($pollVote->getPollChoice());
    }

    public function hasVotes(Poll $poll): bool
    {
       return $this->pollVoteRepository->hasVotes($poll);
    }

    private function getPollChoiceVoteKey(PollChoice $pollChoice): string
    {
        return 'vimeet:poll-choice:' . $pollChoice->getId() . ':count';
    }

    private function saveInRedisVoteCount(PollChoice $pollChoice): int
    {
        $choiceCount = $this->pollVoteRepository->countVoteByPollChoice($pollChoice);

        $key = $this->getPollChoiceVoteKey($pollChoice);
        $this->client->set($key, $choiceCount);
        $this->client->expire($key, self::RESULT_COUNT_TTL);

        return $choiceCount;
    }

    public function hasUserVoted(Poll $poll, User $user): bool
    {
        return $this->pollVoteRepository->hasUserVoted($poll, $user);
    }

    public function countVotingUsers(Poll $poll): int
    {
        return $this->pollVoteRepository->countVotingUsers($poll);
    }
}
