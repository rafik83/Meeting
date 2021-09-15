<?php

namespace Proximum\Vimeet\Application\View\Spot\Agenda;

class ListView
{
    /**
     * @var SpotView[]
     */
    public $spotViews;

    /**
     * ListView constructor.
     *
     * @param SpotView[] $spotViews
     */
    public function __construct(array $spotViews)
    {
        $this->spotViews = $spotViews;
    }
}
