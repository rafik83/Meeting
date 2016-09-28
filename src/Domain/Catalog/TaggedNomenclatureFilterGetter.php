<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
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

        $nomenclatures = $this->nomenclatureRepository->findByEventAndIds(
            $event,
            $taggedNomenclatureFilter->getNomenclaturesId()
        );

        $nomenclatureItems = [];

        foreach ($nomenclatures as $nomenclature) {
            foreach ($nomenclature->getChildren() as $nomenclatureItem) {
                $nomenclatureItems[$nomenclatureItem->getKey()] = $nomenclatureItem->getLabel($locale);
            }
        }

        asort($nomenclatureItems);

        return $nomenclatureItems;
    }
}
