<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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

    /** @var bool */
    public $isShowOnMobile;

    /**
     * SubmenuButtonView constructor.
     *
     * @param string|null $icon
     * @param string|null $label
     * @param string|null $link
     * @param bool        $state
     * @param bool        $alert
     * @param bool        $isShowOnMobile
     */
    public function __construct(
        ?string $icon,
        ?string $label,
        ?string $link,
        bool $state = true,
        bool $alert = false,
        bool $isShowOnMobile = false
    ) {
        $this->icon = $icon;
        $this->state = $state;
        $this->label = $label;
        $this->link = $link;
        $this->alert = $alert;
        $this->isShowOnMobile = $isShowOnMobile;
    }

    /**
     * @return bool
     */
    public function hasAlert()
    {
        return true === $this->alert;
    }
}
