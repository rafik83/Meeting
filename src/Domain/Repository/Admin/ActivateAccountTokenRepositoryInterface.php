<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Admin;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;

interface ActivateAccountTokenRepositoryInterface
{
    /**
     * @param ActivateAccountToken $activateAccountToken
     */
    public function create(ActivateAccountToken $activateAccountToken);

    /**
     * @param Admin $admin
     */
    public function deleteAllForUser(Admin $admin);
}
