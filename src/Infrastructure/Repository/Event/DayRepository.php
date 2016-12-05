<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;

class DayRepository implements DayRepositoryInterface
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
    public function add(Day $day)
    {
        $this->entityManager->persist($day);
        $this->entityManager->flush($day);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFromEvent(Event $event)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(Day::class, 'day')
            ->where('day.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->execute()
        ;

        $this->entityManager->flush();
    }
}
