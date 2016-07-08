<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Navigation;

class LinkView
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $link;

    /**
     * LinkView constructor.
     *
     * @param string $title
     * @param string $link
     */
    public function __construct($title, $link)
    {
        $this->title = $title;
        $this->link  = $link;
    }
}
