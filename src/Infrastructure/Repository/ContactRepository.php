<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;

class ContactRepository implements ContactRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Contact $contact): void
    {
        $this->entityManager->persist($contact);
        $this->entityManager->flush($contact);
    }

    public function set(Contact $contact): void
    {
        $this->entityManager->flush($contact);
    }

    public function find(Contact $contact): ?Contact
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('contact')
            ->from(Contact::class, 'contact')
            ->andWhere('contact.event = :event')
            ->andWhere('contact.user = :user')
            ->andWhere('contact.contact = :contact')
            ->setParameters(
                [
                    'event' => $contact->getEvent(),
                    'user' => $contact->getUser(),
                    'contact' => $contact->getContact(),
                ]
            )
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findSeenUserByEventAndUser(Event $event, User $user): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user')
            ->from(User::class, 'user')
            ->join(
                Contact::class,
                'contact',
                'WITH',
                'contact.contact = user AND contact.event = :event AND contact.user = :user'
            )
            ->setParameters(
                [
                    'event' => $event,
                    'user' => $user,
                ]
            )
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByEventAndUsers(Event $event, array $users): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('contact, user')
            ->from(Contact::class, 'contact')
            ->join('contact.contact', 'user')
            ->andWhere('contact.event = :event')
            ->andWhere('contact.user IN (:users)')
            ->setParameters(
                [
                    'event' => $event,
                    'users' => $users,
                ]
            )
            ->getQuery()
            ->getResult()
        ;
    }

    public function getByEvent(Event $event): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('contact')
            ->from(Contact::class, 'contact')
            ->andWhere('contact.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }

    public function hasEvaluateContactByEventAndUser(Event $event, User $user, User $contact): bool
    {
        return null !== $this->getEvaluationContactByEventAndUser($event, $user, $contact);
    }

    public function getEvaluationContactByEventAndUser(Event $event, User $user, User $contact): ?int
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('contact.evaluation')
            ->from(Contact::class, 'contact')
            ->andWhere('contact.event = :event')
            ->andWhere('contact.user = :user')
            ->andWhere('contact.contact = :contact')
            ->andWhere('contact.evaluation IS NOT NULL')
            ->setParameter('event', $event)
            ->setParameter('user', $user)
            ->setParameter('contact', $contact)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }
}
