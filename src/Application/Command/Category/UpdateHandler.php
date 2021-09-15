<?php

namespace Proximum\Vimeet\Application\Command\Category;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UpdateHandler
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        TypeRepositoryInterface $typeRepository,
        JobQueueInterface $jobQueue
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->typeRepository = $typeRepository;
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param Update $update
     *
     * @throws \Exception
     */
    public function handle(Update $update): void
    {
        $category = $update->category;
        $eventTypes = $this->typeRepository->getTypesByEvent($update->event);

        foreach ($update->translations as $locale => $translation) {
            $category->getTranslations()->get($locale)->update($translation['title']);
        }

        // Add Type
        foreach ($update->types as $typeId) {
            if (!isset($eventTypes[$typeId])) {
                throw new \Exception('Type id not found for this event');
            }

            if (!in_array($eventTypes[$typeId], $category->getTypes(), true)) {
                $category->addType($eventTypes[$typeId]);
            }
        }

        $oldTypeIds = array_map(
            static function (Type $type) {
                return $type->getId();
            },
            $category->getTypes()
        );

        $newTypeIds = $update->types;

        // Remove Type
        foreach ($category->getTypes() as $type) {
            if (!in_array($type->getId(), $update->types)) {
                $category->removeType($type);
            }
        }

        $this->categoryRepository->set($category);

        if ($oldTypeIds != $newTypeIds) {
            $allTypeIds = array_unique(array_merge($oldTypeIds, $newTypeIds));
            $this->jobQueue->indexSheetsByTypes($allTypeIds);
        }
    }
}
