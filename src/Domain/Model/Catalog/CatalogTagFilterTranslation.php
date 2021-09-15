<?php

namespace Proximum\Vimeet\Domain\Model\Catalog;

class CatalogTagFilterTranslation
{
    /** @var int */
    private $id;

    /** @var string */
    private $locale;

    /** @var null|string */
    private $label;

    /** @var null|string */
    private $placeholder;

    /** @var CatalogTagFilter */
    private $catalogTagFilter;

    public function __construct(string $locale, ?string $label, ?string $placeholder)
    {
        $this->locale = $locale;
        $this->label = $label;
        $this->placeholder = $placeholder;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getCatalogTagFilter(): CatalogTagFilter
    {
        return $this->catalogTagFilter;
    }

    public function setCatalogTagFilter(CatalogTagFilter $catalogTagFilter): void
    {
        $this->catalogTagFilter = $catalogTagFilter;
    }
}
