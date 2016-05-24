<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class PackageTranslation
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
     * @var Package
     */
    private $package;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $descriptionTitle;

    /**
     * @var string
     */
    private $descriptionContent;

    /**
     * @var string
     */
    private $optionalPriceText;

    /**
     * @param Package $package
     * @param string  $locale
     * @param string  $title
     * @param string  $descriptionTitle
     * @param string  $descriptionContent
     * @param string  $optionalPriceText
     */
    public function __construct(Package $package, $locale, $title, $descriptionTitle, $descriptionContent, $optionalPriceText)
    {
        $this->package            = $package;
        $this->locale             = $locale;
        $this->title              = $title;
        $this->descriptionTitle   = $descriptionTitle;
        $this->descriptionContent = $descriptionContent;
        $this->optionalPriceText  = $optionalPriceText;
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
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
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
    public function getDescriptionTitle()
    {
        return $this->descriptionTitle;
    }

    /**
     * @return string
     */
    public function getDescriptionContent()
    {
        return $this->descriptionContent;
    }

    /**
     * @return string
     */
    public function getOptionalPriceText()
    {
        return $this->optionalPriceText;
    }
}
