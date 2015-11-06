<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * "Evènement"
 */
class Event
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $title;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var array
     */
    private $locales;

    /**
     * @var string
     */
    private $fallback;

    /**
     * @var string
     */
    private $billingTemplate;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get locales
     *
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Get fallback
     *
     * @return string
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * Has locale
     *
     * @param $locale
     *
     * @return bool
     */
    public function hasLocale($locale)
    {
        return in_array($locale, $this->locales);
    }

    /**
     * @param array $billingTemplate
     */
    public function setBillingTemplate(array $billingTemplate)
    {
        $this->billingTemplate = $billingTemplate;
    }

    /**
     * @return array
     */
    public function getBillingTemplate()
    {
        return $this->billingTemplate;
    }

    /**
     * @param string $title
     * @param array  $locales
     * @param string $fallback
     */
    public function update($title, array $locales, $fallback)
    {
        $this->title    = $title;
        $this->locales  = $locales;
        $this->fallback = $fallback;
    }
}
