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
use Proximum\Vimeet\Domain\Model\Admin\ForgottenPasswordToken;

interface ForgottenPasswordTokenRepositoryInterface
{
    /**
     * @param ForgottenPasswordToken $forgottenPasswordToken
     */
    public function create(ForgottenPasswordToken $forgottenPasswordToken);

    /**
     * @param Admin $admin
     */
    public function deleteAllForUser(Admin $admin);
}
