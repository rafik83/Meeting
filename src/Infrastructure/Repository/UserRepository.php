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
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserSheetTypeView;
use Proximum\Vimeet\Application\Query\User\Event\Contact\UserSheetsView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\AuthenticationToken;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\FilterType;

class UserRepository implements UserRepositoryInterface
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
    public function add(User $user)
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }

    /**
     * {@inheritdoc}
     */
    public function emailExists($email)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user.id')
            ->from(User::class, 'user')
            ->where('user.email = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult() ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function set(User $user)
    {
        $this->entityManager->flush($user);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail($email): ?User
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.email = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findOneById(int $id): ?User
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findByIds(array $ids): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->where('user.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($page, $limit, Event $event, array $filter, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->from(User::class, 'user', 'user.id')
            ->join(UserEvent::class, 'userEvent', 'WITH', 'userEvent.user = user AND userEvent.event = :event')
            ->join('userEvent.type', 'type')
            ->join('type.translations', 'typeTranslations', 'WITH', 'typeTranslations.locale = :locale')
            ->addOrderBy('user.account.lastName', 'ASC')
            ->addOrderBy('user.email', 'ASC')
            ->setParameter('event', $event)
            ->setParameter('locale', $locale);

        $this->filterQueryBuilder($queryBuilder, $filter);

        if (!empty($filter['participation'])) {
            switch ($filter['participation']) {
                case FilterType::FILTER_WITH_SHEET:
                    $queryBuilder->select('user.id, user.email, user.account.lastName as lastname, user.account.firstName as firstname, typeTranslations.title as typeTitle, sheet.id as sheetId, sheetType.id as sheetTypeId, sheetTypeTranslations.title as sheetTypeTitle');
                    $this->userWithSheetQueryBuilder($queryBuilder);
                    break;
                case FilterType::FILTER_WITHOUT_SHEET:
                    $queryBuilder->select('user.id, user.email, user.account.lastName as lastname, user.account.firstName as firstname, typeTranslations.title as typeTitle');
                    $this->userWithoutSheetQueryBuilder($queryBuilder);
                    break;
            }
        } else {
            $queryBuilder
                ->select('user.id, user.email, user.account.lastName as lastname, user.account.firstName as firstname, typeTranslations.title as typeTitle, sheet.id as sheetId, sheetType.id as sheetTypeId, sheetTypeTranslations.title as sheetTypeTitle')
                ->leftJoin(Participant::class, 'participant', 'WITH', 'participant.user = user')
                ->leftJoin('participant.sheet', 'sheet', 'WITH', 'sheet.event = :event')
                ->leftJoin('sheet.type', 'sheetType')
                ->leftJoin('sheetType.translations', 'sheetTypeTranslations', 'WITH', 'sheetTypeTranslations.locale = :locale');
        }

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function getByIdsIndexedById(array $ids)
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user', 'user.id')
            ->where('user.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findWithEnabledSheetByEvent(Event $event): array
    {
        return $this->getWithSheetByEvent($event, true);
    }

    /**
     * {@inheritdoc}
     */
    public function findWithSheetByEvent(Event $event): array
    {
        return $this->getWithSheetByEvent($event, false);
    }

    /**
     * @param Event $event
     * @param bool  $onlyEnabledSheets
     *
     * @return User[]
     */
    private function getWithSheetByEvent(Event $event, bool $onlyEnabledSheets): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join(Participant::class, 'participant', 'WITH', 'participant.user = user')
            ->join(
                'participant.sheet',
                'sheet',
                'WITH',
                'sheet.event = :event' . ($onlyEnabledSheets ? ' AND sheet.enable = true' : '')
            )
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByEventAndEmail(Event $event, string $email): ?User
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join(Participant::class, 'participant', 'WITH', 'participant.user = user AND user.email = :email')
            ->join(
                'participant.sheet',
                'sheet',
                'WITH',
                'sheet.event = :event'
            )
            ->setParameter('event', $event)
            ->setParameter('email', $email)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findOwnersWithEnabledSheetByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join(Sheet::class, 'sheet', 'WITH', 'sheet.owner = user AND sheet.enable = true AND sheet.event = :event')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function getWithSheetAndTypeByEvent(Event $event, string $locale, array $types, array $states): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select(sprintf('new %s(
                user.id,
                sheet.id,
                user.account.firstName,
                user.account.lastName,
                sheet.title,
                spot.reference,
                typeTranslation.title,
                presenceDate.arrival,
                presenceDate.departure,
                COALESCE(presenceDate.hasArrivalHours, false),
                COALESCE(presenceDate.hasDepartureHours, false),
                sheet.state
            )', UserSheetTypeView::class))
            ->from(User::class, 'user')
            ->join(Participant::class, 'participant', 'WITH', 'participant.user = user')
            ->join(
                'participant.sheet',
                'sheet',
                'WITH',
                'sheet.event = :event AND sheet.enable = true'
            )
            ->join('sheet.type', 'type')
            ->leftJoin('sheet.spot', 'spot')
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->leftJoin(
                User\Event\PresenceDate::class,
                'presenceDate',
                'WITH',
                'user.id = presenceDate.user AND presenceDate.event = :event'
            )
            ->setParameter('event', $event)
            ->setParameter('locale', $locale)
            ->orderBy('sheet.title, user.account.lastName, user.account.firstName', 'ASC')
        ;

        if (count($types)) {
            $queryBuilder
                ->andWhere('sheet.type IN (:types)')
                ->setParameter('types', $types);
        }

        if (!empty($states)) {
            $queryBuilder
                ->andWhere('sheet.state IN (:states)')
                ->setParameter('states', $states);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        return $this->findWithEnabledSheetByEvent($event);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndInCatalog(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join(Participant::class, 'participant', 'WITH', 'participant.user = user')
            ->join(
                'participant.sheet',
                'sheet',
                'WITH',
                'sheet.event = :event AND sheet.enable = true AND sheet.inCatalog = true'
            )
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    private function userWithoutSheetQueryBuilder(QueryBuilder $queryBuilder)
    {
        $queryBuilder->andWhere($queryBuilder->expr()->not(
            $queryBuilder->expr()
                ->exists(sprintf('SELECT p.id FROM %s p WHERE p.user = user', Participant::class))
        ));
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    private function userWithSheetQueryBuilder(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->join(Participant::class, 'participant', 'WITH', 'participant.user = user')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.event = :event')
            ->join('sheet.type', 'sheetType')
            ->join('sheetType.translations', 'sheetTypeTranslations', 'WITH', 'sheetTypeTranslations.locale = :locale');
    }

    /**
     * Filter paginated user list query by types, name or email
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $filter
     */
    private function filterQueryBuilder(QueryBuilder $queryBuilder, array $filter)
    {
        if (!empty($filter['text'])) {
            $queryBuilder
                ->andWhere('user.account.lastName LIKE :filter_text OR user.account.firstName LIKE :filter_text')
                ->orWhere('user.email LIKE :filter_text')
                ->setParameter('filter_text', '%' . $filter['text'] . '%');
        }

        if (!empty($filter['types'])) {
            if (empty($filter['participation']) || FilterType::FILTER_WITH_SHEET === $filter['participation']) {
                $queryBuilder
                    ->andWhere('sheet.type IN (:types) OR sheet.type IS NULL AND userEvent.type IN (:types)')
                    ->setParameter('types', $filter['types']);
            } elseif (FilterType::FILTER_WITHOUT_SHEET === $filter['participation']) {
                $queryBuilder
                    ->andWhere('userEvent.type IN (:types)')
                    ->setParameter('types', $filter['types']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersParticipantOfSheets(array $sheets)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user', 'user.id')
            ->join(Participant::class, 'participant', 'WITH', 'participant.user = user AND participant.sheet IN (:sheets)')
            ->addOrderBy('user.account.lastName', 'ASC')
            ->setParameter('sheets', $sheets)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventWithoutDispatch(Event $event, Mass $mass)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join(Participant::class, 'participant', 'WITH', 'user = participant.user')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.event = :event')
            ->andWhere(
                'NOT EXISTS (
                    SELECT m.id FROM ' . MassAssignment::class . ' m
                    WHERE m.mass = :mass AND m.user = user
                )'
            )
            ->setParameter('mass', $mass)
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventWithDispatch(Event $event, Mass $mass): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join(Participant::class, 'participant', 'WITH', 'user = participant.user')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.event = :event')
            ->andWhere(
                'EXISTS (
                    SELECT m.id FROM ' . MassAssignment::class . ' m
                    WHERE m.mass = :mass AND m.user = user
                )'
            )
            ->setParameter('mass', $mass)
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByAuthenticationTokenAndEvent(string $token, Event $event, \DateTimeInterface $expiredAt): ?User
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join(
                AuthenticationToken::class,
                'authenticationToken',
                'WITH',
                'authenticationToken.token = :token
                AND authenticationToken.user = user
                AND authenticationToken.event = :event
                AND (authenticationToken.expiredAt IS NULL OR authenticationToken.expiredAt > :expiredAt)'
            )
            ->setParameters([
                'token' => $token,
                'event' => $event,
                'expiredAt' => $expiredAt,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipantProduct(Product $product): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join(Participant::class, 'participant', 'WITH', 'user = participant.user AND participant.participantProduct = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getResult();
    }

    public function getUserSheetsViewsByEvent(Event $event): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf(
                    'new %s(user.id, user.account.firstName, user.account.lastName, sheet.id, sheet.title, type.id)',
                    UserSheetsView::class
                )
            )
            ->from(User::class, 'user')
            ->join(Participant::class, 'participant', 'WITH', 'user = participant.user')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.event = :event')
            ->join('sheet.type', 'type')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult();
    }
}
