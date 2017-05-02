<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;

interface GroupRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return null|Group
     */
    public function getById($id);

    /**
     * @param Event $event
     * @param User  $manager
     *
     * @return null|Group
     */
    public function getByEventAndManager(Event $event, User $manager);

    /**
     * @param Event $event
     *
     * @return Group[]
     */
    public function getAllByEventOrderedByTitle(Event $event);

    /**
     * @param Group $group
     */
    public function add(Group $group);

    /**
     * @param Event $event
     *
     * @return Group[]
     */
    public function getByEvent(Event $event);
}
