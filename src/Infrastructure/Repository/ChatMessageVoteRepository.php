<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageVote;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatMessageVoteRepositoryInterface;

class ChatMessageVoteRepository implements ChatMessageVoteRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(ChatMessageVote $chatMessageVote): void
    {
        $this->entityManager->persist($chatMessageVote);
        $this->entityManager->flush();
    }

    public function remove(ChatMessageVote $chatMessageVote): void
    {
        $this->entityManager->remove($chatMessageVote);
        $this->entityManager->flush();
    }

    public function getByChatMessageAndUser(ChatMessage $chatMessage, User $user, string $type): ?ChatMessageVote
    {
        $queryBuilder = $this->getVotesQb($chatMessage, $user);
        $queryBuilder
            ->andWhere('vote.type = :type')
            ->setParameter('type', $type);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function removeVotes(ChatMessage $chatMessage, User $user): void
    {
        $queryBuilder = $this->getVotesQb($chatMessage, $user);
        $votes = $queryBuilder->getQuery()->getResult();

        if (!count($votes)) {
            return;
        }

        foreach ($votes as $vote) {
            $this->entityManager->remove($vote);
        }
        $this->entityManager->flush();
    }

    public function getVotesCountByChatMessage(ChatMessage $chatMessage): array
    {
        $votesCount =  $this->entityManager
            ->createQueryBuilder()
            ->select('vote.type, COUNT(vote) AS count')
            ->from(ChatMessageVote::class, 'vote')
            ->where('vote.chatMessage = :message')
            ->setParameter('message', $chatMessage)
            ->addGroupBy('vote.type')
            ->getQuery()
            ->getResult()
        ;

        return array_column($votesCount, 'count', 'type');
    }

    private function getVotesQb(ChatMessage $chatMessage, User $user): QueryBuilder
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('vote')
            ->from(ChatMessageVote::class, 'vote')
            ->where('vote.chatMessage = :message')
            ->andWhere('vote.user = :user')
            ->setParameter('message', $chatMessage)
            ->setParameter('user', $user)
        ;
    }
}
