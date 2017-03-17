<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order\Export;

class CustomRowView
{
    /** @var string */
    public $title;

    /** @var string */
    public $unitPriceTitle;

    /** @var string */
    public $quantityTitle;

    /** @var string */
    public $totalTitle;

    /**
     * @param string $title
     * @param string $unitPriceTitle
     * @param string $quantityTitle
     * @param string $totalTitle
     */
    public function __construct($title, $unitPriceTitle, $quantityTitle, $totalTitle)
    {
        $this->title          = $title;
        $this->unitPriceTitle = $unitPriceTitle;
        $this->quantityTitle  = $quantityTitle;
        $this->totalTitle     = $totalTitle;
    }
}
