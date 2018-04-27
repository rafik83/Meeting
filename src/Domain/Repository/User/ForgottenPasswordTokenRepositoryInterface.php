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
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;

interface ForgottenPasswordTokenRepositoryInterface
{
    /**
     * @param ForgottenPasswordToken $forgottenPasswordToken
     */
    public function create(ForgottenPasswordToken $forgottenPasswordToken);

    /**
     * @param User $user
     */
    public function deleteAllForUser(User $user);
}
