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
use Proximum\Vimeet\Domain\Model\DateRangeInterface;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class MeetingSlotRepository implements MeetingSlotRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @param EntityManager                     $entityManager
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     */
    public function __construct(
        EntityManager $entityManager,
        UnavailabilityRepositoryInterface $unavailabilityRepository
    ) {
        $this->entityManager            = $entityManager;
        $this->unavailabilityRepository = $unavailabilityRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findBlockedForMeetingsByParticipants(array $participants)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('meetingSlot')
            ->from(MeetingSlot::class, 'meetingSlot')
            ->join(Meeting::class, 'meeting', 'WITH', 'meeting.meetingSlot = meetingSlot')
            ->leftJoin('meeting.fromParticipants', 'fromParticipants')
            ->leftJoin('meeting.toParticipants', 'toParticipants')
            ->where('fromParticipants IN (:participants) OR toParticipants IN (:participants)')
            ->setParameter('participants', $participants);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getMeetingSlotsDependingOnParticipants(array $participants)
    {
        $unavailabilities = array_merge(
            $this->unavailabilityRepository->findByParticipants($participants),
            $this->findBlockedForMeetingsByParticipants($participants)
        );

        return $this->getMeetingSlotsDependingOnUnavailableDateRanges($unavailabilities);
    }

    /**
     * {@inheritdoc}
     */
    public function getMeetingSlotsDependingOnUnavailableDateRanges(array $unavailabilities)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder();

        $queryBuilder
            ->select('meetingSlot')
            ->from(MeetingSlot::class, 'meetingSlot')
            ->orderBy('meetingSlot.begin');

        foreach ($unavailabilities as $key => $unavailability) {
            if (!$unavailability instanceof DateRangeInterface) {
                throw new \Exception('Unavailability must be an instance of DateRangeInterface');
            }

            $queryBuilder
                ->andWhere('meetingSlot.begin >= :end' . $key . ' OR meetingSlot.end <= :begin' . $key)
                ->setParameter('begin' . $key, $unavailability->getBegin())
                ->setParameter('end' . $key, $unavailability->getEnd());
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
