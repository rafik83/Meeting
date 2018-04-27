<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Navigation;

class SubmenuView
{
    /**
     * @var SubmenuButtonView[]
     */
    public $submenuButtonViews;

    /**
     * SubmenuView constructor.
     *
     * @param SubmenuButtonView[] $submenuButtonViews
     */
    public function __construct(array $submenuButtonViews)
    {
        $this->submenuButtonViews = $submenuButtonViews;
    }
}
