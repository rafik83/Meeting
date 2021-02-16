<?php

namespace Proximum\Vimeet\Application\Command\Register;

use Proximum\Vimeet\Domain\Model\User;

class RegisterNewUserResult
{
    /**
     * @var User
     */
    public $user;

    /**
     * RegisterNewUserResult constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
