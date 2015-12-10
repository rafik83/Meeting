<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository;

use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @param EntityManager           $entityManager
     * @param PaginatorInterface      $paginator
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(
        EntityManager $entityManager,
        PaginatorInterface $paginator,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
        $this->typeRepository = $typeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Category $category)
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush($category);

        foreach ($category->getTypes() as $type) {
            $this->entityManager->flush($type);
        }

        foreach ($category->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(Category $category)
    {
        $this->entityManager->flush($category);

        foreach ($category->getTypes() as $type) {
            $this->entityManager->flush($type);
        }

        foreach ($category->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($page, $limit, $eventId, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\CategoryListView(category.id, translation.title)')
            ->from('Entity:Category', 'category')
            ->join('category.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->join('category.event', 'event', 'WITH', 'event.id = :eventId')
            ->setParameter('locale', $locale)
            ->setParameter('eventId', $eventId);

        return $this->paginator->paginate($queryBuilder, $page, $limit, [
            'defaultSortFieldName' => 'category.id',
            'defaultSortDirection' => 'ASC',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryViewsByEventAndUser($event, $user, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('DISTINCT NEW Proximum\Vimeet\Domain\View\CategoryView(category.id, translation.title)')
            ->from('Entity:Category', 'category')
            ->join('category.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->join('category.types', 'type', 'WITH', 'type IN (:seeableType)')
            ->setParameter('seeableType', $this->typeRepository->getSeeableTypeIdsByUser($user))
            ->where('category.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoriesByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('category')
            ->from('Entity:Category', 'category')
            ->where('category.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryViewById($id, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\CategoryView(category.id, translation.title)')
            ->from('Entity:Category', 'category')
            ->join('category.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('category.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
