<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Event;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\User\Event\AuthenticationTokenImport;

class ConfirmAuthenticationTokenImport implements Command
{
    /** @var AuthenticationTokenImport[] */
    public $authenticationTokenImports;

    public function __construct(array $authenticationTokenImports)
    {
        $this->authenticationTokenImports = $authenticationTokenImports;
    }
}
