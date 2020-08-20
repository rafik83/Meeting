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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Type;
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
    }

    public function findById(int $id): ?Happening
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening')
            ->from(Happening::class, 'happening')
            ->where('happening.id = :id')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findListByEvent(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening, translation, talking, speaker, category, categoryTranslation')
            ->from(Happening::class, 'happening')
            ->join(
                'happening.translations',
                'translation',
                'WITH',
                'happening.event = :event AND translation.locale = :locale'
            )
            ->join('happening.category', 'category')
            ->join('category.translations', 'categoryTranslation')
            ->leftJoin('happening.talkings', 'talking')
            ->leftJoin('talking.speaker', 'speaker')
            ->setParameter('locale', $locale)
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
            ->join('happening.translations', 'translations', 'WITH', 'happening.event = :event')
            ->orderBy('happening.begin')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndTypeAndDayAndCategory(
        Event $event,
        Type $type,
        \DateTimeInterface $day,
        Happening\Category $category = null
    ) {
        $date = clone $day;

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening, translations')
            ->from(Happening::class, 'happening')
            ->join('happening.types', 'type', 'WITH', 'type = :type')
            ->join(
                'happening.translations',
                'translations',
                'WITH',
                'happening.event = :event AND happening.begin >= :startDay AND happening.begin < :endDay'
            )
            ->orderBy('happening.begin')
            ->setParameter('event', $event)
            ->setParameter('type', $type)
            ->setParameter('startDay', sprintf('%s 00:00:00', $date->format('Y-m-d')))
            ->setParameter('endDay', sprintf('%s 00:00:00', $date->modify('+1 day')->format('Y-m-d')));

        if (null !== $category) {
            $queryBuilder
                ->andWhere('happening.category = :category')
                ->setParameter('category', $category);
        }

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
            ->join('happening.talkings', 'talking')
            ->join('talking.speaker', 'speaker', 'WITH', 'talking.speaker = :speaker')
            ->setParameter('locale', $locale)
            ->setParameter('speaker', $speaker);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Event $event
     *
     * @return Happening[]
     */
    public function findHappeningParticipant(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening, translations, participations, user')
            ->from(Happening::class, 'happening')
            ->join('happening.translations', 'translations', 'WITH', 'happening.event = :event')
            ->join('happening.participations', 'participations', 'WITH', 'participations.disabled = false')
            ->join('participations.user', 'user')
            ->orderBy('happening.begin')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findWithProductsAndType(Event $event, Type $type): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('happening')
            ->from(Happening::class, 'happening')
            ->join('happening.products', 'product', 'WITH', 'happening.event = :event')
            ->join('happening.types', 'type', 'WITH', 'type = :type')
            ->setParameter('event', $event)
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult()
        ;
    }
}
