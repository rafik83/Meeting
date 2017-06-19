<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
