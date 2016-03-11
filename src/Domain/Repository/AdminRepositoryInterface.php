<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\PaginatedResult;

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

    /**
     * @param Admin $admin
     */
    public function set(Admin $admin);

    /**
     * @param string $email
     *
     * @return Admin
     */
    public function findByEmail($email);

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
}
