<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
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
