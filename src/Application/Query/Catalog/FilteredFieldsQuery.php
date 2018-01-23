<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;

class FilteredFieldsQuery
{
    /** @var Event */
    public $event;

    /** @var array */
    public $filters;

    /** @var array */
    public $currentAggregations;

    /** @var TypeView[] */
    public $typeViews;

    /** @var OrganizationCategoryView[] */
    public $organizationCategoryViews;

    /** @var PositionView[] */
    public $positionViews;

    /** @var string */
    public $locale;

    /** @var CategoryView[] */
    public $categoryViews;

    /** @var array */
    public $availableSlotIds;

    /** @var array */
    public $sheetsToExclude;

    /**
     * @param Event                      $event
     * @param array                      $filters
     * @param array                      $currentAggregations
     * @param TypeView[]                 $typeViews
     * @param CategoryView[]             $categoryViews
     * @param OrganizationCategoryView[] $organizationCategoryViews
     * @param PositionView[]             $positionViews
     * @param string                     $locale
     * @param array                      $availableSlotIds
     * @param array                      $sheetsToExclude
     */
    public function __construct(
        Event $event,
        array $filters,
        array $currentAggregations,
        array $typeViews,
        array $categoryViews,
        array $organizationCategoryViews,
        array $positionViews,
        string $locale,
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ) {
        $this->event                     = $event;
        $this->filters                   = $filters;
        $this->currentAggregations       = $currentAggregations;
        $this->typeViews                 = $typeViews;
        $this->organizationCategoryViews = $organizationCategoryViews;
        $this->positionViews             = $positionViews;
        $this->locale                    = $locale;
        $this->categoryViews             = $categoryViews;
        $this->availableSlotIds          = $availableSlotIds;
        $this->sheetsToExclude           = $sheetsToExclude;
    }
}
