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
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Infrastructure\QueryBuilder\Sheet\SearchQueryBuilder;
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
    public function getSheetsMeetingsStats(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, type, typeTranslation')
            ->addSelect('COUNT(DISTINCT meetings_from_requests.id) AS meetingsRequestsNumber')
            ->addSelect('COUNT(DISTINCT meetings_from_propositions.id) AS meetingsPropositionsNumber')
            ->addSelect('(COUNT(DISTINCT meetings_from_propositions.id) + COUNT(DISTINCT meetings_from_requests.id)) AS meetingsTotal')
            ->addSelect('COUNT(DISTINCT requests.id) AS requestsNumber')
            ->addSelect('COUNT(DISTINCT propositions.id) AS propositionsNumber')
            ->addSelect('(COUNT(DISTINCT requests.id) + COUNT(DISTINCT propositions.id)) AS requestsTotal')
            ->addSelect('CASE WHEN (COUNT(DISTINCT requests.id) > 0) THEN COUNT(DISTINCT meetings_from_requests.id)/COUNT(DISTINCT requests.id) * 100 ELSE 0 END AS requestsTransformation')
            ->addSelect('CASE WHEN (COUNT(DISTINCT propositions.id) > 0) THEN COUNT(DISTINCT meetings_from_propositions.id)/COUNT(DISTINCT propositions.id) * 100 ELSE 0 END AS propositionsTransformation')
            ->addSelect('CASE WHEN (COUNT(DISTINCT requests.id) + COUNT(DISTINCT propositions.id) > 0) THEN (COUNT(DISTINCT meetings_from_propositions.id) + COUNT(DISTINCT meetings_from_requests.id))/(COUNT(DISTINCT requests.id) + COUNT(DISTINCT propositions.id)) * 100 ELSE 0 END AS transformationTotal')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join('sheet.type', 'type', 'WITH', 'type.event = :event')
            ->setParameter('event', $event)
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->setParameter('locale', $locale)
            ->leftJoin(Request::class, 'requests', 'WITH', 'requests.from = sheet AND requests.state = :state')
            ->leftJoin(Request::class, 'propositions', 'WITH', 'propositions.to = sheet AND propositions.state = :state')
            ->setParameter('state', Request::STATE_APPROVED)
            ->leftJoin(Meeting::class, 'meetings_from_requests', 'WITH', 'meetings_from_requests.fromSheet = sheet')
            ->leftJoin(Meeting::class, 'meetings_from_propositions', 'WITH', 'meetings_from_propositions.toSheet = sheet')
            ->groupBy('sheet.id')
            ->having('requestsTotal > 0 OR meetingsTotal > 0')
            ->orderBy('transformationTotal', 'desc')
            ->addOrderBy('meetingsTotal', 'desc')
            ->addOrderBy('requestsTotal', 'desc');

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
