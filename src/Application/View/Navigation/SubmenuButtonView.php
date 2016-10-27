<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Navigation;

class SubmenuButtonView
{
    /**
     * @var string
     */
    public $icon;

    /**
     * @var bool
     */
    public $state;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $link;

    /**
     * SubmenuButtonView constructor.
     *
     * @param string $icon
     * @param bool   $state
     * @param string $label
     * @param string $link
     */
    public function __construct($icon, $label, $link, $state = true)
    {
        $this->icon  = $icon;
        $this->state = $state;
        $this->label = $label;
        $this->link  = $link;
    }
}
