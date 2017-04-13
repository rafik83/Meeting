<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Group\Request;

class SheetView
{
    /** @var int */
    public $sheetId;

    /** @var string */
    public $sheetTitle;

    /** @var RequestView[] */
    public $requestViews;

    /**
     * @param int    $sheetId
     * @param string $sheetTitle
     */
    public function __construct($sheetId, $sheetTitle)
    {
        $this->sheetId = $sheetId;
        $this->sheetTitle = $sheetTitle;
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
