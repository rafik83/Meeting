<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

/**
 * "Compte admin/organisateur/collaborateur".
 */
class Admin extends AbstractUser
{
    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @param string $email
     * @param string $salt
     * @param string $password
     * @param string $firstname
     * @param string $lastname
     */
    public function __construct($email, $salt, $password, $firstname, $lastname)
    {
        parent::__construct($email, $salt, $password);

        $this->firstname = $firstname;
        $this->lastname  = $lastname;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return ['ROLE_ADMIN'];
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
}
