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
     * @var string
     */
    public $locale;

    /**
     * @var bool
     */
    public $state;

    /**
     * @var StateButtonView
     */
    public $button;

    /**
     * LinkView constructor.
     *
     * @param string          $label
     * @param string          $link
     * @param null            $locale
     * @param StateButtonView $button
     * @param bool            $state
     */
    public function __construct($label, $link, $locale = null, StateButtonView $button = null, $state = true)
    {
        $this->label  = $label;
        $this->link   = $link;
        $this->locale = $locale;
        $this->button = $button;

        if ($link === null) {
            $this->state = false;
        } else {
            $this->state = $state;
        }
    }
}
