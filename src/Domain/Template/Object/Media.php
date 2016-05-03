<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Object;

class Media
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $type;

    /**
     * Media constructor.
     *
     * @param string $title
     * @param string $url
     * @param string $type
     */
    public function __construct($title, $url, $type)
    {
        $this->title = $title;
        $this->url   = $url;
        $this->type  = $type;
    }
}
