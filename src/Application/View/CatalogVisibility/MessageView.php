<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\CatalogVisibility;

class MessageView
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $content;

    /**
     * MessageView constructor.
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
