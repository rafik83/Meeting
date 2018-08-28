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
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;

class NomenclatureTagViewQueryHandler
{
    /** @var TaggedNomenclatureFilterGetter */
    private $taggedNomenclatureFilterGetter;

    public function __construct(TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter)
    {
        $this->taggedNomenclatureFilterGetter = $taggedNomenclatureFilterGetter;
    }

    public function handle(NomenclatureTagViewQuery $query): array
    {
        $tagViews = [];

        foreach ($query->tags as $tag) {
            $nomenclaturesItemsViews = $this->taggedNomenclatureFilterGetter->getLastNomenclaturesItems(
                $query->event,
                $tag,
                $query->locale
            );

            foreach ($nomenclaturesItemsViews->nomenclaturesItems as $itemKey => $itemLabel) {
                $tagViews[$tag][] = new NomenclatureTagView($itemKey , $itemLabel);
            }
        }

        return $tagViews;
    }
}
