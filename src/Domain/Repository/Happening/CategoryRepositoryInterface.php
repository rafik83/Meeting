<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
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
     * @param Event $event
     * @return Category[]
     */
    public function findByEvent(Event $event);
}
