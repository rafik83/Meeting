<?php

namespace Proximum\Vimeet\Application\Query\Tip;

class TipViewQuery
{
    /** @var int */
    public $page;

    /** @var int */
    public $limit;

    /**
     * TipViewQuery constructor.
     *
     * @param int $page
     * @param int $limit = 20
     */
    public function __construct($page, $limit = 20)
    {
        $this->page  = $page;
        $this->limit = $limit;
    }
}
