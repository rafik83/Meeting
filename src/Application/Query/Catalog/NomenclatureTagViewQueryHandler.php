<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\View\Catalog\Aggregat\NomenclatureTagView;
use Proximum\Vimeet\Application\View\Catalog\Aggregat\NomenclatureTagViews;
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;

class NomenclatureTagViewQueryHandler
{
    /** @var TaggedNomenclatureFilterGetter */
    private $taggedNomenclatureFilterGetter;

    public function __construct(TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter)
    {
        $this->taggedNomenclatureFilterGetter = $taggedNomenclatureFilterGetter;
    }

    /**
     * @return NomenclatureTagViews[] indexed by tag
     */
    public function handle(NomenclatureTagViewQuery $query): array
    {
        $nomenclatureTagViewsIndexedByTag = [];

        foreach ($query->tags as $tag) {
            $nomenclatureTagViews = [];

            $nomenclaturesItemsViews = $this->taggedNomenclatureFilterGetter->getLastNomenclaturesItems(
                $query->event,
                $tag,
                $query->locale
            );

            foreach ($nomenclaturesItemsViews->nomenclaturesItems as $itemKey => $itemLabel) {
                $nomenclatureTagViews[] = new NomenclatureTagView(mb_strtolower($itemKey), $itemLabel);
            }

            $nomenclatureTagViewsIndexedByTag[$tag] = new NomenclatureTagViews(
                $tag,
                $nomenclatureTagViews,
                $nomenclaturesItemsViews->maxDepth
            );
        }

        return $nomenclatureTagViewsIndexedByTag;
    }
}
