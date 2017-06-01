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
    /** @var TipView[] */
    public $tipListView;

    /** @var PaginatedResult */
    public $results;
}
