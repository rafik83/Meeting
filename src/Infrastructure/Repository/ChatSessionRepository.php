<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
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

    public function add(ChatSession $chatSession): ChatSession
    {
        $this->entityManager->persist($chatSession);
        $this->entityManager->flush();

        return $chatSession;
    }

    public function update():void
    {
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
    public function findSessionsByEventAndUser(Event $event, User $user): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('
                other AS otherUser,
                NEW \DateTimeImmutable(MAX(message.createdAt)) AS latestMessageDate,
                COUNT(message) AS messagesCount,
                session.unreadMessages')
            ->from(ChatSession::class, 'session')
            ->join('session.fromUser', 'fromUser')
            ->join('session.toUser', 'toUser')
            ->join(User::class, 'other', 'WITH', 'other = (CASE WHEN fromUser=:user THEN toUser ELSE fromUser END)')
            ->leftJoin(ChatMessage::class, 'message', 'WITH', 'message.objectId=session.id AND message.objectType=:objectType')
            ->addGroupBy('session.id')
            ->setParameter('objectType', ChatMessage::TYPE_PRIVATE_CHAT)
            ->where('session.event = :event')
            ->setParameter('event', $event)
            ->andWhere('session.fromUser = :user OR session.toUser = :user')
            ->setParameter('user', $user)
            ->having('messagesCount > 0')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function findIdsByEventAndUser(Event $event, User $user): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('session.id')
            ->from(ChatSession::class, 'session')
            ->join('session.fromUser', 'fromUser')
            ->join('session.toUser', 'toUser')
            ->where('session.event = :event')
            ->setParameter('event', $event)
            ->andWhere('session.fromUser = :user OR session.toUser = :user')
            ->setParameter('user', $user)
        ;

        return array_column($queryBuilder->getQuery()->getArrayResult(), 'id');
    }

    public function findIds(Event $event, User $user): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('session.id')
            ->from(ChatSession::class, 'session')
            ->join('session.fromUser', 'fromUser')
            ->join('session.toUser', 'toUser')
            ->where('session.event = :event')
            ->setParameter('event', $event)
            ->andWhere('session.fromUser = :user OR session.toUser = :user')
            ->setParameter('user', $user)
        ;

        return array_column($queryBuilder->getQuery()->getArrayResult(), 'id');
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

    public function hasMessageFromUser(ChatSession $chatSession, User $user): bool {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('session')
            ->from(ChatSession::class, 'session')
            ->join(ChatMessage::class, 'message', 'WITH',
                'message.objectId = session.id AND message.objectType = :type AND message.createdBy = :user')
            ->setParameter('type', ChatMessage::TYPE_PRIVATE_CHAT)
            ->setParameter('user', $user)
            ->where('session.id = :id')
            ->setParameter('id', $chatSession)
            ->setMaxResults(1);

        return !empty($queryBuilder->getQuery()->getArrayResult());
    }

    public function hasAStartedVisio (Event $event, User $user): bool
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('session.id')
            ->from(ChatSession::class, 'session')
            ->andWhere('session.visioStartedAt IS NOT NULL')
            ->andWhere('session.fromUser = :user OR session.toUser = :user')
            ->setParameter('user', $user)
            ->andWhere('session.event = :event')
            ->setParameter('event', $event)
            ->setMaxResults(1);

        return !empty($queryBuilder->getQuery()->getOneOrNullResult());
    }

    public function countCallVisioByEvent(Event $event): int
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('COUNT(session)')
            ->from(ChatSession::class, 'session')
            ->andWhere('session.event = :event')
            ->setParameter('event', $event)
            ->andWhere('session.visioStartedAt IS NOT NULL')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getSingleScalarResult();

    }

}
