<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Repository\Unavailability\CategoryRepositoryInterface;

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
            $category->update($locale, $title, '#EFEFEF', '#EFEFEF');
        }

        $this->categoryRepository->create($category);

        return $category;
    }
}
