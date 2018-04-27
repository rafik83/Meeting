<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Happening;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;

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
    public function add(Category $category)
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush($category);
    }

    /**
     * @param Category $category
     */
    public function set(Category $category)
    {
        $this->entityManager->flush($category);

        foreach ($category->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\Happening\CategoryListView(category.id, translation.title, category.picto, category.rank, category.leftColor, category.rightColor)')
            ->from(Category::class, 'category')
            ->join('category.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->where('category.event = :event')
            ->orderBy('category.rank')
            ->setParameter('locale', $locale)
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}
