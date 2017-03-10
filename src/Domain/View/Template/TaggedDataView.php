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
     * TaggedDataView constructor.
     *
     * @param string $type
     * @param bool   $translatable
     * @param array  $translations
     * @param string $content
     */
    public function __construct($type, $translatable, array $translations, $content)
    {
        $this->type         = $type;
        $this->translatable = $translatable;
        $this->translations = $translations;
        $this->content      = $content;
    }
}
