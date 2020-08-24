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
use Proximum\Vimeet\Domain\Model\User;

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
     */
    public function create(string $email): User
    {
        $user = $this->userContextProxy->getUserManager()->create($email);

        $this->userContextProxy->getStorage()->set('user', $user);

        return $user;
    }

    /**
     * @Given /^the user is created with email "(?P<email>[^"]+)", firstname "(?P<firstname>[^"]+)" and lastname "(?P<lastname>[^"]+)"$/
     */
    public function createWithEmailFirstnameAndLastname(string $email, string $firstname, string $lastname): User
    {
        $user = $this->create($email);
        $this->userContextProxy->getUserManager()->fillInformation($user, $firstname, $lastname);

        return $user;
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
