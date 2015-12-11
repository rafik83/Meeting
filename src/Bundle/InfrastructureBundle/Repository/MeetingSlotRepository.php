<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class MeetingSlotRepository implements MeetingSlotRepositoryInterface
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

    public function findAvailableSlotIdByParticipantsIds(array $ids)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('slot.id')
            ->from(MeetingSlot::class, 'slot', 'slot.id');

        // Participants have not already a meeting at this slot
        $queryBuilder
            ->andWhere('NOT EXISTS (SELECT m.id FROM Entity:Meeting m LEFT JOIN m.fromParticipants fp LEFT JOIN m.toParticipants tp WHERE (fp.id IN (:ids) OR tp.id IN (:ids)) AND m.slot = slot)');

        // Participants have not unavailability during this slot
        $queryBuilder
            ->andWhere('NOT EXISTS (SELECT u.id FROM Entity:Unavailability u WHERE u.participant IN (:ids) AND (u.begin BETWEEN slot.begin AND slot.end OR u.end BETWEEN slot.begin AND slot.end))');

        // Participants have not blocking partipicipation
        $queryBuilder
            ->andWhere('NOT EXISTS (SELECT hp.id FROM Entity:HappeningParticipation hp JOIN hp.happening h WHERE hp.participant IN (:ids) AND (h.begin BETWEEN slot.begin AND slot.end OR h.end BETWEEN slot.begin AND slot.end))');

        $queryBuilder->setParameter('ids', $ids);

        return array_keys($queryBuilder->getQuery()->getResult());
    }
}
