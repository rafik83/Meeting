<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;

class CategoryManager
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function create(Event $event, string $title = 'Category'): Category
    {
        $category = new Category($event, 'Cocktail', 1, '#EFEFEF', '#EFEFEF');

        foreach ($event->getLocales() as $locale) {
            $category->update($locale, $title);
        }

        $this->categoryRepository->add($category);

        return $category;
    }
}
