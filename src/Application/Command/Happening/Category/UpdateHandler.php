<?php

namespace Proximum\Vimeet\Application\Command\Happening\Category;

use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;

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
        $category->setPicto($update->picto);
        $category->setRank($update->rank);
        $category->setLeftColor($update->leftColor);
        $category->setRightColor($update->rightColor);

        foreach ($update->translations as $locale => $translation) {
            $category->update($locale, $translation['title']);
        }

        $this->categoryRepository->set($category);
    }
}
