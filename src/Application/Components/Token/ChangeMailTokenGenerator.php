<?php

namespace Proximum\Vimeet\Application\Components\Token;

use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\User;

class ChangeMailTokenGenerator extends AbstractTokenGenerator
{
    /**
     * @param User   $user
     * @param string $mail
     *
     * @return ChangeMailToken
     */
    public function generate(User $user, $mail)
    {
        return new ChangeMailToken($user, $mail, $this->generateToken($user), $this->expirateDate);
    }
}
