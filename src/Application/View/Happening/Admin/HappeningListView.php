<?php

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
