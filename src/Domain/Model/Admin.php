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
use DateTimeInterface;
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
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @var DateTimeInterface
     */
    private $lastLoginAt;

    /**
     * @param string            $email
     * @param string            $salt
     * @param string            $password
     * @param string            $locale
     * @param string            $firstname
     * @param string            $lastname
     * @param string            $role
     * @param DateTimeInterface $createdAt
     */
    public function __construct($email, $salt, $password, $locale, $firstname, $lastname, $role, DateTimeInterface $createdAt)
    {
        parent::__construct($email, $salt, $password, $locale, null, null);

        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->role      = $role;
        $this->events    = new ArrayCollection();
        $this->createdAt = $createdAt;
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
     *
     * @return self
     */
    public function addEvent(Event $event)
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * @param Event $event
     *
     * @return self
     */
    public function removeEvent(Event $event)
    {
        $this->events->removeElement($event);

        return $this;
    }

    /**
     * @param array $events
     *
     * @return self
     */
    public function setEvents(array $events)
    {
        foreach ($this->events as $event) {
            if (!in_array($event, $events)) {
                $this->removeEvent($event);
            }
        }

        foreach ($events as $event) {
            if (!$this->hasEvent($event)) {
                $this->addEvent($event);
            }
        }

        return $this;
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $firstname
     *
     * @return self
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @param string $lastname
     *
     * @return self
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

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
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeInterface
     */
    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    /**
     * @param DateTimeInterface $lastLoginAt
     */
    public function setLastLoginAt($lastLoginAt)
    {
        $this->lastLoginAt = $lastLoginAt;
    }

    /**
     * @param string $role
     *
     * @return self
     */
    public function setRole($role)
    {
        if (in_array($role, $this->getAllRoles())) {
            $this->role = $role;
        }

        return $this;
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
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }
}
