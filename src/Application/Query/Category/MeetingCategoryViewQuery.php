<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Category;

use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingCategoryViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /**
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Sheet $sheet, string $locale)
    {
        $this->sheet = $sheet;
        $this->locale = $locale;
    }
}
