<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\Category;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
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
     * @param Event $event
     * @param array $duplicationHelper
     *
     * @return array
     */
    public function duplicate(Event $event, array $duplicationHelper): array
    {
        $categories = $this->categoryRepository->getCategoriesByEvent($event->getDuplicatedFrom());
        $duplicationHelper['category'] = [];

        foreach ($categories as $category) {
            $newCategory = new Category($event);

            foreach ($category->getTranslations() as $locale => $translation) {
                $newCategory
                    ->getTranslations()
                    ->set($locale, new EventTranslation($event, $locale, $translation->getDescription()));
            }

            foreach ($category->getTypes() as $type) {
                $newType = $duplicationHelper['type'][$type->getId()];
                $newCategory->setType($newType, $newType->getId());
            }

            $duplicationHelper['category'][$category->getId()] = $newCategory;
            $this->categoryRepository->add($newCategory);
        }

        return $duplicationHelper;
    }
}
