<?php

namespace Proximum\Vimeet\Application\View\Spot;

class ListView
{
    /**
     * @var SpotView[]
     */
    public $spots = [];

    /**
     * @param SpotView $spot
     */
    public function addSpot(SpotView $spot)
    {
        $this->spots[] = $spot;
    }

    /**
     * @return bool
     */
    public function hasSpot()
    {
        return !empty($this->spots);
    }
}
