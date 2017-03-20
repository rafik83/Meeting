<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Template;

class TaggedDataView
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var bool
     */
    public $translatable;

    /**
     * @var array
     */
    public $translations;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $tag;

    /**
     * TaggedDataView constructor.
     *
     * @param string $type
     * @param bool   $translatable
     * @param array  $translations
     * @param string $content
     * @param string $tag
     */
    public function __construct($type, $translatable, array $translations, $content, $tag)
    {
        $this->type         = $type;
        $this->translatable = $translatable;
        $this->translations = $translations;
        $this->content      = $content;
        $this->tag          = $tag;
    }
}
