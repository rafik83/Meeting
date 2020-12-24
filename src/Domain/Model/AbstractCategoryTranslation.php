<?php

namespace Proximum\Vimeet\Domain\Model;

class AbstractCategoryTranslation
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var AbstractCategory
     */
    protected $category;

    /**
     * @var string
     */
    protected $title;

    /**
     * @param AbstractCategory $category
     * @param string           $locale
     * @param string           $title
     */
    public function __construct(AbstractCategory $category, $locale, $title)
    {
        $this->category = $category;
        $this->locale   = $locale;
        $this->title    = $title;
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
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return AbstractCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param AbstractCategory $category
     */
    public function setCategory(AbstractCategory $category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $title
     *
     * @return AbstractCategoryTranslation
     */
    public function update($title)
    {
        $this->title = $title;

        return $this;
    }
}
