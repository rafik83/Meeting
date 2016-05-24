<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class ProductTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $heading;

    /**
     * @var string
     */
    private $description;

    /**
     * Small addon text dipslayed under the price
     *
     * @var string
     */
    private $addon;

    /**
     * @param Product $product
     * @param string  $locale
     * @param string  $title
     * @param string  $heading
     * @param string  $description
     * @param string  $addon
     */
    public function __construct(Product $product, $locale, $title, $heading, $description, $addon)
    {
        $this->product     = $product;
        $this->locale      = $locale;
        $this->title       = $title;
        $this->heading     = $heading;
        $this->description = $description;
        $this->addon       = $addon;
    }

    /**
     * @param string $title
     * @param string $heading
     * @param string $description
     * @param string $addon
     */
    public function set($title, $heading, $description, $addon)
    {
        $this->title       = $title;
        $this->heading     = $heading;
        $this->description = $description;
        $this->addon       = $addon;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get heading
     *
     * @return string
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get addon
     *
     * @return string
     */
    public function getAddon()
    {
        return $this->addon;
    }
}
