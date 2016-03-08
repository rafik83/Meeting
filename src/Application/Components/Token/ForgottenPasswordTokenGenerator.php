<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Token;

use Proximum\Vimeet\Domain\Model\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Model\User;

class ForgottenPasswordTokenGenerator extends AbstractTokenGenerator
{
    /**
     * @param User $user
     *
     * @return ForgottenPasswordToken
     */
    public function generate(User $user)
    {
        return new ForgottenPasswordToken($user, $this->generateToken($user), $this->expirateDate);
    }

    /**
     * @return \DateInterval
     */
    protected function getLifetime()
    {
        return new \DateInterval('P1D');
    }
}
