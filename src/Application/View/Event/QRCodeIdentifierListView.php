<?php

namespace Proximum\Vimeet\Application\View\Event;

class QRCodeIdentifierListView
{
    /** @var QRCodeIdentifierView[] */
    public $list;

    public function __construct(array $list)
    {
        $this->list = $list;
    }
}
