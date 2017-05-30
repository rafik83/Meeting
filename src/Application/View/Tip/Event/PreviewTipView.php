<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Tip\Event;

class PreviewTipView
{
    /** @var string */
    public $title;

    /** @var string */
    public $content;

    /**
     * PreviewTipView constructor.
     *
     * @param string $title
     * @param string $content
     */
    public function __construct($title, $content)
    {
        $this->title   = $title;
        $this->content = $content;
    }
}
