<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Navigation;

class MenuView
{
    /**
     * @var CategoryView[]
     */
    public $categoriesView;

    /**
     * MenuView constructor.
     *
     * @param CategoryView[] $categoriesView
     */
    public function __construct(array $categoriesView)
    {
        $this->categoriesView = $categoriesView;
    }
}
