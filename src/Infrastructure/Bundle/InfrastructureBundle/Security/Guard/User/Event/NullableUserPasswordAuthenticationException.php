<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Guard\User\Event;

use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class NullableUserPasswordAuthenticationException extends AuthenticationException
{
    /** @var ActivateAccountToken */
    private $activateAccountToken;

    public function __construct(ActivateAccountToken $activateAccountToken)
    {
        parent::__construct();

        $this->activateAccountToken = $activateAccountToken;
    }

    public function getActivateAccountToken(): ActivateAccountToken
    {
        return $this->activateAccountToken;
    }
}
