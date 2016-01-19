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
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * SheetRepository constructor.
     *
     * @param EntityManager           $entityManager
     * @param TypeRepositoryInterface $typeRepository
     * @param PaginatorInterface      $paginator
     */
    public function __construct(
        EntityManager $entityManager,
        TypeRepositoryInterface $typeRepository,
        PaginatorInterface $paginator
    ) {
        $this->entityManager  = $entityManager;
        $this->typeRepository = $typeRepository;
        $this->paginator      = $paginator;
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
    public function paginate($page, $limit, Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, type, typeTranslation')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join('sheet.type', 'type', 'WITH', 'type.event = :event')
            ->setParameter('event', $event)
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->setParameter('locale', $locale)
            ->join('sheet.participants', 'participant', 'WITH', 'participant.owner = TRUE');

        return $this->paginator->paginate($queryBuilder, $page, $limit);
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
    public function getSheetById($sheetId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from('Entity:Sheet', 'sheet')
            ->where('sheet.id = :sheetId')
            ->setParameter('sheetId', $sheetId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
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
            ->from('Entity:Sheet', 'sheet')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameter('user', $user)
            ->where('sheet.type IN (:types)')
            ->setParameter('types', $types);

        return $queryBuilder->getQuery()->getResult();
    }
}
