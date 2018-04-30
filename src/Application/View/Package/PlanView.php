<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

class PlanView
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
     * @var float
     */
    public $price;

    /**
     * @var string
     */
    public $heading;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $image;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var FeatureView[]
     */
    public $features;

    /**
     * @param int           $id
     * @param string        $title
     * @param float         $price
     * @param string        $heading
     * @param string        $description
     * @param string        $image
     * @param string        $vatMode
     * @param string        $currency
     * @param FeatureView[] $features
     */
    public function __construct(
        $id,
        $title,
        $price,
        $heading,
        $description,
        $image,
        $vatMode,
        $currency,
        array $features
    ) {
        $this->id          = $id;
        $this->title       = $title;
        $this->price       = $price;
        $this->heading     = $heading;
        $this->description = $description;
        $this->image       = $image;
        $this->vatMode     = $vatMode;
        $this->currency    = $currency;
        $this->features    = $features;
    }
}
