<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @var bool
     */
    public $isTextarea;

    /**
     * TaggedDataView constructor.
     *
     * @param string $type
     * @param bool   $translatable
     * @param array  $translations
     * @param string $content
     * @param string $tag
     * @param bool   $isTextarea
     */
    public function __construct($type, $translatable, array $translations, $content, $tag, $isTextarea)
    {
        $this->type         = $type;
        $this->translatable = $translatable;
        $this->translations = $translations;
        $this->content      = $content;
        $this->tag          = $tag;
        $this->isTextarea   = $isTextarea;
    }
}
