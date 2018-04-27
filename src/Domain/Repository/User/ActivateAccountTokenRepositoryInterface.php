<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\User;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;

interface ActivateAccountTokenRepositoryInterface
{
    /**
     * @param ActivateAccountToken $activateAccountToken
     */
    public function create(ActivateAccountToken $activateAccountToken);

    /**
     * @param User $user
     */
    public function deleteAllForUser(User $user);
}
