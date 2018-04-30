<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin\Request;

class RequestSheetsView
{
    /**
     * @var RequestSheetView
     */
    public $fromSheet;

    /**
     * @var RequestSheetView
     */
    public $toSheet;

    /**
     * RequestSheetsView constructor.
     *
     * @param RequestSheetView $fromSheet
     * @param RequestSheetView $toSheet
     */
    public function __construct(RequestSheetView $fromSheet, RequestSheetView $toSheet)
    {
        $this->fromSheet = $fromSheet;
        $this->toSheet   = $toSheet;
    }
}
