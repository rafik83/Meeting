<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;

class OrganizationCategoryViewQueryHandler
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /**
     * @var TaggedNomenclatureFilterGetter
     */
    private $taggedNomenclatureFilterGetter;

    /**
     * @param TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter
     * @param SheetSearchAdapterInterface    $sheetSearchAdapter
     */
    public function __construct(
        TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter,
        SheetSearchAdapterInterface $sheetSearchAdapter
    ) {
        $this->taggedNomenclatureFilterGetter = $taggedNomenclatureFilterGetter;
        $this->sheetSearchAdapter             = $sheetSearchAdapter;
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

        $organizationCategoryStats = $this->sheetSearchAdapter->getOrganizationCategoryStats(
            $query->event,
            array_merge(['inCatalog' => true], $query->filters)
        );

        $organizationCategoryViews = [];

        foreach ($organizationCategoryItems as $key => $title) {
            if (isset($organizationCategoryStats[$key]) && $organizationCategoryStats[$key] > 0) {
                $organizationCategoryViews[$key] = new OrganizationCategoryView($key, $title);
            }
        }

        return $organizationCategoryViews;
    }
}
