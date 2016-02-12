<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Token;

use Proximum\Vimeet\Domain\Model\ActivateAccountToken;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ActivateAccountTokenGenerator extends AbstractTokenGenerator
{
    /**
     * @param User  $user
     * @param Sheet $sheet
     *
     * @return ActivateAccountToken
     */
    public function generate(User $user, Sheet $sheet)
    {
        return new ActivateAccountToken(
            $user,
            $this->generateToken($user),
            $sheet,
            $this->expirateDate
        );
    }
}
