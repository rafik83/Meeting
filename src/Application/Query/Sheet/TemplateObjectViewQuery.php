<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class TemplateObjectViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var string
     */
    public $key;

    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param string $key
     */
    public function __construct(Sheet $sheet, $locale, $key)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->key    = $key;
    }
}
