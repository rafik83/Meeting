<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Tip\Event;

class TipListView
{
    /** @var TipView[] */
    public $tipViews;

    /**
     * TipListView constructor.
     *
     * @param TipView[] $tipViews
     */
    public function __construct(array $tipViews)
    {
        $this->tipViews = $tipViews;
    }
}
