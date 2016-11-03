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
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
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
    public function findByEmail($email)
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
            ->select('NEW Proximum\Vimeet\Domain\View\User\UserListView(user.id, user.email, user.account.lastName, user.account.firstName, typeTranslations.title, sheet.id, sheetType.id, sheetTypeTranslations.title)')
            ->from(User::class, 'user', 'user.id')
            ->join(UserEvent::class, 'userEvent', 'WITH', 'userEvent.user = user AND userEvent.event = :event')
            ->join('userEvent.type', 'type')
            ->join('type.translations', 'typeTranslations', 'WITH', 'typeTranslations.locale = :locale')
            ->leftJoin(Participant::class, 'participant', 'WITH', 'participant.user = user')
            ->leftJoin('participant.sheet', 'sheet', 'WITH', 'sheet.event = :event')
            ->leftJoin('sheet.type', 'sheetType')
            ->leftJoin('sheetType.translations', 'sheetTypeTranslations', 'WITH', 'sheetTypeTranslations.locale = :locale')
            ->addOrderBy('user.account.lastName', 'ASC')
            ->addOrderBy('user.email', 'ASC')
            ->setParameter('event', $event)
            ->setParameter('locale', $locale);

        $this->filterQueryBuilder($queryBuilder, $filter);

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'user', 'id');
    }

    /**
     * Filter paginated user list query by types, name or email
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $filter
     */
    private function filterQueryBuilder(QueryBuilder &$queryBuilder, array $filter)
    {
        if (!empty($filter['types'])) {
            $queryBuilder
                ->andWhere('sheet.type IS NOT NULL AND sheet.type IN (:types) OR sheet.type IS NULL AND userEvent.type IN (:types)')
                ->setParameter('types', $filter['types']);
        }

        if (!empty($filter['text'])) {
            switch ($filter['predefined']) {
                case FilterType::FILTER_NAME:
                    $queryBuilder->andWhere('LOWER(user.account.lastName) LIKE LOWER(:filter_text) OR LOWER(user.account.firstName) LIKE LOWER(:filter_text)');
                    break;
                case FilterType::FILTER_EMAIL:
                    $queryBuilder->andWhere('LOWER(user.email) LIKE LOWER(:filter_text)');
                    break;
                default:
                    $queryBuilder
                        ->andWhere('LOWER(user.account.lastName) LIKE LOWER(:filter_text) OR LOWER(user.account.firstName) LIKE LOWER(:filter_text)')
                        ->orWhere('LOWER(user.email) LIKE LOWER(:filter_text)');
                    break;
            }

            $queryBuilder->setParameter('filter_text', '%' . $filter['text'] . '%');
        }
    }
}
