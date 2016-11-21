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
    private $alert;

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
     * @param bool   $alert
     */
    public function __construct($icon, $label, $link, $state = true, $alert = false)
    {
        $this->icon  = $icon;
        $this->state = $state;
        $this->label = $label;
        $this->link  = $link;
        $this->alert = $alert;
    }

    /**
     * @return bool
     */
    public function hasAlert()
    {
        return $this->alert === true;
    }
}
