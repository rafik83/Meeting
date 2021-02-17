<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\AbstractUser;

interface AuthenticationManagerInterface
{
    /**
     * Authenticate the user
     */
    public function authenticate(AbstractUser $user, string $providerKey);

    /**
     * Disconnect the user
     */
    public function disconnect();


}
