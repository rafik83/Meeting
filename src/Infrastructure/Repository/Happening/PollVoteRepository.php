<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Happening;

use Doctrine\ORM\EntityManagerInterface;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollChoiceResult;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\Happening\PollChoice;
use Proximum\Vimeet\Domain\Model\Happening\PollVote;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\PollVoteRepositoryInterface;

class PollVoteRepository implements PollVoteRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getResults(Poll $poll): array
    {
        return array_map(
            fn(PollChoice $pollChoice) => new PollChoiceResult($pollChoice, $this->countVoteByPollChoice($pollChoice)),
            $poll->getPollChoices()->toArray()
        );
    }

    public function add(PollVote $pollVote): void
    {
        $this->entityManager->persist($pollVote);
        $this->entityManager->flush();
    }

    public function countVoteByPollChoice(PollChoice $pollChoice): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('count(vote.user)')
            ->from(PollVote::class, 'vote')
            ->where('vote.pollChoice = :choice')
            ->setParameter('choice', $pollChoice)
        ;

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function hasUserVoted(Poll $poll, User $user): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('1')
            ->from(PollVote::class, 'vote')
            ->join('vote.pollChoice', 'pollChoice')
            ->join('pollChoice.poll', 'poll')
            ->where('poll = :poll')
            ->andWhere('vote.user = :user')
            ->setMaxResults(1)
            ->setParameter('poll', $poll)
            ->setParameter('user', $user)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult() !== null;
    }

    public function hasVotes(Poll $poll): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('1')
            ->from(PollVote::class, 'vote')
            ->join('vote.pollChoice', 'pollChoice')
            ->join('pollChoice.poll', 'poll')
            ->where('poll = :poll')
            ->setMaxResults(1)
            ->setParameter('poll', $poll)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult() !== null;
    }

    public function countVotingUsers(Poll $poll): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('count(distinct vote.user)')
            ->from(PollVote::class, 'vote')
            ->join('vote.pollChoice', 'choice')
            ->where('choice.poll = :poll')
            ->setParameter('poll', $poll)
        ;

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
