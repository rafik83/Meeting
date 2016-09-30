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

        if (!empty($filter['type'])) {
            $queryBuilder
                ->andWhere('sheet.type IS NOT NULL AND sheet.type = :type OR sheet.type IS NULL AND userEvent.type = :type')
                ->setParameter('type', $filter['type']->getId());
        }

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'user', 'id');
    }
}
