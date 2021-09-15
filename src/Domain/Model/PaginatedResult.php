<?php

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
     * @var array|null
     */
    public $aggregations;

    /**
     * PaginatedResult constructor.
     *
     * @param array      $results
     * @param int        $page
     * @param int        $limit
     * @param int        $total
     * @param array|null $aggregations
     */
    public function __construct(array $results, $page, $limit, $total, array $aggregations = null)
    {
        $this->results      = array_values($results);
        $this->page         = $page;
        $this->limit        = $limit;
        $this->total        = $total;
        $this->pages        = (int) ceil($total / $limit);
        $this->aggregations = $aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return null !== key($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->results);
    }

    /**
     * @param \Closure $closure
     *
     * @return array
     */
    public function map(\Closure $closure)
    {
        return array_map($closure, $this->results);
    }
}
