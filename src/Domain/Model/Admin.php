<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * "Compte admin/organisateur/collaborateur".
 */
class Admin extends AbstractUser implements AdvancedUserInterface
{
    const ROLE_ORGANIZER   = 'ROLE_ORGANIZER';
    const ROLE_OPERATOR    = 'ROLE_OPERATOR';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $role;

    /**
     * @var Event[]
     */
    private $events;

    /**
     * @param string $email
     * @param string $salt
     * @param string $password
     * @param string $locale
     * @param string $firstname
     * @param string $lastname
     * @param string $role
     */
    public function __construct($email, $salt, $password, $locale, $firstname, $lastname, $role)
    {
        parent::__construct($email, $salt, $password, $locale);

        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->role      = $role;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return [$this->getRole()];
    }

    /**
     * @return array
     */
    public static function getAllRoles()
    {
        return [
            self::ROLE_OPERATOR,
            self::ROLE_ORGANIZER,
            self::ROLE_SUPER_ADMIN,
        ];
    }

    /**
     * @return Event[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param Event $event
     * @return self
     */
    public function addEvent(Event $event)
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }
}
