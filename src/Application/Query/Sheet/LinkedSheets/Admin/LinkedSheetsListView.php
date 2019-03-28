<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\LinkedSheets\Admin;

class LinkedSheetsListView
{
    /** @var LinkedSheetsView[] */
    public $linkedSheetsView;

    public function __construct(array $linkedSheetsView)
    {
        $this->linkedSheetsView = $linkedSheetsView;
    }
}
