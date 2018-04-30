<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
