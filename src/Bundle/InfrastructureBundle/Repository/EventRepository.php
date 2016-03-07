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
    public function getList()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\EventListView(event.id, event.title, event.domain, event.locales, event.fallback)')
            ->from('Entity:Event', 'event');

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
            ->from('Entity:Event', 'event')
            ->where('event.domain = :domain')
            ->setParameter('domain', $domain);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventViewByDomain($domain, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\EventView(event.id, event.title, translations.description, translations.locale, event.locales, event.timeZone)')
            ->from('Entity:Event', 'event')
            ->join('event.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('event.domain = :domain')
            ->setParameter('domain', $domain)
            ->setMaxResults(1);

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
            ->from('Entity:Event', 'event')
            ->where('event.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
