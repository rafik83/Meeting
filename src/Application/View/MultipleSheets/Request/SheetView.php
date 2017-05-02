<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\MultipleSheets\Request;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetView
{
    /** @var int */
    public $sheetId;

    /** @var string */
    public $sheetTitle;

    /** @var Sheet */
    public $sheet;

    /** @var RequestView[] */
    public $requestViews;

    /**
     * @param int    $sheetId
     * @param string $sheetTitle
     * @param Sheet  $sheet
     */
    public function __construct($sheetId, $sheetTitle, Sheet $sheet)
    {
        $this->sheetId = $sheetId;
        $this->sheetTitle = $sheetTitle;
        $this->sheet = $sheet;
        $this->requestViews = [];
    }

    /**
     * @param RequestView $requestView
     */
    public function addRequest(RequestView $requestView)
    {
        $this->requestViews[] = $requestView;
    }

    /**
     * @return int
     */
    public function numberOfRequest()
    {
        return count($this->requestViews);
    }
}
