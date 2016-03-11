<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Countable;
use Iterator;

class PaginatedResult implements Countable, Iterator
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

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        reset($this->results);
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return current($this->results);
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return key($this->results);
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        next($this->results);
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return key($this->results) !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->results);
    }
}
