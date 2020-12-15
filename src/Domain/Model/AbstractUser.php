<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Time\DaysHelper;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractUser implements UserInterface, EquatableInterface, \Serializable
{
    private const FAILED_AUTHENTICATION_MAX = 5;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $salt;

    /**
     * @var string
     */
    protected $locale;

    /** @var int */
    protected $failedAuthentication = 0;

    /** @var null|\DateTimeInterface */
    protected $lastFailedAuthentication;

    /**
     * @param string $email
     * @param string $salt
     * @param string $password
     * @param string $locale
     */
    public function __construct($email, $salt, $password, $locale)
    {
        $this->email    = strtolower($email);
        $this->salt     = $salt;
        $this->password = $password;
        $this->locale   = $locale;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return !empty($this->password);
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
            $this->salt,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->email,
            $this->password,
            $this->salt) = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(UserInterface $user)
    {
        return $this->getUsername() === $user->getUsername();
    }

    /**
     * @param string $salt
     * @param string $password
     *
     * @return AbstractUser
     */
    public function updatePassword($salt, $password)
    {
        $this->salt = $salt;
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $email
     *
     * @return AbstractUser
     */
    public function updateEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function updateLastFailedAuthentication(\DateTimeInterface $now): void
    {
        if ($this->isLastFailedAuthenticationExpired($now)) {
            $this->failedAuthentication = 0;
        }

        $this->lastFailedAuthentication = $now;
        ++$this->failedAuthentication;
    }

    public function isLastFailedAuthenticationExpired(\DateTimeInterface $now): bool
    {
        if (null === $this->lastFailedAuthentication) {
            return true;
        }

        $lastFailedAuthenticationPlus15Minutes = DaysHelper::cloneDateTime($this->lastFailedAuthentication);
        $lastFailedAuthenticationPlus15Minutes->add(new \DateInterval('PT15M'));

        return $lastFailedAuthenticationPlus15Minutes < $now;
    }

    public function isTemporarilyDisabledDueToFailedAuthentication(\DateTimeInterface $now): bool
    {
        if (self::FAILED_AUTHENTICATION_MAX > $this->failedAuthentication) {
            return false;
        }

        return !$this->isLastFailedAuthenticationExpired($now);
    }

    public function getRemainingAuthenticationAttempt(\DateTimeInterface $now): int
    {
        return self::FAILED_AUTHENTICATION_MAX - $this->failedAuthentication;
    }
}
