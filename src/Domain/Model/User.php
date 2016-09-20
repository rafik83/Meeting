<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\User\Account;

/**
 * "Compte utilisateur".
 */
class User extends AbstractUser
{
    /**
     * @var Account
     */
    private $account;

    /**
     * @var ArrayCollection
     */
    private $events;

    /**
     * @var ArrayCollection
     */
    private $types;

    /**
     * User constructor.
     *
     * @param string $email
     * @param string $salt
     * @param string $password
     * @param string $locale
     */
    public function __construct($email, $salt, $password, $locale)
    {
        parent::__construct($email, $salt, $password, $locale);

        $this->events = new ArrayCollection();
        $this->types  = new ArrayCollection();
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Account $account
     *
     * @return User
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Event
     */
    public function getEvents()
    {
        return $this->events->toArray();
    }

    /**
     * @param Event $event
     *
     * @return User
     */
    public function addEvent($event)
    {
        $this->events->add($event);

        return $this;
    }

    /**
     * @return Type
     */
    public function getTypes()
    {
        return $this->types->toArray();
    }

    /**
     * @param Type $type
     *
     * @return User
     */
    public function addType($type)
    {
        $this->types->add($type);

        return $this;
    }
}
