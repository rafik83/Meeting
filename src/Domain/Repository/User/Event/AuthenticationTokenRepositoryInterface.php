<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\User\Event;

use Proximum\Vimeet\Domain\Model\User\Event\AuthenticationToken;

interface AuthenticationTokenRepositoryInterface
{
    public function add(AuthenticationToken $authenticationToken): void;
}
