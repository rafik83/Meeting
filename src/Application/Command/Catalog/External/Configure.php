<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
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

    /** @var CatalogVisibility */
    public $catalogVisibility;

    /**
     * Configure constructor.
     *
     * @param Event             $event
     * @param CatalogVisibility $catalogVisibility
     * @param SearchFacet[]     $searchFacets
     */
    public function __construct(Event $event, CatalogVisibility $catalogVisibility, array $searchFacets)
    {
        $this->event = $event;
        $this->searchFacets = $searchFacets;
        $this->externalCatalogEnabled = $event->isExternalCatalogEnabled();
        $this->catalogVisibility = $catalogVisibility;
        $this->types = $catalogVisibility->getTypes();
        $this->categories = $catalogVisibility->getCategories();
    }
}
