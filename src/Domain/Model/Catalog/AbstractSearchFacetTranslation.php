<?php

namespace Proximum\Vimeet\Domain\Model\Catalog;

class AbstractSearchFacetTranslation
{
    /** @var int */
    protected $id;

    /** @var AbstractSearchFacet */
    protected $searchFacet;

    /** @var string|null */
    protected $label;

    /** @var string|null */
    protected $placeholder;

    /** @var string */
    protected $locale;

    /**
     * AbstractSearchFacetTranslation constructor.
     *
     * @param AbstractSearchFacet $searchFacet
     * @param string|null         $label
     * @param string|null         $placeholder
     * @param string              $locale
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
     * @return string|null
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
     * @return string|null
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
     * @return AbstractSearchFacetTranslation
     */
    public function update($label = '', $placeholder = ''): AbstractSearchFacetTranslation
    {
        $this->label       = $label;
        $this->placeholder = $placeholder;

        return $this;
    }
}
