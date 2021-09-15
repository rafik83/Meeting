<?php

namespace Proximum\Vimeet\Domain\Model;

class CategoryTranslation
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
     * @var Category
     */
    private $category;

    /**
     * @var string
     */
    private $title;

    /**
     * CategoryTranslation constructor.
     *
     * @param Category $category
     * @param string   $locale
     * @param string   $title
     */
    public function __construct(Category $category, $locale, $title)
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
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
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
     *
     * @return CategoryTranslation
     */
    public function update($title)
    {
        $this->title = $title;

        return $this;
    }
}
