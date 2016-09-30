<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\User;

interface UserRepositoryInterface
{
    /**
     * @param $email
     *
     * @return bool
     */
    public function emailExists($email);

    /**
     * @param User $user
     */
    public function add(User $user);

    /**
     * @param User $user
     */
    public function set(User $user);

    /**
     * @param string $email
     *
     * @return User
     */
    public function findByEmail($email);

    /**
     * @return User[]
     */
    public function all();

    /**
     * @param int    $page
     * @param int    $limit
     * @param Event  $event
     * @param array  $filter
     * @param string $locale
     *
     * @return PaginatedResult
     */
    public function paginate($page, $limit, Event $event, array $filter, $locale);
}
