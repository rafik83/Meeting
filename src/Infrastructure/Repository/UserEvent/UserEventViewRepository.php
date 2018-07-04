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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\UserEvent\UserEventViewRepositoryInterface;

class UserEventViewRepository implements UserEventViewRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getByEvent(Event $event): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('
                sheet.id sheetId,
                owner.id ownerId,
                owner.email ownerEmail,
                owner.account.firstName ownerFirstName,
                owner.account.lastName ownerLastName,
                owner.locale ownerLocale,
                user.id userId,
                user.email userEmail,
                user.account.firstName userFirstName,
                user.account.lastName userLastName,
                user.locale userLocale
            ')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.owner', 'owner', 'WITH', 'sheet.event = :event')
            ->join('sheet.participants', 'participant')
            ->join('participant.user', 'user')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }
}
