<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetDetailQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /**
     * SheetDetailQuery constructor.
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
