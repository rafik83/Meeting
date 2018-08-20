<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\Scan;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;

class ScanRepository implements ScanRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /** @param EntityManager $entityManager */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(Scan $scan): void
    {
        $this->entityManager->persist($scan);
        $this->entityManager->flush($scan);
    }

    public function isUserCheckinTodayByEvent(User $user, Event $event, \DateTimeInterface $dateTime): bool
    {
        $begin = (new \DateTime())
            ->setTimestamp($dateTime->getTimestamp())
            ->setTime(0, 0, 0);

        $end = (new \DateTime())
            ->setTimestamp($dateTime->getTimestamp())
            ->setTime(23, 59, 59);

        return
            (int) $this->entityManager->createQueryBuilder()
                ->select('count(scan.id)')
                ->from(Scan::class, 'scan')
                ->where('scan.event = :event')
                ->andWhere('scan.user = :user')
                ->andWhere('scan.scannedAt >= :startAt and scan.scannedAt <= :endAt')
                ->setParameters([
                    'event' => $event,
                    'user' => $user,
                    'startAt' => $begin,
                    'endAt' => $end
                ])
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }
}
