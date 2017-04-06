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
    public $context;

    /** @var string */
    public $locale;

    /**
     * TipTranslationViewQuery constructor.
     *
     * @param string $context
     * @param string $locale
     */
    public function __construct($context, $locale)
    {
        $this->context = $context;
        $this->locale  = $locale;
    }
}
