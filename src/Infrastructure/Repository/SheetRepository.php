<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\FilterRequestView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventInterface;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\View\Rooming\SheetView;
use Proximum\Vimeet\Domain\View\Spot\Import\SheetView as ImportSheetView;

class SheetRepository implements SheetRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * SheetRepository constructor.
     *
     * @param EntityManager $entityManager
     * @param Paginator     $paginator
     */
    public function __construct(EntityManager $entityManager, Paginator $paginator)
    {
        $this->entityManager = $entityManager;
        $this->paginator     = $paginator;
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
    public function getByEvent(Event $event): array
    {
        $queryBuilder = $this->getByEventQueryBuilder($event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventAndOrderedByTitle(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf(
                    'new %s(sheet.id, sheet.title)',
                    SheetView::class
                )
            )
            ->from(Sheet::class, 'sheet')
            ->where('sheet.event = :event')
            ->setParameter('event', $event)
            ->orderBy('sheet.title');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventWithParticipantsAndOwner(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, participant, owner')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.owner', 'owner')
            ->join('sheet.participants', 'participant')
            ->where('sheet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getOwnerEmails(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('owner.email AS email')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.owner', 'owner', 'WITH', 'sheet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsInCatalogByEvent(Event $event, array $excludedSheets = []): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, participants')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.participants', 'participants', 'WITH', 'sheet.event = :event AND sheet.inCatalog = true')
            ->setParameter('event', $event);

        if (!empty($excludedSheets)) {
            $queryBuilder->where('sheet NOT IN (:excludedSheets)');
            $queryBuilder->setParameter('excludedSheets', $excludedSheets);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated This request is not used anymore.
     */
    public function countAvailableSheetsInCatalogWithTypesByEvent(
        Event $event,
        array $types = [],
        MeetingSlot $slot,
        array $excludedSheets = []
    ): int {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('count(sheet.id)')
            ->from(Sheet::class, 'sheet')
            ->join(
                'sheet.availableSlots',
                'availableSlot',
                'WITH',
                'availableSlot.slot = :slot AND sheet.event = :event
                AND sheet.inCatalog = true AND sheet.enable = true AND sheet.type IN (:types) AND sheet.attend = true'
            )
            ->setParameter('slot', $slot)
            ->setParameter('types', $types)
            ->setParameter('event', $event)
        ;

        if (!empty($excludedSheets)) {
            $queryBuilder->andWhere('sheet NOT IN (:excludedSheets)');
            $queryBuilder->setParameter('excludedSheets', $excludedSheets);
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsMetBySheet(Sheet $sheet): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->where('sheet.event = :event AND sheet != :sheet AND EXISTS (
                   SELECT meeting.id FROM Entity:Meeting meeting WHERE (
                        meeting.fromSheet = :sheet AND meeting.toSheet = sheet
                        OR meeting.toSheet = :sheet AND meeting.fromSheet = sheet
                   )
            )')
            ->setParameter('event', $sheet->getEvent())
            ->setParameter('sheet', $sheet);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsWithRequestWithSheet(Sheet $sheet): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->where('sheet.event = :event AND sheet != :sheet AND EXISTS (
                   SELECT request.id FROM Entity:Meeting\Request request WHERE
                   request.event = :event AND
                   (
                        request.from = :sheet AND request.to = sheet
                        OR request.to = :sheet AND request.from = sheet
                   )
            )')
            ->setParameter('event', $sheet->getEvent())
            ->setParameter('sheet', $sheet);

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
            ->join('sheet.type', 'type', 'WITH', 'type.event = :event')
            ->join('sheet.participants', 'participants')
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
    public function getSheetViewByEventAndId(Event $event, int $sheetId): ? ImportSheetView
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\Spot\Import\SheetView(sheet.id, sheet.title)')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->where('sheet.event = :event AND sheet.id = :sheetId')
            ->setParameter('event', $event)
            ->setParameter('sheetId', $sheetId)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
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
            ->join(
                'sheet.participants',
                'participant',
                'WITH',
                '(sheet.owner = :user OR participant.user = :user) AND sheet.event = :event AND sheet.enable = true'
            )
            ->setParameter('event', $event)
            ->setParameter('user', $user)
            ->groupBy('sheet.id')
            ->orderBy('sheet.title')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsByUsersAndEvent(array $users, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join(
                'sheet.participants',
                'participant',
                'WITH',
                '(sheet.owner IN (:users) OR participant.user IN (:users)) AND sheet.event = :event AND sheet.enable = true'
            )
            ->setParameter('event', $event)
            ->setParameter('users', $users)
            ->groupBy('sheet.id')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countSheetsByUserAndEvent(User $user, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(DISTINCT sheet.id)')
            ->from(Sheet::class, 'sheet')
            ->join(
                'sheet.participants',
                'participant',
                'WITH',
                'sheet.event = :event AND sheet.enable = true AND (sheet.owner = :user OR participant.user = :user)'
            )
            ->setParameter('event', $event)
            ->setParameter('user', $user);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllSheetsByUserAndEvent(User $user, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join(
                'sheet.participants',
                'participant',
                'WITH',
                'sheet.event = :event AND (sheet.owner = :user OR participant.user = :user)'
            )
            ->setParameter('event', $event)
            ->setParameter('user', $user);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param User           $user
     * @param EventInterface $event
     *
     * @return QueryBuilder
     */
    private function sheetsByUserAndEventWhereUserIsParticipantQueryBuilder(User $user, EventInterface $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join(
                'sheet.participants',
                'participant',
                'WITH',
                'sheet.event = :event AND sheet.enable = true AND participant.user = :user'
            )
            ->setParameter('user', $user)
            ->setParameter('event', $event->getId());

        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsByUserAndEventWhereUserIsParticipant(User $user, EventInterface $event)
    {
        $queryBuilder = $this->sheetsByUserAndEventWhereUserIsParticipantQueryBuilder($user, $event);

        return $queryBuilder->select('sheet')->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isParticipantToEnabledSheet(User $user, EventInterface $event)
    {
        $queryBuilder = $this->sheetsByUserAndEventWhereUserIsParticipantQueryBuilder($user, $event);

        return null !== $queryBuilder
                ->select('sheet.id')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
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
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->where('sheet.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('sheet.id');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsByIdOrdered(array $ids, string $orderBy): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->where('sheet.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('sheet.id');

        if (Sheet\Constant::ORDER_BY_CREATED_AT === $orderBy) {
            $queryBuilder->orderBy('sheet.createdAt', 'asc');
        } elseif (Sheet\Constant::ORDER_BY_ALPHABETICAL === $orderBy) {
            $queryBuilder->orderBy('sheet.title', 'asc');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsByEventAndIds(Event $event, array $ids)
    {
        $queryBuilder = $this
            ->findByIdsQueryBuilder($ids)
            ->andWhere('sheet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsMetBySheets(
        Event $event,
        array $sheets,
        $state = null,
        $type = null,
        $user = null
    ) {
        $queryBuilder = $this->getSheetsMetBySheetsBuilder($event, $sheets, $state, $type, $user);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsMetBySheetsPaginated(
        Event $event,
        array $sheets,
        $page,
        $limit,
        $state = null,
        $type = null,
        $user = null
    ) {
        $queryBuilder = $this->getSheetsMetBySheetsBuilder($event, $sheets, $state, $type, $user);

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'sheet', 'id');
    }

    /**
     * @param Event            $event
     * @param Sheet[]          $sheets
     * @param string|null      $state
     * @param string|null      $type
     * @param User|string|null $user
     *
     * @return QueryBuilder
     */
    private function getSheetsMetBySheetsBuilder(
        Event $event,
        array $sheets,
        $state = null,
        $type = null,
        $user = null
    ) {
        // condition for filter sheet meeting request using sql exists
        $typeCondition  = '(r.from = sheet AND r.to IN (:sheets) OR r.to = sheet AND r.from IN (:sheets))';
        $stateCondition = '1 = 1';
        $userCondition  = '1 = 1';
        $userJoinCondition = '';

        if (null !== $type) {
            if (Request::TYPE_REQUEST === $type) {
                $typeCondition = 'r.to = sheet AND r.from IN (:sheets)';
            } elseif (Request::TYPE_PROPOSITION === $type) {
                $typeCondition = 'r.from = sheet AND r.to IN (:sheets)';
            }
        }

        if (null !== $state && in_array($state, Request::getAllStates())) {
            $stateCondition = sprintf("r.state = '%s'", $state);
        }

        if ($user instanceof User) {
            $userJoinCondition = 'LEFT JOIN r.fromParticipants fp LEFT JOIN r.toParticipants tp';
            $userCondition = '(fp.user = :user OR tp.user = :user)';
        }

        // filter meeting request with fromParticipants or toParticipants empty
        if (FilterRequestView::NO_PREFERENCE === $user) {
            $userJoinCondition = 'LEFT JOIN r.fromParticipants fp LEFT JOIN r.toParticipants tp';
            $typeCondition  =
                '((r.from = sheet AND r.to IN (:sheets) AND tp.id IS NULL)
                OR
                (r.to = sheet AND r.from IN (:sheets) AND fp.id IS NULL))';
        }

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet', 'type', 'type_translations')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join('sheet.type', 'type')
            ->join('type.translations', 'type_translations')
            ->where('sheet.event = :event AND sheet.enable = true AND sheet.inCatalog = true')
            ->andWhere(
                sprintf(
                    'EXISTS (
                    SELECT r.id FROM Entity:Meeting\Request r
                    %s
                    WHERE %s AND %s AND %s
                )', $userJoinCondition, $typeCondition, $stateCondition, $userCondition)
            )
            ->setParameter('event', $event)
            ->setParameter('sheets', $sheets)
            ->orderBy('sheet.title', 'asc')
        ;

        if ($user instanceof User) {
            $queryBuilder->setParameter('user', $user);
        }

        if (Request::STATE_PLANNED === $state) {
            $queryBuilder
                ->join(Request::class,
                    'request',
                    'WITH',
                    'request.from = sheet or request.to = sheet'
                )
                ->andWhere('EXISTS(SELECT m.id FROM Entity:Meeting m where m.request = request)');
        }

        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnvalidatedSheetsById(array $ids)
    {
        $queryBuilder = $this->findByIdsQueryBuilder($ids);

        $queryBuilder
            ->andWhere('sheet.state != :state')
            ->setParameter('state', Sheet::STATE_VALIDATED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsUnacceptedById(array $ids)
    {
        $queryBuilder = $this->findByIdsQueryBuilder($ids);
        $queryBuilder
            ->andWhere('sheet.state != :state')
            ->setParameter('state', Sheet::STATE_ACCEPTED);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsNotPendingById(array $ids): array
    {
        $queryBuilder = $this->findByIdsQueryBuilder($ids);
        $queryBuilder
            ->andWhere('sheet.state != :state')
            ->setParameter('state', Sheet::STATE_PENDING);

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
    public function findSheets(array $sheets)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, type, typeTranslation')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->leftJoin('sheet.type', 'type')
            ->leftJoin('type.translations', 'typeTranslation')
            ->where('sheet.id IN (:sheets)')
            ->setParameter('sheets', $sheets);

        $results = $queryBuilder->getQuery()->getResult();

        // Reorder results
        $resultsOrdered = [];

        foreach ($sheets as $sheet) {
            $resultsOrdered[] = $results[$sheet->getId()];
        }

        return $resultsOrdered;
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsByIdsWithTypesAndCategories(array $sheetIds, string $locale): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, type, typeTranslation, category, categoryTranslation')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join('sheet.type', 'type', 'WITH', 'sheet.id IN (:sheetIds)')
            ->leftJoin('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->leftJoin('type.categories', 'category')
            ->leftJoin('category.translations', 'categoryTranslation', 'WITH', 'categoryTranslation.locale = :locale')
            ->setParameter('sheetIds', $sheetIds)
            ->setParameter('locale', $locale)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $sheetIds)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, participant')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join('sheet.participants', 'participant')
            ->where('sheet.id IN (:sheets)')
            ->setParameter('sheets', $sheetIds);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findFullSheets(array $sheets)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, participant, type, typeTranslation, category, categoryTranslation, user, linked_sheets')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->leftJoin('sheet.participants', 'participant')
            ->leftJoin('participant.user', 'user')
            ->leftJoin('sheet.type', 'type')
            ->leftJoin('type.translations', 'typeTranslation')
            ->leftJoin('type.categories', 'category')
            ->leftJoin('category.translations', 'categoryTranslation')
            ->leftJoin('sheet.linkedSheets', 'linked_sheets')
            ->leftJoin('linked_sheets.sheets', 'linked_sheets_sheet')
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

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isThereAtLeastOneByType(Type $type)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet.id')
            ->from(Sheet::class, 'sheet')
            ->where('sheet.type = :type')
            ->setParameter('type', $type)
            ->setMaxResults(1)
        ;

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabledSheetsByEvent(Event $event)
    {
        $queryBuilder = $this->queryEnabledSheetsByEvent($event);

        $queryBuilder->select('sheet, owner, type');
        $queryBuilder->join('sheet.owner', 'owner');
        $queryBuilder->join('sheet.type', 'type');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countEnabledSheetsByEvent(Event $event)
    {
        $queryBuilder = $this->queryEnabledSheetsByEvent($event);

        $queryBuilder->select('COUNT(sheet)');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findEnabledByEvent(Event $event): array
    {
        $queryBuilder = $this->queryEnabledSheetsByEvent($event);
        $queryBuilder->select('sheet');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Event $event
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function queryEnabledSheetsByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->from(Sheet::class, 'sheet')
            ->where('sheet.event = :event')
            ->andWhere('sheet.enable = true')
            ->setParameter('event', $event);

        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function countEnabledSheetsTypeByEvent(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(sheet) as total, type.id, typeTranslation.title')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.type', 'type')
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->where('sheet.event = :event')
            ->andWhere('sheet.enable = :enable')
            ->groupBy('type')
            ->setParameter('event', $event)
            ->setParameter('enable', true)
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByUser(User $user): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->leftJoin('sheet.participants', 'participant')
            ->andWhere('sheet.owner = :user OR participant.user = :user')
            ->setParameter('user', $user)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByGroup(Group $group)
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->where('sheet.group = :group AND sheet.enable=true')
            ->setParameter('group', $group)
            ->orderBy('sheet.title')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $ids
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function findByIdsQueryBuilder(array $ids)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->where('sheet.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function updateInCatalogBySheetsId(array $ids, $state)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->update(Sheet::class, 'sheet')
            ->set('sheet.inCatalog', ':state')
            ->where('sheet.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->setParameter('state', $state);

        if (true === $state) {
            $queryBuilder->set('sheet.inCatalogAt', ':date')
                ->setParameter('date', new \DateTime());
        }

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function updateEnableStateBySheetsId(array $ids, $state)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->update(Sheet::class, 'sheet')
            ->set('sheet.enable', ':state')
            ->where('sheet.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->setParameter('state', $state);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function updateStateBySheetsId(array $ids, $state)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->update(Sheet::class, 'sheet')
            ->set('sheet.state', ':state')
            ->setParameter('state', $state)
            ->where('sheet.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->andWhere('sheet.state != :state')
        ;

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function refuseBySheetsId(array $ids)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->update(Sheet::class, 'sheet')
            ->set('sheet.state', ':state')
            ->set('sheet.inCatalog', ':inCatalog')
            ->where('sheet.id IN (:ids)')
            ->setParameter('state', Sheet::STATE_REFUSED)
            ->setParameter('inCatalog', false)
            ->setParameter('ids', $ids)
        ;

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getBySheetTemplate(SheetTemplate $sheetTemplate)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.type', 'type', 'WITH', 'type.sheetTemplate = :sheetTemplate')
            ->setParameter('sheetTemplate', $sheetTemplate);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function updateValidationState(array $ids, $state)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->update(Sheet::class, 'sheet')
            ->where('sheet.id IN (:ids)')
            ->andWhere('sheet.validationState != :state')
            ->set('sheet.validationState', ':state')
            ->setParameter('ids', $ids)
            ->setParameter('state', $state);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getByRegistrationTemplate(RegistrationTemplate $registrationTemplate)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.type', 'type', 'WITH', 'type.registrationTemplate = :registrationTemplate')
            ->setParameter('registrationTemplate', $registrationTemplate);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function batchAssignBySheetsId(array $ids, Admin $admin)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->update(Sheet::class, 'sheet')
            ->set('sheet.follower', ':follower')
            ->where('sheet.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->setParameter('follower', $admin);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function batchUnAssignBySheetsId(array $ids)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->update(Sheet::class, 'sheet')
            ->set('sheet.follower', 'NULL')
            ->where('sheet.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getByTypes(array $types)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->where('sheet.type IN (:types)')
            ->setParameter('types', $types);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasSheetWithGroupByUserByEvent(User $user, Event $event): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet.id')
            ->from(Sheet::class, 'sheet')
            ->join(
                'sheet.participants',
                'participant',
                'WITH',
                '(sheet.owner = :user OR participant.user = :user)
                AND sheet.event = :event
                AND sheet.enable = true
                AND sheet.group IS NOT NULL'
            )
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasSheetOutOfGroup(User $user, Group $group): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet.id')
            ->from(Sheet::class, 'sheet')
            ->join(
                'sheet.participants',
                'participant',
                'WITH',
                'sheet.event = :event
                AND (sheet.owner = :user OR participant.user = :user)
                AND sheet.enable = true
                AND (sheet.group IS NULL OR sheet.group != :group)'
            )
            ->setParameter('user', $user)
            ->setParameter('group', $group)
            ->setParameter('event', $group->getEvent())
            ->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isUserParticipantMultipleSheetsInEvent(User $user, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet.id')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user AND sheet.event = :event')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
        ;

        return count($queryBuilder->getQuery()->getResult()) > 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetByEventAndTitle(Event $event, $title)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->where('sheet.event = :event AND sheet.title = :title')
            ->setParameter('title', $title)
            ->setParameter('event', $event)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByTypesAndWithoutGivenExtraData(array $types, string $extraDataName): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->where('sheet.type IN (:types)')
            ->andWhere('sheet.enable = true')
            ->andWhere('NOT EXISTS (SELECT extradata.id FROM Entity:Sheet\ExtraData extradata WHERE extradata.sheet = sheet AND extradata.name = :extraDataName)')
            ->setParameter('types', $types)
            ->setParameter('extraDataName', $extraDataName)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function hasSheetBeenDuplicatedByEvent(Sheet $sheet, Event $event): bool
    {
        return (
            $this->entityManager
                ->createQueryBuilder()
                ->select('count(sheet.id)')
                ->from(Sheet::class, 'sheet')
                ->where('sheet.duplicatedFrom = :sheet')
                ->andWhere('sheet.event = :event')
                ->setParameters([
                    'sheet' => $sheet,
                    'event' => $event,
                ])
                ->getQuery()
                ->getSingleScalarResult()
            ) > 0;
    }

    public function getSheetsEnabledByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, participant, user, type, typeTranslation, sheetsGroup')
            ->from(Sheet::class, 'sheet', 'sheet.id')
            ->join('sheet.type', 'type', 'WITH', 'type.event = :event AND sheet.enable = true')
            ->setParameter('event', $event)
            ->join('sheet.participants', 'participant')
            ->leftJoin('sheet.group', 'sheetsGroup')
            ->join('participant.user', 'user')
            ->join('type.translations', 'typeTranslation')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getNotLinkedSheets(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet', 'type')
            ->from(Sheet::class, 'sheet')
            ->innerJoin('sheet.type', 'type')
            ->where('sheet.event = :event')
            ->andWhere('sheet.linkedSheets IS NULL')
            ->andWhere('sheet.enable = true')
            ->orderBy('sheet.title')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function filterWithScheduledMeetings(array $sheets): array
    {
      $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from(Sheet::class, 'sheet')
            ->where('sheet IN (:sheets)')
            ->andWhere('EXISTS (SELECT 1
                                 FROM Entity:Meeting meeting
                                 WHERE (meeting.fromSheet = sheet or meeting.toSheet = sheet)
                                    AND meeting.state = :state)')
            ->setParameter('state', Meeting::STATE_SCHEDULED)
            ->setParameter('sheets', $sheets);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Event $event
     *
     * @return QueryBuilder
     */
    protected function getByEventQueryBuilder(Event $event): QueryBuilder
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet, participants')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.participants', 'participants')
            ->where('sheet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder;
    }
}
