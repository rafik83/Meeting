<?php

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\AbstractUser;
use Proximum\Vimeet\Domain\Model\User;

class UserImpersonateViewQuery implements Query
{
    /**
     * @var AbstractUser
     */
    public $previousUser;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $exitRouteName;

    /**
     * @var array
     */
    public $exitRouteParameters;

    /**
     * UserImpersonateViewQuery constructor.
     *
     * @param AbstractUser $previousUser
     * @param User         $user
     * @param string       $exitRouteName
     * @param array        $exitRouteParameters
     */
    public function __construct(
        AbstractUser $previousUser,
        User $user,
        $exitRouteName,
        array $exitRouteParameters = []
    ) {
        $this->previousUser        = $previousUser;
        $this->user                = $user;
        $this->exitRouteName       = $exitRouteName;
        $this->exitRouteParameters = $exitRouteParameters;
    }
}
