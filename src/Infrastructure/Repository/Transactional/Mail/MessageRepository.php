<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Transactional\Mail;

use Doctrine\ORM\EntityManagerInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
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
}
