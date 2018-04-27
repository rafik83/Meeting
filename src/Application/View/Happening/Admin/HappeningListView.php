<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening\Admin;

class HappeningListView
{
    /**
     * @var HappeningView[]
     */
    public $happenings;

    /**
     * @param HappeningView[] $happenings
     */
    public function __construct(array $happenings = [])
    {
        $this->happenings = $happenings;
    }
}
