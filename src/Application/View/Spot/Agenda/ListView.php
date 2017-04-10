<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
