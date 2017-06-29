<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Application\View\Catalog\External\CatalogVisibilityView;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class Configure
{
    /** @var Event */
    public $event;

    /** @var Type[] */
    public $types;

    /** @var Category[] */
    public $categories;

    /** @var bool */
    public $externalCatalogEnabled;

    /** @var SearchFacet[] */
    public $searchFacets;

    /** @var CatalogVisibilityView */
    public $catalogVisibilityView;

    /**
     * Configure constructor.
     *
     * @param Event                 $event
     * @param CatalogVisibilityView $catalogVisibilityView
     * @param SearchFacet[]         $searchFacets
     */
    public function __construct(Event $event, CatalogVisibilityView $catalogVisibilityView, array $searchFacets)
    {
        $this->event = $event;
        $this->catalogVisibilityView = $catalogVisibilityView;
        $this->searchFacets = $searchFacets;
        $this->externalCatalogEnabled = $event->isExternalCatalogEnabled();
        $this->types = $catalogVisibilityView->catalogVisibility->getTypes();
        $this->categories = $catalogVisibilityView->catalogVisibility->getCategories();
    }
}
