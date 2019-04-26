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
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
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
}
