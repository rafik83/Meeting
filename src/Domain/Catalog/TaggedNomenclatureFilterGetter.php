<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Catalog\View\NomenclatureFilterView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
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

    public function getNomenclaturesItemsByEvent(Event $event, string $locale): array
    {
        $taggedNomenclatureFilter = $this->taggedNomenclatureFilterRepository->getByEvent($event);
        $nomenclatureIds = null === $taggedNomenclatureFilter ? [] : $taggedNomenclatureFilter->getNomenclaturesId();

        $nomenclatures = $this->nomenclatureRepository->findByEventAndIds(
            $event,
            $nomenclatureIds
        );

        $nomenclatureItems = [];

        foreach ($nomenclatures as $nomenclature) {
            $nomenclatureItems[] = new NomenclatureFilterView(
                $nomenclature->getId(),
                $nomenclature->getTitle(),
                $nomenclature->getLabels($locale)
            );
        }

        return $nomenclatureItems;
    }

    /**
     * @param Event  $event
     * @param string $tag
     * @param string $locale
     *
     * @return NomenclaturesItemsView
     */
    public function getLastNomenclaturesItems(Event $event, $tag, $locale): NomenclaturesItemsView
    {
        $taggedNomenclatureFilter = $this->taggedNomenclatureFilterRepository->getByEventAndTag(
            $event,
            $tag
        );

        if (null === $taggedNomenclatureFilter) {
            return new NomenclaturesItemsView([], 0);
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

        return new NomenclaturesItemsView($nomenclatureItems, $this->getMaxDepth($nomenclatures));
    }

    /**
     * @param Nomenclature[] $nomenclatures
     *
     * @return int
     */
    private function getMaxDepth(array $nomenclatures): int
    {
        $maxDepth = 0;

        foreach ($nomenclatures as $nomenclature) {
            if ($maxDepth < $nomenclature->getDepth()) {
                $maxDepth = $nomenclature->getDepth();
            }
        }

        return $maxDepth;
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
