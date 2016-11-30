<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening\Category;

use Proximum\Vimeet\Domain\Model\Happening\Category;

class Update
{
    /**
     * @var int
     */
    public $position;

    /**
     * @var Category
     */
    public $category;

    /**
     * @var string
     */
    public $picto;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var string
     */
    public $leftColor;

    /**
     * @var string
     */
    public $rightColor;

    /**
     * Update constructor.
     *
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category   = $category;
        $this->picto      = $category->getPicto();
        $this->position   = $category->getPosition();
        $this->leftColor  = $category->getLeftColor();
        $this->rightColor = $category->getRightColor();

        foreach ($category->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'title' => $translation->getTitle(),
            ];
        }
    }
}
