<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip;

class TipTranslationViewQuery
{
    /** @var string */
    public $path;

    /** @var string */
    public $locale;

    /**
     * TipTranslationViewQuery constructor.
     *
     * @param string $path
     * @param string $locale
     */
    public function __construct($path, $locale)
    {
        $this->path   = $path;
        $this->locale = $locale;
    }
}