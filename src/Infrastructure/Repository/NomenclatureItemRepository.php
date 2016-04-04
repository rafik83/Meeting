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
use Proximum\Vimeet\Domain\Repository\NomenclatureItemRepositoryInterface;

class NomenclatureItemRepository implements NomenclatureItemRepositoryInterface
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
    public function getNomenclatureItemViewsByNomenclatureId($nomenclatureId, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\NomenclatureItemView(nomenclatureItem.id, translations.title, translations.locale)')
            ->from('Entity:NomenclatureItem', 'nomenclatureItem')
            ->join('nomenclatureItem.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('nomenclatureItem.nomenclature = :nomenclatureId')
            ->setParameter('nomenclatureId', $nomenclatureId)
            ->orderBy('translations.title');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getArrayOfNomenclatureItemsByNomenclatureId($nomenclatureId, $locale)
    {
        $items      = $this->getNomenclatureItemViewsByNomenclatureId($nomenclatureId, $locale);
        $itemsArray = [];

        foreach ($items as $item) {
            $itemsArray[$item->id] = $item->title;
        }

        return $itemsArray;
    }

    /**
     * {@inheritdoc}
     */
    public function getNomenclatureItemLabelById($id, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\NomenclatureItemView(nomenclatureItem.id, translations.title, translations.locale)')
            ->from('Entity:NomenclatureItem', 'nomenclatureItem')
            ->join('nomenclatureItem.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('nomenclatureItem.id = :id')
            ->setParameter('id', $id);

        $nomenclatureItem = $queryBuilder->getQuery()->getOneOrNullResult();

        return null !== $nomenclatureItem ? $nomenclatureItem->title : null;
    }
}
