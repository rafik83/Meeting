<?php

namespace Proximum\Vimeet\Application\Command\Token\UserEventToken;

use Proximum\Vimeet\Domain\Model\Token\UserEventToken;

class ConfirmAgenda
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
