<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class SearchFacetTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var SearchFacet
     */
    private $searchFacet;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $placeholder;

    /**
     * @var string
     */
    private $locale;

    /**
     * SearchFacetTranslation constructor.
     *
     * @param SearchFacet $searchFacet
     * @param string      $label
     * @param string      $placeholder
     * @param string      $locale
     */
    public function __construct(SearchFacet $searchFacet, $label, $placeholder, $locale)
    {
        $this->searchFacet = $searchFacet;
        $this->label       = $label;
        $this->placeholder = $placeholder;
        $this->locale      = $locale;
    }

    /**
     * @return SearchFacet
     */
    public function getSearchFacet()
    {
        return $this->searchFacet;
    }

    /**
     * @param SearchFacet $searchFacet
     */
    public function setSearchFacet($searchFacet)
    {
        $this->searchFacet = $searchFacet;
    }

    /**
     * @return string
     */
    public function getLabel()
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
    public function getPlaceholder()
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
    public function getLocale()
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
    public function getId()
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
