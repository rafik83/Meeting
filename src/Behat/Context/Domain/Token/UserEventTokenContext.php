<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain\Token;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Token\UserEventTokenContextProxyInterface;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class UserEventTokenContext implements Context
{
    /** @var UserEventTokenContextProxyInterface */
    private $userEventTokenContextProxy;

    /**
     * @param UserEventTokenContextProxyInterface $userEventTokenContextProxy
     */
    public function __construct(UserEventTokenContextProxyInterface $userEventTokenContextProxy)
    {
        $this->userEventTokenContextProxy = $userEventTokenContextProxy;
    }

    /**
     * @Given /^there is a token of type "(?P<type>[^"]+)" for this user on this event$/
     *
     * @param string $type
     */
    public function createUserEventToken($type)
    {
        $event = $this->userEventTokenContextProxy->getStorage()->get('event');
        $user  = $this->userEventTokenContextProxy->getStorage()->get('user');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        if (!in_array($type, UserEventTokenType::getUserEventTokenType())) {
            throw new \InvalidArgumentException('The given type is not a valid UserEventToken type');
        }

        $event = $this->userEventTokenContextProxy
            ->getUserEventTokenManager()
            ->create(
                $event,
                $user,
                $type
            );
        $this->userEventTokenContextProxy->getStorage()->set('event', $event);
    }
}
