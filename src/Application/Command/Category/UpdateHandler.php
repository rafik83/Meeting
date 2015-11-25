<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Category;

use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UpdateHandler
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param TypeRepositoryInterface     $typeRepository
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->typeRepository     = $typeRepository;
    }

    /**
     * @param Update $update
     *
     * @throws \Exception
     */
    public function handle(Update $update)
    {
        $category = $update->category;

        $types      = [];
        $eventTypes = $this->typeRepository->getTypesByEvent($update->event);

        foreach ($eventTypes as $type) {
            $types[$type->getId()] = $type;
        }

        foreach ($update->translations as $locale => $translation) {
            $category->getTranslations()->get($locale)->update($translation['title']);
        }

        // add Type
        foreach ($update->types as $typeId) {
            if (!isset($types[$typeId])) {
                throw new \Exception('Type id not found for this event');
            }

            if (!$category->getTypes()->contains($types[$typeId])) {
                 $category->getTypes()->add($types[$typeId]);
            }
        }

        // remove Type
        foreach ($category->getTypes() as $type) {
            if (!in_array($type->getId(), $update->types)) {
                $category->getTypes()->removeElement($type);
            }
        }

        $this->categoryRepository->set($category);
    }
}
