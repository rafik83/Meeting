<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
