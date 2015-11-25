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

class CreateHandler
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
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $category = new Category($create->event);

        foreach ($create->translations as $locale => $translation) {
            $category->getTranslations()->set(
                $locale,
                new CategoryTranslation($category, $locale, $translation['title'])
            );
        }

        $this->categoryRepository->add($category);

        $create->category = $category;
    }
}
