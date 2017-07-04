<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Token;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;

interface UserEventTokenRepositoryInterface
{
    /**
     * @param UserEventToken $userEventToken
     */
    public function add(UserEventToken $userEventToken);

    /**
     * @param UserEventToken $userEventToken
     */
    public function set(UserEventToken $userEventToken);

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $type
     *
     * @return UserEventToken|null
     */
    public function findByEventAndUserAndType(Event $event, User $user, $type);

    /**
     * @param User $user
     *
     * @return bool|null
     */
    public function getConfirmationStatusByUserAndType(User $user);
}
