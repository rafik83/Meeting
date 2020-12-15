<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * "Compte admin/organisateur/collaborateur".
 */
class Admin extends AbstractUser implements AdvancedUserInterface
{
    public const ROLE_ORGANIZER = 'ROLE_ORGANIZER';
    public const ROLE_OPERATOR = 'ROLE_OPERATOR';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const ROLE_PARTNER = 'ROLE_PARTNER';
    public const ROLE_HOST = 'ROLE_HOST';

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
     * @var ArrayCollection
     */
    private $types;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @var DateTimeInterface
     */
    private $lastLoginAt;

    /** @var \DateTime */
    private $deletedAt;

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
    public function __construct(
        $email,
        $salt,
        $password,
        $locale,
        $firstname,
        $lastname,
        $role,
        DateTimeInterface $createdAt
    ) {
        parent::__construct($email, $salt, $password, $locale);

        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->role      = $role;
        $this->events    = new ArrayCollection();
        $this->types     = new ArrayCollection();
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
            self::ROLE_PARTNER,
            self::ROLE_HOST,
        ];
    }

    /**
     * @return ArrayCollection of Events
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @return Type[]
     */
    public function getAllowedTypes()
    {
        return $this->types->toArray();
    }

    /**
     * @return bool
     */
    public function hasAllowedTypes()
    {
        return count($this->types) > 0;
    }

    /**
     * @param Event $event
     *
     * @return Admin
     */
    public function addEvent(Event $event)
    {
        $this->events->add($event);

        return $this;
    }

    /**
     * @param $type
     *
     * @return Admin
     */
    public function addType($type)
    {
        $this->types->add($type);

        return $this;
    }

    /**
     * @param Event $event
     *
     * @return Admin
     */
    public function removeEvent(Event $event)
    {
        $this->events->removeElement($event);

        return $this;
    }

    /**
     * @param Type $type
     *
     * @return Admin
     */
    public function removeType(Type $type)
    {
        $this->types->removeElement($type);

        return $this;
    }

    /**
     * @param array $events
     *
     * @return Admin
     */
    public function setEvents(array $events)
    {
        $this->events = new ArrayCollection($events);

        return $this;
    }

    /**
     * @param Type[] $types
     *
     * @return Admin
     */
    public function setTypeEvents(array $types)
    {
        foreach ($this->events as $event) {
            $find = false;

            foreach ($types as $type) {
                if ($type->getEvent() === $event) {
                    $find = true;
                }

                if (!$this->hasEvent($type->getEvent())) {
                    $this->addEvent($type->getEvent());
                }
            }

            if (false === $find) {
                $this->removeEvent($event);
            }
        }

        return $this;
    }

    /**
     * Set type and associated event
     *
     * @param Type[] $types
     *
     * @return Admin
     */
    public function setTypes(array $types)
    {
        foreach ($this->types as $type) {
            if (!in_array($type, $types)) {
                $this->removeType($type);
            }
        }

        foreach ($types as $type) {
            if (!$this->hasType($type)) {
                $this->addType($type);
            }
        }

        return $this;
    }

    /**
     * @param string $email
     *
     * @return Admin
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $firstname
     *
     * @return Admin
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @param string $lastname
     *
     * @return Admin
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
     * @return string
     */
    public function getFullname()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeInterface|null
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
            if (self::ROLE_SUPER_ADMIN === $this->getRole()) {
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
     * @return bool
     */
    public function hasAccessToAllEvent()
    {
        return $this->isSuperAdmin() && !$this->hasEvents();
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
     * @param Type $type
     *
     * @return bool
     */
    public function hasType(Type $type)
    {
        return $this->types->contains($type);
    }

    /**
     * @return bool
     */
    public function isPartner()
    {
        return self::ROLE_PARTNER === $this->role;
    }

    /**
     * @return bool
     */
    public function isOrganizer()
    {
        return self::ROLE_ORGANIZER === $this->role;
    }

    /**
     * @return bool
     */
    public function isOperator()
    {
        return self::ROLE_OPERATOR === $this->role;
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        return self::ROLE_SUPER_ADMIN === $this->role;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt instanceof \DateTime;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function restore(): void
    {
        $this->deletedAt = null;
    }
}
