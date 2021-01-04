<?php

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
