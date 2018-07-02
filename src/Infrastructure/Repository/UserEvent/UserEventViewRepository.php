<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\UserEvent;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserEvent\UserEventViewRepositoryInterface;
use Proximum\Vimeet\Domain\UserEvent\UserEventView;

class UserEventViewRepository implements UserEventViewRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return UserEventView[]
     */
    public function getByEvent(Event $event): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('new Proximum\Vimeet\Domain\UserEvent\UserEventView(:eventId, user.id, user.account.firstName, user.account.lastName, user.email)')
            ->from(User::class, 'user')
            ->join(
                Sheet::class,
                'sheet',
                'WITH',
                'sheet.event = :eventId'
            )
            ->leftJoin(Participant::class, 'participant', 'WITH', 'participant.user = user AND participant.sheet = sheet')
            ->where('sheet.owner = user OR participant.id IS NOT NULL')
            ->setParameter('eventId', $event->getId())
            ->setMaxResults(100) // @todo: to remove
            ->getQuery()
            ->getResult()
        ;
    }
}
