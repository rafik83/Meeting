<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;

class ChatSessionRepository implements ChatSessionRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(ChatSession $chatSession)
    {
        $this->entityManager->persist($chatSession);
        $this->entityManager->flush();
    }

    public function findOneByEventAndUsers(Event $event, User $aUser, User $anotherUser): ?ChatSession
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('session')
            ->from('Entity:ChatSession', 'session')
            ->where('session.event = :event')
            ->andWhere('
                (session.fromUser = :aUser AND session.toUser = :anotherUser)
            OR
                (session.fromUser = :anotherUser AND session.toUser = :aUser)
            ')
            ->setParameter('event', $event)
            ->setParameter('aUser', $aUser)
            ->setParameter('anotherUser', $anotherUser);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findOneById(int $id): ?ChatSession
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('session')
            ->from('Entity:ChatSession', 'session')
            ->where('session.id = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
