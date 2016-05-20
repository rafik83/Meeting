<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
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
    private $description;

    /**
     * @var string
     */
    private $optionalPriceText;

    /**
     * @param Product $product
     * @param string  $locale
     * @param string  $title
     * @param string  $description
     * @param string  $optionalPriceText
     */
    public function __construct(Product $product, $locale, $title, $description, $optionalPriceText)
    {
        $this->product           = $product;
        $this->locale            = $locale;
        $this->title             = $title;
        $this->description       = $description;
        $this->optionalPriceText = $optionalPriceText;
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getOptionalPriceText()
    {
        return $this->optionalPriceText;
    }
}
