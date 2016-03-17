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
use Doctrine\Common\Collections\ArrayCollection;

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
     * @var ArrayCollection
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
        $this->events    = new ArrayCollection();
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
     * @return string
     */
    public function serialize()
    {
        return serialize(
            [
                $this->id,
                $this->email,
                $this->firstname,
                $this->lastname,
                $this->password,
                $this->salt,
            ]
        );
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->email,
            $this->firstname,
            $this->lastname,
            $this->password,
            $this->salt
        ) = unserialize($serialized);
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
        if (!$this->hasEvents()) {
            if ($this->getRole() === self::ROLE_SUPER_ADMIN) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function hasEvents()
    {
        return !$this->events->isEmpty();
    }

    /**
     * @param EventInterface $event
     *
     * @return bool
     */
    public function hasEvent(EventInterface $event)
    {
        return $this->events->exists(function ($index, Event $eventLinked) use ($event) {
            return $eventLinked->getId() === $event->getId();
        });
    }

    /**
     * @return bool
     */
    public function isOrganizer()
    {
        return $this->role === self::ROLE_ORGANIZER;
    }

    /**
     * @return bool
     */
    public function isOperator()
    {
        return $this->role === self::ROLE_OPERATOR;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }
}
