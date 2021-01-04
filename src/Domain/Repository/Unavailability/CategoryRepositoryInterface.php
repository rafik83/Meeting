<?php

namespace Proximum\Vimeet\Domain\Repository\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;

interface CategoryRepositoryInterface
{
    /**
     * @param Category $category
     */
    public function create(Category $category);

    /**
     * @param Category $category
     */
    public function update(Category $category);

    /**
     * @param Event $event
     *
     * @return Category[]
     */
    public function findByEvent(Event $event);
}
