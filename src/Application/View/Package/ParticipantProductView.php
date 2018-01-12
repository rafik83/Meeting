<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

class ParticipantProductView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var float */
    public $unitPrice;

    /** @var string */
    public $currency;

    /** @var string */
    public $vatMode;

    /** @var int */
    public $quantityMax;

    /** @var int */
    public $quantityIncluded;

    /**
     * @param int    $id
     * @param string $title
     * @param string $description
     * @param float  $unitPrice
     * @param string $currency
     * @param string $vatMode
     * @param int    $quantityMax
     * @param int    $quantityIncluded
     */
    public function __construct(
        int $id,
        string $title,
        string $description,
        float $unitPrice,
        string $currency,
        string $vatMode,
        int $quantityMax,
        int $quantityIncluded
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->unitPrice = $unitPrice;
        $this->currency = $currency;
        $this->vatMode = $vatMode;
        $this->quantityMax = $quantityMax;
        $this->quantityIncluded = $quantityIncluded;
    }
}
