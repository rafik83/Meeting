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
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Bundle\InfrastructureBundle\Doctrine\ORM\QueryBuilder\Sheet\SearchQueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class SheetRepository implements SheetRepositoryInterface
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
     * @var Paginator
     */
    private $paginator;

    /**
     * SheetRepository constructor.
     *
     * @param EntityManager           $entityManager
     * @param Paginator               $paginator
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(
        EntityManager $entityManager,
        Paginator $paginator,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->entityManager  = $entityManager;
        $this->paginator      = $paginator;
        $this->typeRepository = $typeRepository;

    }

    /**
     * {@inheritdoc}
     */
    public function add(Sheet $sheet)
    {
        $this->entityManager->persist($sheet);
        $this->entityManager->flush($sheet);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Sheet $sheet)
    {
        $this->entityManager->flush($sheet);
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(array $filters, $page, $limit, Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, type, category, typeTranslation')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join('sheet.type', 'type', 'WITH', 'type.event = :event')
            ->join('type.categories', 'category')
            ->setParameter('event', $event)
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->setParameter('locale', $locale)
            ->join('sheet.participants', 'participant', 'WITH', 'participant.owner = TRUE');

        if (isset($filters['state'])) {
            $queryBuilder
                ->andWhere('sheet.state = :state')
                ->setParameter('state', $filters['state']);
        }

        if (isset($filters['category'])) {
            $queryBuilder
                ->andWhere('category = :category')
                ->setParameter('category', $filters['category']);
        }

        if (isset($filters['type'])) {
            $queryBuilder
                ->andWhere('type = :type')
                ->setParameter('type', $filters['type']);
        }

        if (isset($filters['follower'])) {
            $queryBuilder
                ->andWhere('sheet.follower = :follower')
                ->setParameter('follower', $filters['follower']);
        }

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'sheet', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetViewsByUserAndEvent($user, $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\SheetView(sheet.id, typeTranslation.title)')
            ->from('Entity:Sheet', 'sheet', 'sheet.id')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameter('user', $user)
            ->join('sheet.type', 'type')
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('sheet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetByUserAndEvent(User $user, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from('Entity:Sheet', 'sheet', 'sheet.id')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameter('user', $user)
            ->where('sheet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetById($sheetId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->where('sheet.id = :sheetId')
            ->setParameter('sheetId', $sheetId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsById(array $ids)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->where('sheet.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function search($category, $user)
    {
        $queryBuilder = new SearchQueryBuilder($this->entityManager);
        $queryBuilder->withCategory($category);
        $queryBuilder->withTypes($this->typeRepository->getSeeableTypeIdsByUser($user));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param User  $user
     * @param array $types
     *
     * @return Sheet[]
     */
    public function getUserSheetsByTypes(User $user, array $types)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameter('user', $user)
            ->where('sheet.type IN (:types)')
            ->setParameter('types', $types);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdsByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet.id')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->where('sheet.event = :event')
            ->setParameter('event', $event);

        return array_keys($queryBuilder->getQuery()->getResult());
    }
}
