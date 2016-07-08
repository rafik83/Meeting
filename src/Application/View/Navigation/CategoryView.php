<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Navigation;

class CategoryView
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var LinkView[]
     */
    private $linksView;

    /**
     * CategoryView constructor.
     *
     * @param string     $title
     * @param LinkView[] $linksView
     */
    public function __construct($title, $icon, array $linksView)
    {
        $this->title     = $title;
        $this->icon      = $icon;
        $this->linksView = $linksView;
    }
}
