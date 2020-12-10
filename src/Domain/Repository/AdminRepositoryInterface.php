<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Type;

interface AdminRepositoryInterface
{
    /**
     * @param $email
     *
     * @return bool
     */
    public function emailExists($email);

    /**
     * @param Admin $admin
     */
    public function add(Admin $admin);

    public function remove(Admin $admin): void;

    /**
     * @param Admin $admin
     */
    public function set(Admin $admin);

    /**
     * @param int $id
     *
     * @return Admin|null
     */
    public function findById($id);

    /**
     * @param string $email
     *
     * @return Admin|null
     */
    public function findByEmail(string $email, bool $includeDeleted = false);

    /**
     * @param array $filters
     *
     * @return Admin[]
     */
    public function list(array $filters): array;

    /**
     * @param int   $page
     * @param int   $limit
     * @param array $filters
     *
     * @return PaginatedResult
     */
    public function listPaginated($page, $limit, array $filters);

    /**
     * @return Admin[]
     */
    public function all();

    /**
     * @param Event $event
     *
     * @return Admin[]
     */
    public function getFollowers(Event $event);

    /**
     * @param Admin $admin
     * @param array $filters
     *
     * @return Admin[]
     */
    public function getOperatorForOrganizer(Admin $admin, array $filters): array;

    /**
     * @param Event $event
     * @param Type  $type
     *
     * @return Admin[]
     */
    public function getAllowedPartner(Event $event, Type $type);

    /**
     * @param Event $event
     *
     * @return Admin[]
     */
    public function getAllowedOrganizer(Event $event);

    /**
     * @param $role
     *
     * @return null|Admin
     */
    public function findOneByRole($role);
}
