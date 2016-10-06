<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetPreviewViewQuery
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
     * @var Sheet
     */
    public $viewer;

    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param Sheet  $viewer
     */
    public function __construct(Sheet $sheet, $locale, Sheet $viewer)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->viewer = $viewer;
    }
}
