<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Filter\TaggedNomenclatureFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class TaggedNomenclatureFilterGetter
{
    /** @var TaggedNomenclatureFilterRepositoryInterface */
    private $taggedNomenclatureFilterRepository;

    /** @var NomenclatureRepositoryInterface */
    private $nomenclatureRepository;

    /**
     * @param TaggedNomenclatureFilterRepositoryInterface $taggedNomenclatureFilterRepository
     * @param NomenclatureRepositoryInterface             $nomenclatureRepository
     */
    public function __construct(
        TaggedNomenclatureFilterRepositoryInterface $taggedNomenclatureFilterRepository,
        NomenclatureRepositoryInterface $nomenclatureRepository
    ) {
        $this->taggedNomenclatureFilterRepository = $taggedNomenclatureFilterRepository;
        $this->nomenclatureRepository             = $nomenclatureRepository;
    }

    /**
     * @param Event  $event
     * @param string $tag
     * @param string $locale
     *
     * @return array
     */
    public function getNomenclaturesItems(Event $event, $tag, $locale)
    {
        $taggedNomenclatureFilter = $this->taggedNomenclatureFilterRepository->getByEventAndTag(
            $event,
            $tag
        );

        $nomenclatureIds = null === $taggedNomenclatureFilter ? [] : $taggedNomenclatureFilter->getNomenclaturesId();

        $nomenclatures = $this->nomenclatureRepository->findByEventAndIds(
            $event,
            $nomenclatureIds
        );

        $nomenclatureItems = [];

        foreach ($nomenclatures as $nomenclature) {
            $nomenclatureItems = array_merge(
                $nomenclatureItems,
                $this->buildNomenclature($nomenclature->getChildren(), $locale)
            );
        }

        asort($nomenclatureItems);

        return $nomenclatureItems;
    }

    /**
     * @param Event  $event
     * @param string $tag
     * @param string $locale
     *
     * @return array
     */
    public function getLastNomenclaturesItems(Event $event, $tag, $locale)
    {
        $taggedNomenclatureFilter = $this->taggedNomenclatureFilterRepository->getByEventAndTag(
            $event,
            $tag
        );

        if (null === $taggedNomenclatureFilter) {
            return [];
        }

        $nomenclatures = $this->nomenclatureRepository->findByEventAndIds(
            $event,
            $taggedNomenclatureFilter->getNomenclaturesId()
        );

        $nomenclatureItems = [];

        foreach ($nomenclatures as $nomenclature) {
            $nomenclatureItems = array_merge(
                $nomenclatureItems,
                $this->buildNomenclature($nomenclature->getLastLevel(), $locale)
            );
        }

        return $nomenclatureItems;
    }

    /**
     * @param string $locale
     * @param array  $nomenclatureItems
     *
     * @return array
     */
    private function buildNomenclature($nomenclatureItems, $locale)
    {
        $buildItems = [];

        foreach ($nomenclatureItems as $nomenclatureItem) {
            $buildItems[$nomenclatureItem->getKey()] = $nomenclatureItem->getLabel($locale);
        }

        return $buildItems;
    }
}
