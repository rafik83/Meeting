<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class CompleteTranslationViewQuery
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
     * CompleteTranslationViewQuery constructor.
     *
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Sheet $sheet, $locale)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
    }
}
