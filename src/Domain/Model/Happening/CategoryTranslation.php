<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\AbstractCategoryTranslation;

class CategoryTranslation extends AbstractCategoryTranslation
{
    /**
     * CategoryTranslation constructor.
     *
     * @param Category $category
     * @param string   $locale
     * @param string   $title
     */
    public function __construct(Category $category, $locale, $title)
    {
        parent::__construct($category, $locale, $title);
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

    /**
     * @return CategoryTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations->toArray();
    }
}
