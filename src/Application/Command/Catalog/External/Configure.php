<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Catalog\External;

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
    public $catalogPublic;

    /** @var SearchFacet[] */
    public $searchFacets;

    /**
     * Configure constructor.
     *
     * @param Event         $event
     * @param SearchFacet[] $searchFacets
     */
    public function __construct(Event $event, array $searchFacets)
    {
        $this->event = $event;
        $this->searchFacets = $searchFacets;
        $this->catalogPublic = $event->isCatalogPublic();
    }
}
