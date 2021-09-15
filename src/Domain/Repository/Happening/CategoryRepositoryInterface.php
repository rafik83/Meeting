<?php

namespace Proximum\Vimeet\Domain\Repository\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\View\Happening\CategoryListView;

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
     * @return CategoryListView[]
     */
    public function findByEvent(Event $event, $locale);

    /**
     * @param Type   $type
     * @param string $locale
     *
     * @return CategoryListView[]
     */
    public function getCategoryListViewByType(Type $type, string $locale): array;
}
