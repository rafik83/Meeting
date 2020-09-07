<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
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

    public function add(ChatMessageVote $chatMessageVote)
    {
        $this->entityManager->persist($chatMessageVote);
        $this->entityManager->flush();
    }

    public function remove(ChatMessageVote $chatMessageVote)
    {
        $this->entityManager->remove($chatMessageVote);
        $this->entityManager->flush();
    }

    public function getByChatMessageAndUser(ChatMessage $chatMessage, User $user, string $type): ?ChatMessageVote
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('vote')
            ->from(ChatMessageVote::class, 'vote')
            ->where('vote.chatMessage = :message')
            ->andWhere('vote.user = :user')
            ->andWhere('vote.type = :type')
            ->setParameter('message', $chatMessage)
            ->setParameter('user', $user)
            ->setParameter('type', $type);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
