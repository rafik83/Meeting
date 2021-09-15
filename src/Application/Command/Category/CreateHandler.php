<?php

namespace Proximum\Vimeet\Application\Command\Category;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
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
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var JobQueueInterface
     */
    private $jobQueue;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param TypeRepositoryInterface     $typeRepository
     * @param JobQueueInterface           $jobQueue
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        TypeRepositoryInterface $typeRepository,
        JobQueueInterface $jobQueue
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->typeRepository     = $typeRepository;
        $this->jobQueue           = $jobQueue;
    }

    /**
     * @param Create $create
     *
     * @throws \Exception
     */
    public function handle(Create $create)
    {
        $category   = new Category($create->event);
        $eventTypes = $this->typeRepository->getTypesByEvent($create->event);

        foreach ($create->translations as $locale => $translation) {
            $category->getTranslations()->set(
                $locale,
                new CategoryTranslation($category, $locale, $translation['title'])
            );
        }

        foreach ($create->types as $typeId) {
            if (!isset($eventTypes[$typeId])) {
                throw new \Exception('Type id not found for this event');
            }

            $category->setType($eventTypes[$typeId], $typeId);
        }

        $this->categoryRepository->add($category);

        $create->category = $category;

        $this->jobQueue->indexSheetsByTypes($create->types);
    }
}
