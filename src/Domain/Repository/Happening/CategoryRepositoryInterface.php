<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;

interface CategoryRepositoryInterface
{
    /**
     * @param Category $category
     */
    public function add(Category $category);

    /**
     * @param Category $category
     */
    public function set(Category $category);

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Category[]
     */
    public function findByEvent(Event $event, $locale);
}
