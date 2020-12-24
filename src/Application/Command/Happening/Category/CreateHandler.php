<?php

namespace Proximum\Vimeet\Application\Command\Happening\Category;

use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\CategoryTranslation;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;

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
        $category = new Category(
            $create->event,
            $create->picto,
            $create->rank,
            $create->leftColor,
            $create->rightColor
        );

        foreach ($create->translations as $locale => $translation) {
            $category->setTranslation(new CategoryTranslation($category, $locale, $translation['title']));
        }

        $this->categoryRepository->add($category);
    }
}
