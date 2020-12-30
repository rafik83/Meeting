<?php

namespace Proximum\Vimeet\Application\Components\Token;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken as UserForgottenPasswordToken;

class UserForgottenPasswordTokenGenerator extends AbstractTokenGenerator
{
    /**
     * @param User $user
     *
     * @return UserForgottenPasswordToken
     */
    public function generate(User $user)
    {
        return new UserForgottenPasswordToken(
            $user,
            $this->generateToken($user),
            $this->expirateDate
        );
    }

    /**
     * @return \DateInterval
     */
    protected function getLifetime()
    {
        return new \DateInterval('P1D');
    }
}
