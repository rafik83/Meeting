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
     * @var string
     */
    public $alertIcon;

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
     * @param string $label
     * @param string $link
     * @param bool   $state
     * @param string $alertIcon
     */
    public function __construct($icon, $label, $link, $state = true, $alertIcon = null)
    {
        $this->icon      = $icon;
        $this->state     = $state;
        $this->label     = $label;
        $this->link      = $link;
        $this->alertIcon = $alertIcon;
    }
}
