<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\UserContextProxyInterface;

class UserContext implements Context
{
    /** @var UserContextProxyInterface */
    private $userContextProxy;

    /**
     * @param UserContextProxyInterface $userContextProxy
     */
    public function __construct(UserContextProxyInterface $userContextProxy)
    {
        $this->userContextProxy = $userContextProxy;
    }

    /**
     * @Given /^the user "(?P<email>[^"]+)" is created$/
     *
     * @param string $email
     */
    public function create($email)
    {
        $user = $this->userContextProxy->getUserManager()->create($email);

        $this->userContextProxy->getStorage()->set('user', $user);
    }

    /**
     * @Given /^the user "(?P<email>[^"]+)" with empty password is created$/
     *
     * @param string $email
     */
    public function createWithEmptyPassword(string $email): void
    {
        $user = $this->userContextProxy->getUserManager()->createWithEmptyPassword($email);

        $this->userContextProxy->getStorage()->set('user', $user);
    }
}
