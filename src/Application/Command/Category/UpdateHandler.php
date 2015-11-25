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

class UpdateHandler
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
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $category = $update->category;

        foreach ($update->translations as $locale => $translation) {
            $category->getTranslations()->get($locale)->update($translation['title']);
        }

        $this->categoryRepository->set($category);
    }
}
