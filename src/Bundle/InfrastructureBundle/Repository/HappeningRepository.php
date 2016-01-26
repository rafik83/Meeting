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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningRepository implements HappeningRepositoryInterface
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
     * @param Happening $happening
     */
    public function add(Happening $happening)
    {
        $this->entityManager->persist($happening);
        $this->entityManager->flush($happening);
    }

    /**
     * {@inheritdoc}
     */
    public function findByScheduleAndParticipant(Schedule $schedule, Participant $participant)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening')
            ->from(Happening::class, 'happening')
            ->join(HappeningParticipation::class, 'participation', 'WITH', 'participation.happening = happening AND participation.participant = :participant')
            ->setParameter('participant', $participant)
            ->where('happening.schedule = :schedule')
            ->setParameter('schedule', $schedule);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByEvent(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\HappeningListView(happening.id, happening.begin, happening.end, translation.title)')
            ->from(Happening::class, 'happening')
            ->join('happening.titleTranslations', 'translation', 'WITH', 'translation.locale = :locale')
            ->where('happening.event = :event')
            ->setParameter('locale', $locale)
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}
