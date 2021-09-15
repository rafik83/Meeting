<?php

namespace Proximum\Vimeet\Domain\Event\Category;

use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\CategoryTranslation;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;

class Duplicator
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param Event                 $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     *
     * @return DuplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage): DuplicatorDataStorage
    {
        $categories = $this->categoryRepository->getCategoriesByEvent($event->getDuplicatedFrom());

        foreach ($categories as $category) {
            $newCategory = new Category($event);

            foreach ($category->getTranslations() as $locale => $translation) {
                $newCategory
                    ->getTranslations()
                    ->set($locale, new CategoryTranslation($newCategory, $locale, $translation->getTitle()));
            }

            foreach ($category->getTypes() as $type) {
                $newType = $duplicatorDataStorage->types[$type->getId()];
                $newCategory->setType($newType, $newType->getId());
            }

            $duplicatorDataStorage->categories[$category->getId()] = $newCategory;
            $this->categoryRepository->add($newCategory);
        }

        return $duplicatorDataStorage;
    }
}
