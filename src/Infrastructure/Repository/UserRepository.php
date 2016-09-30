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
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

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
            ->from(UserEvent::class, 'user_event', 'user_event.id')
            ->where('user_event.event = :event')
            ->setParameter('event', $event)
            ->join('user_event.user', 'user')
            ->leftJoin('user_event.type', 'type')
            ->leftJoin('type.translations', 'typeTranslations', 'WITH', 'typeTranslations.locale = :locale')
            ->leftJoin(Participant::class, 'participant', 'WITH', 'participant.user = user')
            ->leftJoin('participant.sheet', 'sheet')
            ->leftJoin('sheet.type', 'sheetType')
            ->leftJoin('sheetType.translations', 'sheetTypeTranslations', 'WITH', 'sheetTypeTranslations.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('user.account.lastName', 'ASC')
            ->addOrderBy('user.email', 'ASC');

        if (!empty($filter['type'])) {
            $queryBuilder
                ->andWhere('sheet.type IS NOT NULL AND sheet.type = :type OR sheet.type IS NULL AND user_event.type = :type')
                ->setParameter('type', $filter['type']->getId());
        }

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'user_event', 'id');
    }
}
