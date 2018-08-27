<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Filter;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\TaggedNomenclatureFilter;
use Proximum\Vimeet\Domain\Repository\Filter\TaggedNomenclatureFilterRepositoryInterface;

class TaggedNomenclatureFilterRepository implements TaggedNomenclatureFilterRepositoryInterface
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
    public function deleteForEventAndTags(Event $event, array $tags)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(TaggedNomenclatureFilter::class, 'taggedNomenclatureFilter')
            ->where('taggedNomenclatureFilter.event = :event AND taggedNomenclatureFilter.tag IN (:tags)')
            ->setParameter('event', $event)
            ->setParameter('tags', $tags)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function add(TaggedNomenclatureFilter $taggedNomenclatureFilter)
    {
        $this->entityManager->persist($taggedNomenclatureFilter);
        $this->entityManager->flush($taggedNomenclatureFilter);
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventAndTag(Event $event, $tag)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('taggedNomenclatureFilter')
            ->from(TaggedNomenclatureFilter::class, 'taggedNomenclatureFilter')
            ->where('taggedNomenclatureFilter.event = :event')
            ->andWhere('taggedNomenclatureFilter.tag = :tag')
            ->setParameter('tag', $tag)
            ->setParameter('event', $event)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
