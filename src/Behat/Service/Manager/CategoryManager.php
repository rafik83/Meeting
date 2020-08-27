<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\CategoryTranslation;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class CategoryManager
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->typeRepository = $typeRepository;
    }

    public function create(Event $event, $title): Category
    {
        $category = new Category($event);
        $category->getTranslations()->set(
            $event->getLocaleFallback(),
            new CategoryTranslation($category, $event->getLocaleFallback(), $title)
        );

        $this->categoryRepository->add($category);

        return $category;
    }

    public function addAllTypes(Category $category): void
    {
        $types = $this->typeRepository->getTypesByEvent($category->getEvent());

        foreach ($types as $type) {
            $category->addType($type);
        }

        $this->categoryRepository->set($category);
    }
}
