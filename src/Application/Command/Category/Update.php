<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Category;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;

class Update
{
    /**
     * @var Category
     */
    public $category;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var array
     */
    public $types = [];

    /**
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
        $this->event    = $category->getEvent();

        foreach ($category->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'title' => $translation->getTitle(),
            ];
        }

        foreach ($category->getTypes() as $type) {
            $this->types[] = $type->getId();
        }
    }
}
