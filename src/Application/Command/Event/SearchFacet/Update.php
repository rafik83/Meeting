<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\SearchFacet;

class Update
{
    /**
     * @var array
     */
    public $searchFacets;

    /**
     * Update constructor.
     *
     * @param array $searchFacets
     */
    public function __construct(array $searchFacets)
    {
        $this->searchFacets = $searchFacets;
    }
}
