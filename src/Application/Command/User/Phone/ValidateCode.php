<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
