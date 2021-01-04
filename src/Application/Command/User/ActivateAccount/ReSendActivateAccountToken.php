<?php

namespace Proximum\Vimeet\Application\Command\User\ActivateAccount;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ReSendActivateAccountToken
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var User */
    public $fromUser;

    /**
     * @param Sheet $sheet
     * @param User  $user
     * @param User  $fromUser
     */
    public function __construct(Sheet $sheet, User $user, User $fromUser)
    {
        $this->sheet    = $sheet;
        $this->user     = $user;
        $this->fromUser = $fromUser;
    }
}
