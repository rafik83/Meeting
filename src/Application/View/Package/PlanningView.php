<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

class PlanningView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var float
     */
    public $price;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var int
     */
    public $quantityMax;

    /**
     * @var int
     */
    public $numberIncluded;

    /**
     * @param int    $id
     * @param string $title
     * @param string $description
     * @param float  $price
     * @param string $vatMode
     * @param int    $quantityMax
     * @param int    $numberIncluded
     */
    public function __construct($id, $title, $description, $price, $vatMode, $quantityMax, $numberIncluded)
    {
        $this->id             = $id;
        $this->title          = $title;
        $this->description    = $description;
        $this->price          = $price;
        $this->vatMode        = $vatMode;
        $this->quantityMax    = $quantityMax;
        $this->numberIncluded = $numberIncluded;
    }
}
