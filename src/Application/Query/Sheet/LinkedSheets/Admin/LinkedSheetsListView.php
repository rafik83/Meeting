<?php

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
