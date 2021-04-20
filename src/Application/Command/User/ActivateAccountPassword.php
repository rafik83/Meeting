<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ActivateAccountPassword implements Command
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $password;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param User  $user
     * @param Sheet $sheet
     */
    public function __construct(User $user, Sheet $sheet)
    {
        $this->user  = $user;
        $this->sheet = $sheet;
    }
}
