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
    public $title;

    /**
     * @var string
     */
    public $icon;

    /**
     * @var LinkView[]
     */
    public $linksView;

    /**
     * @var bool
     */
    public $isShowOnMobile;

    /**
     * CategoryView constructor.
     *
     * @param string     $title
     * @param string     $icon
     * @param LinkView[] $linksView
     * @param bool       $isShowOnMobile
     */
    public function __construct(string $title, string $icon, array $linksView, bool $isShowOnMobile)
    {
        $this->title          = $title;
        $this->icon           = $icon;
        $this->linksView      = $linksView;
        $this->isShowOnMobile = $isShowOnMobile;
    }

    public function getEnabledLinkView(): ?LinkView
    {
        foreach ($this->linksView as $linkView) {
            if (true === $linkView->state) {
                return $linkView;
            }
        }

        return null;
    }
}
