<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Happening;

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
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
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
}
