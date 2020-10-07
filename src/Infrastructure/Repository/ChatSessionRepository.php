<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\ChatMessage;
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
            ->from(ChatSession::class, 'session')
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

    /**
     * @inheritDoc
     */
    public function findByEventAndUser(Event $event, User $user): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('session.id AS sessionId,
                CASE WHEN fromUser=:user THEN toUser ELSE fromUser END AS otherUserId,
                NEW \DateTimeImmutable(MAX(message.createdAt)) AS latestMessageDate,
                COUNT(message) as messagesCount')
            ->from(ChatSession::class, 'session')
            ->join('session.fromUser', 'fromUser')
            ->join('session.toUser', 'toUser')
            ->leftJoin(ChatMessage::class, 'message', 'WITH', 'message.objectId=session.id AND message.objectType=:objectType')
            ->addGroupBy('session.id')
            ->setParameter('objectType', ChatSession::OBJECT_TYPE)
            ->where('session.event = :event')
            ->setParameter('event', $event)
            ->andWhere('session.fromUser = :user OR session.toUser = :user')
            ->setParameter('user', $user)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function findOneById(int $id): ?ChatSession
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('session')
            ->from(ChatSession::class, 'session')
            ->where('session.id = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
