<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Transactional\Mail;

use Doctrine\ORM\EntityManagerInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;

class MessageRepository implements MessageRepositoryInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(Message $message): void
    {
        $this->entityManager->persist($message);
        $this->entityManager->flush($message);
    }

    public function update(Message $message): void
    {
        $this->entityManager->flush($message);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndType(Event $event, string $transactionalMailType): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('message')
            ->from(Message::class, 'message')
            ->where('message.event = :event')
            ->andWhere('message.type = :type')
            ->setParameter('event', $event)
            ->setParameter('type', $transactionalMailType)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getOneByEventAndType(Event $event, string $transactionalMailType): ?Message
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('message')
            ->from(Message::class, 'message')
            ->where('message.event = :event')
            ->andWhere('message.type = :type')
            ->setParameter('event', $event)
            ->setParameter('type', $transactionalMailType)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function getOneByEventAndTypeAndAssociatedType(
        Event $event,
        string $transactionalMailType,
        Type $associatedType
    ): ?Message
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('message')
            ->from(Message::class, 'message')
            ->join(
                'message.associatedParticipationTypes',
                'associatedParticipationType',
                'WITH',
                'message.event = :event AND message.type = :type AND associatedParticipationType = :associatedType'
            )
            ->setParameter('event', $event)
            ->setParameter('type', $transactionalMailType)
            ->setParameter('associatedType', $associatedType)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param Event $event
     *
     * @return Message[]
     */
    public function findByEvent(Event $event): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('message')
            ->from(Message::class, 'message')
            ->where('message.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
            ;
    }

    public function remove(Message $message): void
    {
        $this->entityManager->remove($message);
        $this->entityManager->flush($message);
    }
}
