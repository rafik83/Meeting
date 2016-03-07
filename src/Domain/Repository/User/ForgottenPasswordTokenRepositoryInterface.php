<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Model\User;

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
