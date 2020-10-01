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
use Proximum\Vimeet\Domain\Model\Event;
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
     *
     * @param string $email
     */
    public function create($email)
    {
        $user = $this->userContextProxy->getUserManager()->find($email);

        if (!$user) {
            $user = $this->userContextProxy->getUserManager()->create($email);
        }

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

    /**
     * @Given this user is called :firstname :lastname
     */
    public function thisUserIsCalled($firstname, $lastname)
    {
        /** @var User */
        $user = $this->userContextProxy->getStorage()->get('user');

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        $account = $user->getAccount();
        $account->setFirstName($firstname);
        $account->setLastName($lastname);
        $user->setAccount($account);
        $this->userContextProxy->getUserManager()->set($user);
    }

    /**
     * @Given /^this user is a (?P<gender>(wo)?man)$/
     */
    public function thisUserIsAWoman($gender)
    {
        /** @var User */
        $user = $this->userContextProxy->getStorage()->get('user');

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        $account = $user->getAccount();
        $account->setGender($gender);
        $this->userContextProxy->getUserManager()->set($user);
    }

    /**
     * @Given this user position is :position
     */
    public function thisUserPositionIs($position)
    {
        /** @var User */
        $user = $this->userContextProxy->getStorage()->get('user');

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        $account = $user->getAccount();
        $account->setPosition($position);
        $this->userContextProxy->getUserManager()->set($user);
    }

    /**
     * @Given this user is declared in this event
     */
    public function thisUserIsDeclaredInThisEvent()
    {
        $storage = $this->userContextProxy->getStorage();
        /** @var Event */
        $event = $storage->get('event');
        /** @var User */
        $user = $storage->get('user');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }
        $this->userContextProxy->getUserManager()->addUserEvent($user, $event, $storage->get('type'));
    }
}
