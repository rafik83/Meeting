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
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class EventRepository implements EventRepositoryInterface
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
    public function add(Event $event)
    {
        $this->entityManager->persist($event);
        $this->entityManager->flush($event);

        foreach ($event->getTranslations() as $translation) {
            $this->entityManager->persist($translation);
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(Event $event)
    {
        $this->entityManager->flush($event);

        foreach ($event->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getListByAdmin(Admin $admin)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\EventListView(event.id, event.title, event.domain, event.locales, event.fallback, event.visible)')
            ->from(Event::class, 'event')
            ->orderBy('event.title');

        if ($admin->hasEvents()) {
            $queryBuilder
                ->where('event IN (:events)')
                ->setParameter('events', $admin->getEvents());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findArchivedByAdmin(Admin $admin): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event', 'days')
            ->from(Event::class, 'event')
            ->leftJoin('event.days', 'days')
            ->where('event.archived = true')
            ->addOrderBy('event.title')
            ->addOrderBy('days.endTime', 'DESC');

        if ($admin->hasEvents()) {
            $queryBuilder
                ->andWhere('event IN (:events)')
                ->setParameter('events', $admin->getEvents());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsWithDaysByAdmin(Admin $admin)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event', 'days')
            ->from(Event::class, 'event')
            ->leftJoin('event.days', 'days')
            ->where('event.archived = false')
            ->addOrderBy('event.title')
            ->addOrderBy('days.endTime', 'DESC');

        if ($admin->hasEvents()) {
            $queryBuilder
                ->andWhere('event IN (:events)')
                ->setParameter('events', $admin->getEvents());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsOrderByIdDesc(): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event')
            ->addOrderBy('event.id', 'DESC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsByAdmin(Admin $admin)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event')
            ->orderBy('event.title');

        if ($admin->hasEvents()) {
            $queryBuilder
                ->where('event IN (:events)')
                ->setParameter('events', $admin->getEvents());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event', 'event.id')
            ->orderBy('event.title');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\EventListView(event.id, event.title, event.domain, event.locales, event.fallback, event.visible)')
            ->from(Event::class, 'event')
            ->orderBy('event.title');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventByDomain($domain)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event')
            ->where('event.domain = :domain')
            ->setParameter('domain', $domain);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event')
            ->where('event.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findEventWithParameters(array $parameters): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event', 'event.id')
        ;

        foreach ($parameters as $key => $parameter) {
            $queryBuilder
                ->andWhere(
                    str_replace(
                        '%epkey%',
                        $key,
                        'EXISTS(SELECT ep_%epkey%.id FROM Entity:Event\ExtraParameter ep_%epkey% WHERE ep_%epkey%.event = event.id AND ep_%epkey%.type = :type_%epkey%)'
                    )
                )
                ->setParameter(str_replace('%epkey%', $key, 'type_%epkey%'), $parameter)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByDay(\DateTimeInterface $dateTime): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event')
            ->join(
                'event.days',
                'day',
                'WITH',
                'day.startTime <= :datetime AND day.endTime >= :datetime'
            )
            ->setParameter('datetime', $dateTime)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return Event[]
     */
    public function findEventsWithPastSMSActivationDateAndAgendaVersionsNotGenerated(\DateTimeInterface $dateTime): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event')
            ->where('event.configuration.smsActivationDate < :datetime')
            ->andWhere('event.userAgendaVersionsGenerated = false')
            ->setParameter('datetime', $dateTime)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findPastEvents(\DateTimeInterface $dateTime): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event')
            ->join('event.days', 'day', 'WITH', 'day.endTime <= :datetime')
            ->join('event.days', 'otherDay')
            ->where('NOT (otherDay.endTime > :datetime)')
            ->setParameter('datetime', $dateTime)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     *
     * @return Event[]
     */
    public function findEventsByDateRange(\DateTimeInterface $begin, \DateTimeInterface $end): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event')
            ->join('event.days', 'day', 'WITH', 'day.endTime <= :end AND day.startTime >= :begin')
            ->join('event.days', 'otherDay')
            ->where('NOT (otherDay.endTime > :end)')
            ->andWhere('NOT (otherDay.startTime < :begin)')
            ->setParameter('begin', $begin)
            ->setParameter('end', $end)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function getEventThatOccursDuringTheGivenDay(\DateTimeInterface $date): array
    {
        $begin = new \DateTime();
        $begin->setTimestamp($date->getTimestamp());
        $end = new \DateTime();
        $end->setTimestamp($date->getTimestamp());

        $begin->setTime(0, 0, 0, 0);
        $end->setTime(23, 59, 59, 99);

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event')
            ->join('event.days', 'day', 'WITH', 'day.endTime <= :end AND day.startTime >= :begin')
            ->setParameter('begin', $begin)
            ->setParameter('end', $end)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
