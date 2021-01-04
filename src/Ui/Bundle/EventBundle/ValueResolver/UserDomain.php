<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver;

use Proximum\Vimeet\Domain\Model\User;

class UserDomain
{
    /** @var User */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
