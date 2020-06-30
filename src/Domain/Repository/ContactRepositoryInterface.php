<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

interface ContactRepositoryInterface
{
    public function add(Contact $contact): void;

    public function set(Contact $contact): void;

    public function find(Contact $contact): ?Contact;

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return User[]
     */
    public function findSeenUserByEventAndUser(Event $event, User $user): array;

    /**
     * @param Event  $event
     * @param User[] $users
     *
     * @return Contact[]
     */
    public function findByEventAndUsers(Event $event, array $users): array;

    /**
     * @param Event $event
     *
     * @return Contact[]
     */
    public function getByEvent(Event $event): array;

    public function hasEvaluateContactByEventAndUser(Event $event, User $user, User $contact): bool;

    public function getEvaluationContactByEventAndUser(Event $event, User $user, User $contact): ?int;
}
