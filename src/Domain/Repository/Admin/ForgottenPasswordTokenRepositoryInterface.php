<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Admin;

use Proximum\Vimeet\Domain\Model\Admin\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Model\Admin;

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
