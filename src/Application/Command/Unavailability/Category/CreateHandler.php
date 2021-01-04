<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Category;

use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Repository\Unavailability\CategoryRepositoryInterface;

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
            $create->title,
            $create->leftColor,
            $create->rightColor
        );

        $this->categoryRepository->create($category);
    }
}
