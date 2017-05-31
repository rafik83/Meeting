<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Tip\Event;

use Proximum\Vimeet\Application\View\Tip\TipTranslationView;

class TipListView
{
    /** @var TipTranslationView[] */
    public $tipListView;

    /**
     * TipListView constructor.
     *
     * @param TipView[] $tipListView
     */
    public function __construct(array $tipListView)
    {
        $this->tipListView = $tipListView;
    }
}
