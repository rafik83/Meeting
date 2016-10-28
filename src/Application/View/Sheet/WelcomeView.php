<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

class WelcomeView
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $sheetContent;

    /**
     * @var string
     */
    public $backToSheetLink;

    /**
     * @var string|null
     */
    public $packageContent = null;

    /**
     * @var string|null
     */
    public $backToPackageLink = null;

    /**
     * @param string $title
     * @param string $sheetContent
     * @param string $backToSheetLink
     */
    public function __construct(
        $title,
        $sheetContent,
        $backToSheetLink
    ) {
        $this->title           = $title;
        $this->sheetContent    = $sheetContent;
        $this->backToSheetLink = $backToSheetLink;
    }
}
