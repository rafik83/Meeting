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
     * {@inheritdoc}
     */
    public function add(Happening $happening)
    {
        $this->entityManager->persist($happening);
        $this->entityManager->flush($happening);

        foreach ($happening->getTalkings() as $talking) {
            $this->entityManager->flush($talking);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(Happening $happening)
    {
        $this->entityManager->flush($happening);

        foreach ($happening->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }

        foreach ($happening->getTalkings() as $talking) {
            $this->entityManager->flush($talking);
        }

        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findListByEvent(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\HappeningListView(happening.id, happening.begin, happening.end, translation.title)')
            ->from(Happening::class, 'happening')
            ->join('happening.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->where('happening.event = :event')
            ->setParameter('locale', $locale)
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventWithoutParticipation(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening')
            ->from(Happening::class, 'happening')
            ->where('happening.event = :event')
            ->andWhere('NOT EXISTS(SELECT hp.id FROM Entity:HappeningParticipation hp where hp.happening = happening)')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }


    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening, translations')
            ->from(Happening::class, 'happening')
            ->join('happening.translations', 'translations')
            ->where('happening.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}
