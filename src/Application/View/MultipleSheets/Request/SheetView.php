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

    /** @var string */
    public $type;

    /**
     * @param int         $sheetId
     * @param string      $sheetTitle
     * @param Sheet       $sheet
     * @param string $type
     */
    public function __construct($sheetId, $sheetTitle, Sheet $sheet, string $type = '')
    {
        $this->sheetId = $sheetId;
        $this->sheetTitle = $sheetTitle;
        $this->sheet = $sheet;
        $this->requestViews = [];
        $this->type = $type;
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
