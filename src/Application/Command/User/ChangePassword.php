<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\User;

class ChangePassword implements Command
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $currentPassword;

    /**
     * @var string
     */
    public $plainPassword;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
