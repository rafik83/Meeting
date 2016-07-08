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
    public $label;

    /**
     * @var string
     */
    public $link;

    /**
     * LinkView constructor.
     *
     * @param string $label
     * @param string $link
     */
    public function __construct($label, $link)
    {
        $this->label = $label;
        $this->link  = $link;
    }
}
