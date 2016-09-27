<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
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
            ->select('NEW Proximum\Vimeet\Domain\View\EventListView(event.id, event.title, event.domain, event.locales, event.fallback)')
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
    public function getEventsByAdmin(Admin $admin)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('event')
            ->from(Event::class, 'event');

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
            ->from(Event::class, 'event', 'event.id');

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
            ->select('NEW Proximum\Vimeet\Domain\View\EventListView(event.id, event.title, event.domain, event.locales, event.fallback)')
            ->from(Event::class, 'event');

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
}
