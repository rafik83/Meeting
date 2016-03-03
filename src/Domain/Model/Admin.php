<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * "Compte admin/organisateur/collaborateur".
 */
class Admin implements UserInterface, EquatableInterface, \Serializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $salt;

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
    private $locale;

    /**
     * @param string $email
     * @param string $salt
     * @param string $password
     * @param string $firstname
     * @param string $lastname
     * @param string $locale
     */
    public function __construct($email, $salt, $password, $firstname, $lastname, $locale)
    {
        $this->email     = $email;
        $this->salt      = $salt;
        $this->password  = $password;
        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->locale    = $locale;
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
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return ['ROLE_ADMIN'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
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
    public function eraseCredentials()
    {
        $this->password = null;
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
            $this->firstname,
            $this->lastname,
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
            $this->firstname,
            $this->lastname,
            $this->salt
            ) = unserialize($serialized);
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
     * @return User
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
     * @return User
     */
    public function updateEmail($email)
    {
        $this->email = $email;

        return $this;
    }
}
