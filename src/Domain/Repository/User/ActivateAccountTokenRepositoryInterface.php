<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\User;

use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Proximum\Vimeet\Domain\Model\User;

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
