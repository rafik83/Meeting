<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
    public function __construct(string $title, string $content)
    {
        $this->title   = $title;
        $this->content = $content;
    }
}
