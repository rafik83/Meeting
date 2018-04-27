<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Preview;

class TagView
{
    /** @var string */
    public $type;

    /** @var string|null */
    public $label;

    /** @var string */
    public $content;

    /**
     * @param string $type
     * @param string $label
     * @param string $content
     */
    public function __construct($type, $label, $content)
    {
        $this->type    = $type;
        $this->label   = $label;
        $this->content = $content;
    }

    /**
     * @return bool
     */
    public function isLink()
    {
        return 'url' === $this->type;
    }
}
