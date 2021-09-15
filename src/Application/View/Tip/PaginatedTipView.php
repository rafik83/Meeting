<?php

namespace Proximum\Vimeet\Application\View\Tip;

use Proximum\Vimeet\Domain\Model\PaginatedResult;

class PaginatedTipView
{
    /** @var PaginatedResult */
    public $results;

    /**
     * PaginatedTipView constructor.
     *
     * @param PaginatedResult $results
     */
    public function __construct(PaginatedResult $results)
    {
        $this->results = $results;
    }
}
