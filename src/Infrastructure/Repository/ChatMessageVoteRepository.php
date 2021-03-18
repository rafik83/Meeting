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
            ->getArrayResult()
        ;

        return array_column($votesCount, 'count', 'type');
    }

    public function getVotesByUser(string $chatLinkableObjectType, int $chatLinkableObjectId, User $user): array
    {
        $votes = $this->entityManager
            ->createQueryBuilder()
            ->select('vote.type, chatMessage.id AS messageId')
            ->from(ChatMessageVote::class, 'vote')
            ->join('vote.chatMessage', 'chatMessage', 'WITH', 'chatMessage.objectType=:objectType AND chatMessage.objectId=:objectId')
            ->setParameter('objectType', $chatLinkableObjectType)
            ->setParameter('objectId', $chatLinkableObjectId)
            ->where('vote.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult()
        ;

        return array_column($votes, 'type', 'messageId');
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
