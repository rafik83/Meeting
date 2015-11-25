<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Category;

use Proximum\Vimeet\Domain\Model\Category;

class Update
{
    /**
     * @var Category
     */
    public $category;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category = $category;

        foreach ($category->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'title' => $translation->getTitle(),
            ];
        }
    }
}
