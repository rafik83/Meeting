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
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Application\Components\Happening\HappeningTitleView;

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
    }

    /**
     * {@inheritdoc}
     */
    public function findListByEvent(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening, translation, talking, speaker')
            ->from(Happening::class, 'happening')
            ->join('happening.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->leftJoin('happening.talkings', 'talking')
            ->leftJoin('talking.speaker', 'speaker')
            ->where('happening.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findIdsWithoutParticipation(array $happenings)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening.id')
            ->from(Happening::class, 'happening', 'happening.id')
            ->andWhere('happening IN (:happenings)')
            ->setParameter('happenings', $happenings)
            ->andWhere('NOT EXISTS(SELECT hp.id FROM Entity:HappeningParticipation hp where hp.happening = happening)');

        return array_keys($queryBuilder->getQuery()->getResult());
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

    /**
     * {@inheritdoc}
     */
    public function findBySpeaker(Speaker $speaker, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening, translation')
            ->from(Happening::class, 'happening')
            ->join('happening.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->join('happening.talkings', 'talking')
            ->join('talking.speaker', 'speaker', 'WITH', 'talking.speaker = :speaker')
            ->setParameter('speaker', $speaker);

        return $queryBuilder->getQuery()->getResult();
    }
}
