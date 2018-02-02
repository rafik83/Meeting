<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Vat;

class VatListView
{
    /** @var float */
    public $total;

    /** @var float */
    public $totalWithVat;

    /** @var bool */
    public $vatApplicable;

    /** @var string */
    public $vatMode;

    /** @var VatView[] */
    public $vatViews;

    /**
     * @param float  $total
     * @param float  $totalWithVat
     * @param bool   $vatApplicable
     * @param string $vatMode
     * @param array  $vatViews
     */
    public function __construct(
        float $total,
        float $totalWithVat,
        bool $vatApplicable,
        string $vatMode,
        array $vatViews = []
    ) {
        $this->total = $total;
        $this->totalWithVat = $totalWithVat;
        $this->vatViews = $vatViews;
        $this->vatApplicable = $vatApplicable;
        $this->vatMode = $vatMode;
    }
}
