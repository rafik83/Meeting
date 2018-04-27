<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening\Category;

use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\CategoryTranslation;

class Update extends AbstractCategory
{
    /** @var Category */
    public $category;

    /**
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category   = $category;
        $this->picto      = $category->getPicto();
        $this->rank       = $category->getRank();
        $this->leftColor  = $category->getLeftColor();
        $this->rightColor = $category->getRightColor();

        /* @var CategoryTranslation $translation */
        foreach ($category->getEvent()->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title' => $category->getTitle($locale),
            ];
        }
    }
}
