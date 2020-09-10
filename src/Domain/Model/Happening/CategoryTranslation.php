<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\AbstractCategoryTranslation;

class CategoryTranslation extends AbstractCategoryTranslation
{
    /**
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
    public function update($title): CategoryTranslation
    {
        $this->title = $title;

        return $this;
    }
}
