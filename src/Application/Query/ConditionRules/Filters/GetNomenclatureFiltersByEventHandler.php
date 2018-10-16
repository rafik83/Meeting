<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\ConditionRules\Filters;

use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;

class GetNomenclatureFiltersByEventHandler
{
    /** @var TaggedNomenclatureFilterGetter */
    private $taggedNomenclatureFilterGetter;

    public function __construct(TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter)
    {
        $this->taggedNomenclatureFilterGetter = $taggedNomenclatureFilterGetter;
    }

    public function handle(GetNomenclatureFiltersByEventQuery $query): array
    {
        return $this->taggedNomenclatureFilterGetter->getNomenclaturesItemsByEvent($query->event, $query->locale);
    }
}
