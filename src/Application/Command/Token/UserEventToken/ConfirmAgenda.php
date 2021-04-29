<?php

namespace Proximum\Vimeet\Application\Command\Token\UserEventToken;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;

class ConfirmAgenda implements Command
{
    /** @var UserEventToken */
    public $userEventToken;

    /**
     * @param UserEventToken $userEventToken
     */
    public function __construct(UserEventToken $userEventToken)
    {
        $this->userEventToken = $userEventToken;
    }
}
