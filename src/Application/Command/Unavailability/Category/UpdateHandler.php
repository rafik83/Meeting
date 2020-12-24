<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Category;

use Proximum\Vimeet\Domain\Repository\Unavailability\CategoryRepositoryInterface;

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
        $update->category->update(
            $update->picto,
            $update->title,
            $update->leftColor,
            $update->rightColor
        );

        $this->categoryRepository->update($update->category);
    }
}
