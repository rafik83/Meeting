<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Preview;

class PreviewView
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $type;

    /**
     * @var bool
     */
    public $strong = false;

    /**
     * @return bool
     */
    public function isImage()
    {
        return $this->type === 'image' && !empty($this->content);
    }

    /**
     * @return bool
     */
    public function isStrong()
    {
        return $this->strong !== false;
    }

    /**
     * @param string $id
     * @param string $content
     * @param string $type
     */
    public function __construct($id, $content, $type)
    {
        $this->id      = $id;
        $this->content = $content;
        $this->type    = $type;
    }
}
