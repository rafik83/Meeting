<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Catalog;

use Proximum\Vimeet\Domain\Model\TypeTranslation;

class AbstractSearchFacetTranslation
{
    /** @var int */
    protected $id;

    /** @var AbstractSearchFacet */
    protected $searchFacet;

    /** @var string */
    protected $label;

    /** @var string */
    protected $placeholder;

    /** @var string */
    protected $locale;

    /**
     * AbstractSearchFacetTranslation constructor.
     *
     * @param AbstractSearchFacet $searchFacet
     * @param string      $label
     * @param string      $placeholder
     * @param string      $locale
     */
    public function __construct(AbstractSearchFacet $searchFacet, $label, $placeholder, $locale)
    {
        $this->searchFacet = $searchFacet;
        $this->label       = $label;
        $this->placeholder = $placeholder;
        $this->locale      = $locale;
    }

    /**
     * @return AbstractSearchFacet
     */
    public function getSearchFacet(): AbstractSearchFacet
    {
        return $this->searchFacet;
    }

    /**
     * @param AbstractSearchFacet $searchFacet
     */
    public function setSearchFacet($searchFacet)
    {
        $this->searchFacet = $searchFacet;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    /**
     * @param string $placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $label
     * @param string $placeholder
     *
     * @return TypeTranslation
     */
    public function update($label = '', $placeholder = '')
    {
        $this->label       = $label;
        $this->placeholder = $placeholder;

        return $this;
    }
}
