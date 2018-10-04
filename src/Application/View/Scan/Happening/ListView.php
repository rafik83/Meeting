<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Scan\Happening;

class ListView
{
    /** @var HappeningView[] */
    public $happenings;

    public function __construct(array $happenings = [])
    {
        $this->happenings = $happenings;
    }
}
