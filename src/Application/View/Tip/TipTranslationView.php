<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Tip;

class TipTranslationView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $content;

    /**
     * TipTranslationView constructor.
     *
     * @param int    $id
     * @param string $title
     * @param string $content
     */
    public function __construct($id, $title, $content)
    {
        $this->id      = $id;
        $this->title   = $title;
        $this->content = $content;
    }
}
