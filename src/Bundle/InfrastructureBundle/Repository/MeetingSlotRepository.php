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
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class MeetingSlotRepository implements MeetingSlotRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * MeetingSlotRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
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
            ->andWhere('NOT EXISTS (SELECT u.id FROM Entity:Unavailability u WHERE u.participant IN (:ids) AND (u.begin BETWEEN slot.begin AND slot.end OR u.end BETWEEN slot.begin AND slot.end OR slot.begin BETWEEN u.begin AND u.end OR slot.end BETWEEN u.begin AND u.end))');

        // Participants have not blocking participation
        $queryBuilder
            ->andWhere('NOT EXISTS (SELECT hp.id FROM Entity:HappeningParticipation hp JOIN hp.happening h WHERE h.blocking = true AND hp.participant IN (:ids) AND (h.begin BETWEEN slot.begin AND slot.end OR h.end BETWEEN slot.begin AND slot.end OR slot.begin BETWEEN h.begin AND h.end OR slot.end BETWEEN h.begin AND h.end))');

        $queryBuilder->setParameter('ids', $ids);

        return array_keys($queryBuilder->getQuery()->getResult());
    }
}
