<?php

namespace Proximum\Vimeet\Application\Command\User\Phone;

use Proximum\Vimeet\Domain\Model\User\UserEventPhone;

class ValidateCode
{
    /** @var UserEventPhone */
    public $userEventPhone;

    /** @var string */
    public $code;

    /**
     * @param UserEventPhone $userEventPhone
     */
    public function __construct(UserEventPhone $userEventPhone)
    {
        $this->userEventPhone = $userEventPhone;
    }
}
