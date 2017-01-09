<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Unavailability;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;

class MassRepository implements MassRepositoryInterface
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
    public function create(Mass $mass)
    {
        $this->entityManager->persist($mass);
        $this->entityManager->flush($mass);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Mass $mass)
    {
        $this->entityManager->flush($mass);

        foreach ($mass->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('mass')
            ->from(Mass::class, 'mass')
            ->join('mass.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->where('mass.event = :event')
            ->setParameter('event', $event)
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findBlockingByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('mass')
            ->from(Mass::class, 'mass')
            ->where('mass.event = :event')
            ->andWhere('mass.blocking = true')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Mass $mass)
    {
        $this->entityManager->remove($mass);
        $this->entityManager->flush($mass);
    }
}
