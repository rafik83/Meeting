<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain\User\Event;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\User\Event\AuthenticationTokenContextProxyInterface;

class AuthenticationTokenContext implements Context
{
    /** @var AuthenticationTokenContextProxyInterface */
    private $authenticationTokenContextProxy;

    public function __construct(AuthenticationTokenContextProxyInterface $authenticationTokenContextProxy)
    {
        $this->authenticationTokenContextProxy = $authenticationTokenContextProxy;
    }

    /**
     * @Given /^there is an authentication token "(?P<token>[^"]+)" for this user on this event$/
     */
    public function createAuthenticationTokenForGivenUserAndEvent(string $token): void
    {
        $event = $this->authenticationTokenContextProxy->getStorage()->get('event');
        $user = $this->authenticationTokenContextProxy->getStorage()->get('user');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        $authenticationToken = $this->authenticationTokenContextProxy
            ->getAuthenticationTokenManager()
            ->create($user, $event, $token);

        $this->authenticationTokenContextProxy
            ->getStorage()
            ->set('authenticationToken', $authenticationToken);
    }
}
