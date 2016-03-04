<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class PaginatedResult
{
    /**
     * @var array
     */
    public $results;

    /**
     * @var int
     */
    public $page;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var int
     */
    public $total;

    /**
     * @var int
     */
    public $pages;

    /**
     * PaginatedResult constructor.
     *
     * @param array $results
     * @param int   $page
     * @param int   $limit
     * @param int   $total
     */
    public function __construct(array $results, $page, $limit, $total)
    {
        $this->results = array_values($results);
        $this->page    = $page;
        $this->limit   = $limit;
        $this->total   = $total;
        $this->pages   = (int) ceil($total / $limit);
    }
}
