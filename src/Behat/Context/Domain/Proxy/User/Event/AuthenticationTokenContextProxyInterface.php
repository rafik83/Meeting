<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\User\Event;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\User\Event\AuthenticationTokenManager;

interface AuthenticationTokenContextProxyInterface
{
    public function getStorage(): StorageInterface;

    public function getAuthenticationTokenManager(): AuthenticationTokenManager;
}
