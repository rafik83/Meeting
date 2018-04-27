<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractUser implements UserInterface, EquatableInterface, \Serializable
{
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
        $this->salt     = $salt;
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
}
