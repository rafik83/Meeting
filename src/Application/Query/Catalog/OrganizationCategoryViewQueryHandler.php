<?php

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
        $nomenclaturesItemsViews = $this->taggedNomenclatureFilterGetter->getLastNomenclaturesItems(
            $query->event,
            Tag::SHEET_ORGANIZATION_CATEGORY,
            $query->locale
        );

        $organizationCategoryViews = [];

        foreach ($nomenclaturesItemsViews->nomenclaturesItems as $key => $title) {
            $organizationCategoryViews[] = new OrganizationCategoryView($key, $title);
        }

        usort($organizationCategoryViews, function (OrganizationCategoryView $first, OrganizationCategoryView $second) {
            return strcmp($first->title, $second->title);
        });

        return $organizationCategoryViews;
    }
}
