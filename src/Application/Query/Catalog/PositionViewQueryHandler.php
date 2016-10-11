<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;

class PositionViewQueryHandler
{
    /**
     * @var TaggedNomenclatureFilterGetter
     */
    private $taggedNomenclatureFilterGetter;

    /**
     * @param TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter
     */
    public function __construct(TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter)
    {
        $this->taggedNomenclatureFilterGetter = $taggedNomenclatureFilterGetter;
    }

    /**
     * @param PositionViewQuery $query
     *
     * @return PositionView[]
     */
    public function handle(PositionViewQuery $query)
    {
        $positionItems = $this->taggedNomenclatureFilterGetter->getNomenclaturesItems(
            $query->event,
            Tag::PARTICIPANT_POSITION,
            $query->locale
        );

        $positionViews = [];

        foreach ($positionItems as $key => $title) {
            $positionViews[] = new PositionView($key, $title);
        }

        return $positionViews;
    }
}
