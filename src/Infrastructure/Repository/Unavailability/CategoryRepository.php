<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Unavailability;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Repository\Unavailability\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Category $category)
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush($category);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Category $category)
    {
        $this->entityManager->flush($category);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('category.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}
