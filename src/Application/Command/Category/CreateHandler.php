<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Category;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\CategoryTranslation;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class CreateHandler
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

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
     * @param Create $create
     *
     * @throws \Exception
     */
    public function handle(Create $create)
    {
        $category = new Category($create->event);

        $types      = [];
        $eventTypes = $this->typeRepository->getTypesByEvent($create->event);

        foreach ($eventTypes as $type) {
            $types[$type->getId()] = $type;
        }

        foreach ($create->translations as $locale => $translation) {
            $category->getTranslations()->set(
                $locale,
                new CategoryTranslation($category, $locale, $translation['title'])
            );
        }

        foreach ($create->types as $typeId) {
            if (!isset($types[$typeId])) {
                throw new \Exception('Type id not found for this event');
            }

            $category->getTypes()->set($typeId, $types[$typeId]);
        }

        $this->categoryRepository->add($category);

        $create->category = $category;
    }
}
