<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;

class OrganizationCategoryViewQueryHandler
{
    /** @var TaggedNomenclatureFilterGetter */
    private $taggedNomenclatureFilterGetter;

    /**
     * @param TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter
     */
    public function __construct(TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter)
    {
        $this->taggedNomenclatureFilterGetter = $taggedNomenclatureFilterGetter;
    }

    /**
     * @param OrganizationCategoryViewQuery $query
     *
     * @return OrganizationCategoryView[]
     */
    public function handle(OrganizationCategoryViewQuery $query)
    {
        $organizationCategoryItems = $this->taggedNomenclatureFilterGetter->getNomenclaturesItems(
            $query->event,
            Tag::SHEET_ORGANIZATION_CATEGORY,
            $query->locale
        );

        $organizationCategoryViews = [];

        foreach ($organizationCategoryItems as $key => $title) {
            $organizationCategoryViews[] = new OrganizationCategoryView($key, $title);
        }

        return $organizationCategoryViews;
    }
}
