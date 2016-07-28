<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Infrastructure\QueryBuilder\Sheet\SearchQueryBuilder;

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
     * SheetRepository constructor.
     *
     * @param EntityManager           $entityManager
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(
        EntityManager $entityManager,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->entityManager  = $entityManager;
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
    public function getByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, participants')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.participants', 'participants')
            ->where('sheet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheets(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, participants, type, typeTranslation')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join('sheet.participants', 'participants')
            ->join('sheet.type', 'type', 'WITH', 'type.event = :event')
            ->setParameter('event', $event)
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()->getResult();
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
            ->join('sheet.type', 'type')
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('sheet.event = :event')
            ->setParameter('event', $event)
            ->andWhere('sheet.owner = :user OR EXISTS (SELECT p.id FROM Entity:Participant p WHERE p.user = :user AND p.sheet = sheet)')
            ->setParameter('user', $user);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsByUserAndEvent(User $user, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join('sheet.participants', 'participant')
            ->where('sheet.event = :event')
            ->setParameter('event', $event->getId())
            ->andWhere('sheet.owner = :user OR participant.user = :user')
            ->setParameter('user', $user);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsByUserAndEventWhereUserIsParticipant(User $user, EventInterface $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameter('user', $user)
            ->where('sheet.event = :event')
            ->setParameter('event', $event->getId());

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
     * {@inheritdoc}
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

    /**
     * {@inheritdoc}
     */
    public function findFullSheets(array $sheets)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, participant, type, typeTranslation, category, categoryTranslation, user')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->leftJoin('sheet.participants', 'participant')
            ->leftJoin('participant.user', 'user')
            ->leftJoin('sheet.type', 'type')
            ->leftJoin('type.translations', 'typeTranslation')
            ->leftJoin('type.categories', 'category')
            ->leftJoin('category.translations', 'categoryTranslation')
            ->where('sheet.id IN (:sheets)')
            ->setParameter('sheets', $sheets);

        $results = $queryBuilder->getQuery()->getResult();

        foreach ($sheets as $key => $sheet) {
            $sheets[$key] = $results[$sheet instanceof Sheet ? $sheet->getId() : $sheet];
        }

        return $sheets;
    }

    /**
     * {@inheritdoc}
     */
    public function countByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(sheet.id)')
            ->from(Sheet::class, 'sheet')
            ->where('sheet.event = :event')
            ->setParameter('event', $event);

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByType(Type $type)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->where('sheet.type = :type')
            ->setParameter('type', $type);

        return $queryBuilder->getQuery()->getResult();
    }
}
