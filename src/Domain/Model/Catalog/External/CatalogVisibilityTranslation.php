<?php

namespace Proximum\Vimeet\Domain\Model\Catalog\External;

class CatalogVisibilityTranslation
{
    /** @var int */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $content;

    /** @var string */
    private $locale;

    /** @var CatalogVisibility */
    private $catalogVisibility;

    /**
     * CatalogVisibilityTranslation constructor.
     *
     * @param CatalogVisibility $catalogVisibility
     * @param string            $title
     * @param string            $content
     * @param string            $locale
     *
     * @internal param int $id
     */
    public function __construct(
        CatalogVisibility $catalogVisibility,
        string $title,
        string $content,
        string $locale
    ) {
        $this->title             = $title;
        $this->content           = $content;
        $this->locale            = $locale;
        $this->catalogVisibility = $catalogVisibility;
    }

    /**
     * @param string $title
     * @param string $content
     */
    public function update(string $title, string $content)
    {
        $this->title   = $title;
        $this->content = $content;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }

    /**
     * @return CatalogVisibility
     */
    public function getCatalogVisibility(): CatalogVisibility
    {
        return $this->catalogVisibility;
    }
}
