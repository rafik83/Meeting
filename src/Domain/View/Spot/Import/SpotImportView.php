<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Spot\Import;

use Proximum\Vimeet\Domain\Spot\Import;

class SpotImportView
{
    /** @var Import */
    public $import;

    /** @var SheetView[] */
    public $sheetViews;

    /** @var array */
    public $errorMessages;

    /**
     * @param Import      $import
     * @param SheetView[] $sheetViews
     * @param array       $errorMessages
     */
    public function __construct(Import $import, array $sheetViews, array $errorMessages)
    {
        $this->import = $import;
        $this->sheetViews = $sheetViews;
        $this->errorMessages = $errorMessages;
    }
}
