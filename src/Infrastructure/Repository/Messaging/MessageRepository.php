<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Messaging;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\Messaging\MessageTranslation;
use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;

class MessageRepository implements MessageRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Message $message)
    {
        $this->entityManager->persist($message);

        foreach ($message->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(Message $message)
    {
        $this->entityManager->flush($message);

        foreach ($message->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->from(Message::class, 'message')
            ->select('message')
            ->where('message.event = :event')
            ->orderBy('message.createdAt', 'ASC')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventOrderByName(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->from(Message::class, 'message')
            ->select('message')
            ->where('message.event = :event')
            ->orderBy('message.name', 'ASC')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(MessageTranslation $messageTranslation)
    {
        $this->entityManager->remove($messageTranslation);
    }
}
