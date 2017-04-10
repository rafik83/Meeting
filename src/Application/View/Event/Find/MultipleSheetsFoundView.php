<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Event\Find;

class MultipleSheetsFoundView
{
    /** @var string */
    public $numero;

    /** @var SheetFoundView[] */
    public $sheets;

    /**
     * @param string           $numero
     * @param SheetFoundView[] $sheetFoundViews
     */
    public function __construct($numero, array $sheetFoundViews)
    {
        $this->numero = $numero;
        $this->sheets = $sheetFoundViews;
    }
}
