<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
